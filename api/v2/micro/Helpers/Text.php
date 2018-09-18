<?php
namespace Micro\Helpers;

class Text extends \Phalcon\Text {

    private function __construct(){}

    public static function slug($text) {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) return '';
        
        return $text;
    }

    public static function initial($words) {
        $part = preg_split('/\s+/', strtolower($words));
        $size = count($part);
        $abbr = '';

        if ($size == 1) {
            $abbr = strtoupper($part[0][0]).(isset($part[0][1]) ? $part[0][1] : '');
        } else {
            for ($i = 0; $i < $size; $i++) {
                if ($i == 0) {
                    $abbr .= strtoupper($part[$i][0]);
                } else {
                    $abbr .= $part[$i][0];
                }
            }    
        }

        return $abbr;
    }

    public static function limitWords($text, $limit = 100, $suffix = '&#8230;') {
        if (trim($text) == '') {
            return $text;
        }

        preg_match('/^\s*+(?:\S++\s*+){1,'.(int) $limit.'}/', $text, $matches);

        if (strlen($text) === strlen($matches[0])) {
            $suffix = '';
        }

        return rtrim($matches[0]).$suffix;
    }

    public static function limitChars($text, $limit = 500, $suffix = '&#8230;') {
        if (mb_strlen($text) < $limit){
            return $text;
        }

        $text = preg_replace('/ {2,}/', ' ', str_replace(array("\r", "\n", "\t", "\x0B", "\x0C"), ' ', $text));

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $chars = '';

        foreach (explode(' ', trim($text)) as $val) {
            $chars .= $val.' ';
            if (mb_strlen($chars) >= $limit) {
                $chars = trim($chars);
                return (mb_strlen($chars) === mb_strlen($text)) ? $chars : $chars.$suffix;
            }
        }
    }

}