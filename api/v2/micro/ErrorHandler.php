<?php
namespace Micro;

class ErrorHandler {

    protected $_app;
    protected $_throwable;
    protected $_user;
    protected $_path;

    public function __construct(App $app) {
        $this->_app = $app;
        $this->_throwable = TRUE;

        // Jika windows, buat folder didrive dimana apache (web server) terinstall
        $this->_path = '/track/whoops';

        if ($this->isDevelopment()) {
            $this->setDevelopment(); 
        } else {
            $this->setProduction();
        }

        $this->cleanup();
    }

    public function setApp(App $app) {
        $this->_app = $app;
    }

    public function getApp() {
        return $this->_app;
    }

    public function getUser() {
        $request = $this->_app->request;
        $token = $request->getQuery('access_token');
        $user = '[system]';

        if (empty($token)) {
            $header = $request->getHeader('Authorization');
            if ($header && (preg_match('/Bearer\s+(.*)/', $header, $matches))) {
                $token = $matches[1];
            }
        }

        if ( ! empty($token)) {
            try {
                $data = \Firebase\JWT\JWT::decode($token, $config->secret, array('HS512'));
                $user = $data['payload']->data->su_email;    
            } catch(\Exception $ex) {}
        }

        return $user;
    }

    public function cleanup() {
        static $clean;

        if ($this->isDevelopment()) {
            return;
        }

        if (is_null($clean)) {
            $path = $this->_path;

            if ( ! file_exists($path)) {
                $exception = new \ErrorException('Whoops, error!', 500);
                $this->handleException($exception);
            }

            $scan = scandir($path);

            foreach($scan as $file) {
                if ($file[0] == '.') continue;

                $y = substr($file, 6, 4);
                $m = substr($file, 10, 2);
                $d = substr($file, 12, 2);

                $created = new \DateTime($y.'-'.$m.'-'.$d);
                $today = new \DateTime();
                $delta = (int)$today->diff($created)->format('%d');

                if ($delta >= 5) {
                    unlink($path.'/'.$file);
                }
            }

            $clean = TRUE;
        }
        
    }

    public function log($exception) {
        $file = $this->_path.'/error_'.date('Ymd').'.log';
        $mode = file_exists($file) ? 'a' : 'w';
        $open = fopen($file, $mode);

        if ($open) {
            $file = $exception->getFile();
            $line = $exception->getLine();

            if (empty($file)) {
                $file = '[internal]';
            }

            if (empty($line)) {
                $line = 0;
            }

            $user = $this->getUser();

            $text = array(
                'date' => date('Y-m-d H:i:s'),
                'user' => $user,
                'type' => get_class($exception),
                'status' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $file,
                'line' => $line
            );

            $json = json_encode($text, \JSON_PRETTY_PRINT).',';

            flock($open, \LOCK_EX);
            fwrite($open, $json.PHP_EOL);
            flock($open, \LOCK_UN);

            fclose($open);
        } else {
            // force to display error
            if ( ! headers_sent()) {
                header('Content-Type: application/json');
            }

            echo json_encode(array(
                'success' => FALSE,
                'message' => 'Whoops, error!'
            ), \JSON_PRETTY_PRINT);

            exit();
        }
        
    }

    public function isDevelopment() {
        $env = $this->_app->config->app->env;
        return $env == 'DEVELOPMENT';
    }

    public function setDevelopment() {
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ALL);
    }

    public function setProduction() {
        ini_set('display_errors', 0);
        ini_set('error_reporting', E_ALL);
    }

    public function handle($types = 'defaults') {

        if ($types == 'defaults') {
            $types = E_ALL | E_STRICT;
        }

        // set handlers
        set_error_handler(array($this, 'handleError'), $types);
        set_exception_handler(array($this, 'handleException'));
        register_shutdown_function(array($this, 'handleShutdown'));
    }

    public function handleException($exception) {

        if ($this->isDevelopment()) {
            /*$traces = sprintf(
                '%s in %s on line %d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );*/

            $response = array(
                'success' => FALSE,
                'status' => $exception->getCode(),
                'message' => $exception->getMessage()
            );

            if ($exception instanceof \ErrorException) {
                $response['trace'] = $exception->getTrace();
            }

        } else {
            $this->log($exception);

            if ($exception instanceof \ErrorException) {
                $message = 'Invalid parameters';
            } else {
                $message = $exception->getMessage();
            }

            $response = array(
                'success' => FALSE,
                'message' => $message
            );
        }

        if ( ! headers_sent()) {
            header('Content-Type: application/json');
        }

        echo json_encode($response, \JSON_PARTIAL_OUTPUT_ON_ERROR | \JSON_PRETTY_PRINT);
        exit();
    }

    public function handleError($level, $message, $file = NULL, $line = NULL) {
        if ($level & error_reporting()) {
            
            $exception = new \ErrorException(
                $message, 
                $level, // as code
                $level, // as severity
                $file, 
                $line
            );

            if ($this->_throwable) {
                throw $exception;
            } else {
                $this->handleException($exception);
            }

            return TRUE;
        }

        return FALSE;
    }

    public function handleShutdown() {
        $this->_throwable = FALSE;
        $error = error_get_last();
        
        if ($error && self::isLevelFatal($error['type'])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    public static function isLevelFatal($level) {
        $errors  = E_ERROR;
        $errors |= E_PARSE;
        $errors |= E_CORE_ERROR;
        $errors |= E_CORE_WARNING;
        $errors |= E_COMPILE_ERROR;
        $errors |= E_COMPILE_WARNING;
        return ($level & $errors) > 0; 
    }
}