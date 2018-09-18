<?php
namespace Micro;

class Image {

    protected $_image;

    public function __construct($source) {
        if ( ! file_exists($source)) {
            throw new \Phalcon\Exception(basename($source) .' not found', 404);
        }

        if (is_dir($source)) {
            throw new \Phalcon\Exception('Unsupported image source', 404);
        }

        $this->_image = new \Phalcon\Image\Adapter\Gd($source);

        if (substr($this->_image->getMime(), 0, 5) != 'image') {
            throw new \Phalcon\Exception("Unsupported image type", 404);
        }
    }

    public function getWidth() {
        return $this->_image->getWidth();
    }

    public function getHeight() {
        return $this->_image->getHeight();
    }

    public function getAspectRatio() {
        return $this->_image->getWidth()/$this->_image->getHeight();
    }

    public function thumb($width = 100, $height = 100) {
        ini_set('memory_limit', '500M');

        $mime = $this->_image->getMime();
        $path = $this->_image->getRealPath();
        $width = (float) $width;
        $height = (float) $height;

        if (in_array($mime, array('image/png', 'image/x-png'))) {
            apache_setenv('no-gzip', 1);
        }

        $this->cache();

        $exp = gmdate('D, d M Y H:i:s', time()) . ' GMT';

        $w = $this->_image->getWidth();
        $h = $this->_image->getHeight();
        $q = 100;

        if ($width >= $w && $height >= $h) {
            $width = $w;
            $height = $h;
        }

        $x = 0;
        $y = 0;

        $imageRatio = $w / $h;
        $cropRatio = (float) $width / (float) $height;

        if ($imageRatio < $cropRatio) { 
            $a = $h;
            $h = $w / $cropRatio;
            $y = ($a - $h) / 2;
        } else if ($imageRatio > $cropRatio) { 
            $b = $w;
            $w = $h * $cropRatio;
            $x = ($b - $w) / 2;
        }

        $xRatio = $width / $w;
        $yRatio = $height / $h;

        if ($xRatio * $h < $height) { 
            $th = ceil($xRatio * $h);
            $tw = $width;
        } else {
            $tw = ceil($yRatio * $w);
            $th = $height;
        }

        $dst = imagecreatetruecolor($tw, $th);

        switch ($mime) {
            case 'image/gif':
                $create = 'ImageCreateFromGif';
                $output = 'ImagePng';
                $mime = 'image/png';
                $q = round(10 - ($q / 10));
            break;

            case 'image/x-png':
            case 'image/png':
                $create = 'ImageCreateFromPng';
                $output = 'ImagePng';
                $q = round(10 - ($q / 10));
            break;

            default:
                $create = 'ImageCreateFromJpeg';
                $output = 'ImageJpeg';
            break;
        }

        $src = $create($path);

        if (in_array($mime, array('image/gif', 'image/png'))) {
            imagealphablending($dst, FALSE);
            imagesavealpha($dst, TRUE);
        }

        ImageCopyResampled($dst, $src, 0, 0, $x, $y, $tw, $th, $w, $h);

        ob_start();
        $output($dst, NULL, $q);
        $data = ob_get_contents();
        ob_clean();

        ImageDestroy($src);
        ImageDestroy($dst);

        header("Cache-Control: cache");
        header("Pragma: cache");
        header("Expires: ".$exp);
        header("Content-type: ".$mime);
        header("Content-Length: ".strlen($data));

        echo $data;
    }

    public function cache() {
        $tag = md5_file($this->_image->getRealPath());
        $mod = gmdate('D, d M Y H:i:s', filemtime($this->_image->getRealPath())) . ' GMT';

        header("Last-Modified: $mod");
        header("ETag: \"{$tag}\"");

        $IF_NONE_MATCH = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : FALSE;
        $IF_MODIFIED_SINCE = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) : FALSE;

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) {
            if ( ! in_array($this->_image->getMime(), array('image/x-png'))) {
                $tag = $tag.'-gzip';
                $IF_NONE_MATCH = strtolower(str_replace(array(
                    '"',
                    '-gzip'
                ) , '', $IF_NONE_MATCH)) . '-gzip';
            }
        }

        if ( ! $IF_MODIFIED_SINCE && ! $IF_NONE_MATCH) return;
        if ($IF_NONE_MATCH && $IF_NONE_MATCH != $tag && $IF_NONE_MATCH != '"' . $tag . '"') return;
        if ($IF_MODIFIED_SINCE && $IF_MODIFIED_SINCE != $mod) return;

        header('HTTP/1.1 304 Not Modified');
        exit();
    }

}