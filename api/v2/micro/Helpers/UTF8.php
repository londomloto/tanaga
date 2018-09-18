<?php
namespace Micro\Helpers;

class UTF8 {

    protected static $_UTF8_WIN1252 = array(
        "\xe2\x82\xac" => "\x80",
        "\xe2\x80\x9a" => "\x82",
        "\xc6\x92" => "\x83",
        "\xe2\x80\x9e" => "\x84",
        "\xe2\x80\xa6" => "\x85",
        "\xe2\x80\xa0" => "\x86",
        "\xe2\x80\xa1" => "\x87",
        "\xcb\x86" => "\x88",
        "\xe2\x80\xb0" => "\x89",
        "\xc5\xa0" => "\x8a",
        "\xe2\x80\xb9" => "\x8b",
        "\xc5\x92" => "\x8c",
        "\xc5\xbd" => "\x8e",
        "\xe2\x80\x98" => "\x91",
        "\xe2\x80\x99" => "\x92",
        "\xe2\x80\x9c" => "\x93",
        "\xe2\x80\x9d" => "\x94",
        "\xe2\x80\xa2" => "\x95",
        "\xe2\x80\x93" => "\x96",
        "\xe2\x80\x94" => "\x97",
        "\xcb\x9c" => "\x98",
        "\xe2\x84\xa2" => "\x99",
        "\xc5\xa1" => "\x9a",
        "\xe2\x80\xba" => "\x9b",
        "\xc5\x93" => "\x9c",
        "\xc5\xbe" => "\x9e",
        "\xc5\xb8" => "\x9f"
    );

    protected static $_WIN1252_UTF8 = array(
        128 => "\xe2\x82\xac",
        130 => "\xe2\x80\x9a",
        131 => "\xc6\x92",
        132 => "\xe2\x80\x9e",
        133 => "\xe2\x80\xa6",
        134 => "\xe2\x80\xa0",
        135 => "\xe2\x80\xa1",
        136 => "\xcb\x86",
        137 => "\xe2\x80\xb0",
        138 => "\xc5\xa0",
        139 => "\xe2\x80\xb9",
        140 => "\xc5\x92",
        142 => "\xc5\xbd",
        145 => "\xe2\x80\x98",
        146 => "\xe2\x80\x99",
        147 => "\xe2\x80\x9c",
        148 => "\xe2\x80\x9d",
        149 => "\xe2\x80\xa2",
        150 => "\xe2\x80\x93",
        151 => "\xe2\x80\x94",
        152 => "\xcb\x9c",
        153 => "\xe2\x84\xa2",
        154 => "\xc5\xa1",
        155 => "\xe2\x80\xba",
        156 => "\xc5\x93",
        158 => "\xc5\xbe",
        159 => "\xc5\xb8"
    );

    public static function fix($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::fix($v);
            }
            return $value;
        }
        
        $last = '';
        
        while ($last <> $value) {
            $last = $value;
            $value = self::make(utf8_decode(str_replace(array_keys(self::$_UTF8_WIN1252) , array_values(self::$_UTF8_WIN1252) , $value)));
        }
        
        $value = self::make(utf8_decode(str_replace(array_keys(self::$_UTF8_WIN1252) , array_values(self::$_UTF8_WIN1252) , $value)));
        
        return $value;
    }

    public static function make($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::make($v);
            }
            return $value;
        } else if (is_string($value)) {
            $max = strlen($value);
            $buf = "";
            for ($i = 0; $i < $max; $i++) {
                $c1 = $value{$i};
                if ($c1 >= "\xc0") {
                    $c2 = $i + 1 >= $max ? "\x00" : $value{$i + 1};
                    $c3 = $i + 2 >= $max ? "\x00" : $value{$i + 2};
                    $c4 = $i + 3 >= $max ? "\x00" : $value{$i + 3};
                    if ($c1 >= "\xc0" & $c1 <= "\xdf") {
                        if ($c2 >= "\x80" && $c2 <= "\xbf") {
                            $buf.= $c1 . $c2;
                            $i++;
                        } else {
                            $cc1 = (chr(ord($c1) / 64) | "\xc0");
                            $cc2 = ($c1 & "\x3f") | "\x80";
                            $buf.= $cc1 . $cc2;
                        }
                    } else if ($c1 >= "\xe0" & $c1 <= "\xef") {
                        if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf") {
                            $buf.= $c1 . $c2 . $c3;
                            $i = $i + 2;
                        } else {
                            $cc1 = (chr(ord($c1) / 64) | "\xc0");
                            $cc2 = ($c1 & "\x3f") | "\x80";
                            $buf.= $cc1 . $cc2;
                        }
                    } else if ($c1 >= "\xf0" & $c1 <= "\xf7") {
                        if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" && $c4 >= "\x80" && $c4 <= "\xbf") {
                            $buf.= $c1 . $c2 . $c3;
                            $i = $i + 2;
                        } else {
                            $cc1 = (chr(ord($c1) / 64) | "\xc0");
                            $cc2 = ($c1 & "\x3f") | "\x80";
                            $buf.= $cc1 . $cc2;
                        }
                    } else {
                        $cc1 = (chr(ord($c1) / 64) | "\xc0");
                        $cc2 = (($c1 & "\x3f") | "\x80");
                        $buf.= $cc1 . $cc2;
                    }
                } else if (($c1 & "\xc0") == "\x80") {
                    if (isset(self::$win_1252_to_utf8[ord($c1) ])) {
                        $buf.= self::$win_1252_to_utf8[ord($c1) ];
                    } else {
                        $cc1 = (chr(ord($c1) / 64) | "\xc0");
                        $cc2 = (($c1 & "\x3f") | "\x80");
                        $buf.= $cc1 . $cc2;
                    }
                } else {
                    $buf.= $c1;
                }
            }
            return $buf;
        } else {
            return $value;
        }
    }

}