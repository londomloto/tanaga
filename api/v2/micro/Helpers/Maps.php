<?php
namespace Micro\Helpers;

class Maps {

    public static function pick($array, $keys) {
        $column = array_flip($keys);
        $values = array_intersect_key($array, $column);
        return $values;
    }

}