<?php
namespace Micro\File;

class FileProvider {

    protected static $_mimes;
    protected static $_types = array();

    public function getExtension($file) {
        $ext = pathinfo($file, \PATHINFO_EXTENSION);
        return $ext;
    }

    public function getType($file) {

        if (is_null(self::$_mimes)) {
            self::$_mimes = require_once(__DIR__.'/mimes.php');
        }

        $ext = $this->getExtension($file);
        $mime = NULL;

        if ( ! empty($ext)) {
            if ( ! isset(self::$_types[$ext])) {
                foreach(self::$_mimes as $key => $val) {
                    if (in_array($ext, $val)) {
                        $mime = $key;
                        self::$_types[$ext] = $mime;
                        break;
                    }
                }
            }
        }

        // still empty ?
        if (empty($mime)) {
            $info = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($info, $file);

            finfo_close($info);
        }

        return $mime;
    }

    public function isImage($file) {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($info, $file);

        finfo_close($info);

        $type = array('image/jpeg', 'image/png', 'image/gif');

        if (in_array($mime, $type)) {
            return TRUE;
        }
        
        return FALSE;
    }

    public function exists($path) {
        return file_exists($path) && ! is_dir($path);
    }

    public function render($file, $name = NULL) {
        set_time_limit(0);

        $buffer = '';
        $handle = fopen($file, 'rb');
        $chunkSize = 1024;

        if ( ! $handle) {
            throw new \Phalcon\Exception("Unable to render {$file}", 500);
        }

        $mime = $this->getType($file);
        $size = filesize($file);

        if (is_null($name)) {
            $info = pathinfo($file);
            $name = '';

            if (isset($info['filename'])) {
                $name .= $info['filename'];
            }

            if (isset($info['extension'])) {
                $name .= '.'.$info['extension'];
            }
        }

        header("Pragma: public");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: ".$mime);
        header("Content-Length: ".filesize($file));
        header("Content-Disposition: inline; filename=\"".$name."\"");

        while( ! feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            echo $buffer;
            ob_flush();
            flush();
        }

        fclose($handle);
        exit();
    }

    public function download($file, $name = NULL) {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($info, $file);

        if (is_null($name)) {
            $info = pathinfo($file);
            $name = '';

            if (isset($info['filename'])) {
                $name .= $info['filename'];
            }

            if (isset($info['extension'])) {
                $name .= '.'.$info['extension'];
            }
        }

        // header('Set-Cookie: downloadtoken=finish; path=/');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: ".$mime);
        header("Content-Disposition: attachment; filename=\"".$name."\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($file));
        header("X-Download-Status: finish");

        ob_start();

        if ($stream = fopen($file, 'r')) {
            while ( ! feof($stream)) {
                echo fread($stream, 1024);
                ob_flush();
                flush();
            }
            fclose($stream);
        }

        ob_end_clean();

        exit();
    }



    /**
     * [file_get_contents description]
     * @return [type] [description]
     */
    public function file_get_contents( $url =null ) {
        
        $file = false;
        if ( !is_null( $url ) ) {
            if ( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url) ) 
            {
                if ( file_get_contents( $url ) == true ) {
                    $file = file_get_contents($url);
                } 
            }
        }

        return $file;

    }

}