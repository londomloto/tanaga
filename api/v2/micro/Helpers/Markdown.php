<?php
namespace Micro\Helpers;

class Markdown {



    public static function html($markdown) {
        static $engine;

        if (is_null($engine)) {
            $engine = new \Parsedown();
            $engine->setSafeMode(true);
        }

        return $engine->text($markdown);
    }

    public static function text($html) {
        static $engine;

        if (is_null($engine)) {
            $engine = new \Markdownify\Converter();
        }
        
        echo $engine->parseString($html);
    }


}