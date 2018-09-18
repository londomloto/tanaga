<?php
namespace App\Command\Controllers;

class DbController extends \Micro\Controller {

    public function vacuumAction() {
        self::__auth();
        self::__head();

        $conn = $this->conn();

        echo '<pre class="code">';

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'bpm_diagrams'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'bpm_shapes'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'bpm_links'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'bpm_forms'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'trx_tasks'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'trx_tasks_statuses'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'trx_tasks_activities'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'trx_tasks_users'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'trx_tasks_labels'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'sys_users'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'sys_roles'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'sys_roles_permissions'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'sys_projects'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'sys_projects_users'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'sys_projects_labels'";
        self::__exec($comm, TRUE);

        $comm = "vacuumdb ".$conn['link']." --analyze --verbose --table 'sys_labels'";
        self::__exec($comm, TRUE);

        echo '</pre>';

        self::__foot();
    }

    public function backupAction() {

        self::__auth();
        self::__head();

        $conn = $this->conn();  

        $host = $conn['host'];
        $port = $conn['port'];
        $name = $conn['name'];
        $link = $conn['link'];

        $file = APPPATH.'dumps/'.$name.'_'.date('Y_m_d').'.sql';

        // Backup
        $comm = 'pg_dump '.$link.' --no-owner --encoding=utf-8 --format=p --insert --verbose > '.$file;
        self::__exec($comm);

        // Decorate
        if (file_exists($file)) {

            $tables = $this->db->listTables();
            $serials = array();

            foreach($tables as $table) {
                $columns = $this->db->describeColumns($table);
                foreach($columns as $column) {
                    if ($column->isAutoincrement()) {
                        $serials[$table] = $column->getName();
                    }
                }
            }

            $read = fopen($file, 'r');
            $scan = FALSE;
            $skip = FALSE;
            $void = FALSE;

            $text  = "";
            $text .= "--\n";
            $text .= "-- Worksaurus PostgreSQL Backup\n";
            $text .= "--\n";
            $text .= "-- Hostname : {$host}:{$port}\n";
            $text .= "-- Database : {$name}\n";
            $text .= "--\n";
            $text .= "-- Date: ".date('Y-m-d H:i:s')."\n";
            $text .= "--\n";


            while( ! feof($read)) {
                $line = fgets($read);

                if (preg_match('#CREATE TABLE ([^\s]+)#', $line, $matches)) {
                    $node = $matches[1];
                    if (isset($serials[$node])) {
                        $scan = $node;
                    }
                }

                if ($scan) {

                    $column = $serials[$scan];

                    $line = preg_replace_callback('#\b('.$column.')\b\s([^\s]+)#', function($matches){
                        return $matches[1] . ' '.str_replace('int', 'serial', $matches[2]);
                    }, $line);

                    if (preg_match('#\);#', $line, $matches)) {
                        $scan = FALSE;
                    }
                }

                if (preg_match('#CREATE SEQUENCE#', $line)) {
                    $skip = TRUE;
                }

                if ($skip) {
                    if (preg_match('#;#', $line)) {
                        $skip = FALSE;
                        continue;
                    }
                }

                if (preg_match("#^--#", $line)) {
                    continue;
                }

                if (preg_match('#ALTER SEQUENCE#', $line)) {
                    continue;
                }

                if (preg_match('#ALTER TABLE ([^\s]+)(?=_seq)#', $line)) {
                    continue;
                }

                if ( ! $void) {
                    if (preg_match('#^\n#', $line)) {
                        $void = TRUE;
                        $text .= "\n";
                    }    
                }

                if ($void) {
                    if (preg_match('#^\w#', $line)) {
                        $void = FALSE;
                    }
                }

                if ( ! $skip && ! $void) {
                    $text .= $line;
                }
            }

            fclose($read);

            $puts = fopen($file, 'w+');
            fwrite($puts, $text);
            fclose($puts);
            
            echo '<textarea class="code text">'."\n";
            echo file_get_contents($file);
            echo '</textarea>'."\n";
            echo "<script>setTimeout(() => { document.body.querySelectorAll('.line').forEach(n => n.remove()); }, 200);</script>";

        }

        self::__foot();
    }

    public function conn() {
        $info = $this->db->getDescriptor();

        return array(
            'name' => $info['dbname'],
            'host' => $info['host'],
            'port' => $info['port'],
            'link' => '--dbname=postgresql://'.$info['username'].':'.$info['password'].'@'.$info['host'].':'.$info['port'].'/'.$info['dbname']
        );
    }

    private static function __auth() {
        if ( ! isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Worksaurus"');
            header('HTTP/1.0 401 Unauthorized');
            echo '401 - UNAUTHORIZED';
            exit();
        } else {
            $auth = \App\Users\Models\User::findFirst(array(
                'su_email' => $_SERVER['PHP_AUTH_USER']
            ));

            $fail = FALSE;

            if ( ! $auth || ( ! \Micro\App::getDefault()->security->verifyHash($_SERVER['PHP_AUTH_PW'], $auth->su_passwd))) {
                $fail = TRUE;
            }

            if ($fail) {
                header('HTTP/1.0 401 Unauthorized');
                echo '401 - UNAUTHORIZED';
                exit();
            }

        }
    }

    private static function __head() {
        // HTML
        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo '<meta charset="UTF-8">'."\n";
        echo "<style>\n";
        echo "\thtml,body { padding: 0px; margin: 0px; width: 100%; height: 100%; }\n";
        echo "\tbody { background-color: #263238; color: #fff; }";
        echo "\t.code { color: #fff; margin: 0; padding: 1em; font-family: Consolas, Monaco, 'Courier New'; font-size: 0.83em;   }\n";
        echo "\t.line { position: absolute; left: 0; right: 0; top: 0; overflow: hidden; background-color: #263238; }";
        echo "\t.text { resize: none;  border: none; display: block; position: relative; box-sizing: border-box; width: 100%; height: 100%; outline: none; background-color: #263238; top: 0; z-index: 2; }";
        echo "</style>\n";
        echo "<head>\n";
        echo "</head>\n";
        echo "<body>\n";
    }

    private static function __foot() {
        echo "</body>\n";
        echo "</html>";
        exit();
    }

    private static function __exec($comm, $logs = FALSE) {
        $spec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );

        $proc = proc_open($comm, $spec, $pipes, NULL, NULL);

        if (is_resource($proc)) {

            flush();
            ob_flush();

            if ($logs) {
                while( ! feof($pipes[2])) {
                    $line = fgets($pipes[2]);
                    echo '&gt; '.$line;
                    flush();
                    ob_flush();
                    usleep(50000);  
                }
                echo "\n";
                flush();
                ob_flush();
            } else {
                while( ! feof($pipes[2])) {
                    $line = fgets($pipes[2]);
                    echo '<div class="code line">';
                    echo '&gt; '.$line;
                    echo '</div>';
                    flush();
                    ob_flush();
                    usleep(50000);  
                }
            }
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        proc_close($proc);
    }
}