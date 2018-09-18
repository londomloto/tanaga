<?php
namespace Micro\Helpers;

class Theme {

    protected static $_colors = array(
        'var(--paper-red-500)' => '#f44336',
        'var(--paper-pink-500)' => '#e91e63',
        'var(--paper-purple-500)' => '#9c27b0',
        'var(--paper-deep-purple-500)' => '#673ab7',
        'var(--paper-indigo-500)' => '#3f51b5',
        'var(--paper-blue-500)' => '#2196f3',
        'var(--paper-cyan-500)' => '#00bcd4',
        'var(--paper-teal-500)' => '#009688',
        'var(--paper-green-500)' => '#4caf50',
        'var(--paper-lime-500)' => '#cddc39',
        'var(--paper-yellow-500)' => '#ffeb3b',
        'var(--paper-amber-500)' => '#ffc107',
        'var(--paper-orange-500)' => '#ff9800',
        'var(--paper-deep-orange-500)' => '#ff5722',
        'var(--paper-brown-500)' => '#795548',
        'var(--paper-grey-500)' => '#9e9e9e',
        'var(--paper-blue-grey-500)' => '#607d8b'
    );

    public static function colors() {
        return array_values(self::$_colors);
    }

    public static function val($prop) {
        return isset(self::$_colors[$prop]) ? self::$_colors[$prop] : $prop;
    }

}