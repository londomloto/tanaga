<?php
namespace Micro\Helpers;

class Coordinate {

	public static function longDmsToDec($longDeg, $longMin, $longSec, $longDir ='') {
        if(strtoupper($longDir ) == 'W'){
             return (round($longDeg + ($longMin / 60) + ($longSec/3600), 8)* -1);
        }
        else {
            return round($longDeg + ($longMin / 60) + ($longSec/3600), 8);
        }
    }

    public static function latDmsToDec($latDeg, $latMin, $latSec, $latDir = '') {        
        $latDec = round($latDeg + ($latMin / 60) + ($latSec / 3600), 8);        
        return strtoupper($latDir) == 'S' ? -$latDec : $latDec;
    }

    public static function latDecToDms($lat) {
        
        $x = ($lat < 0) ? -$lat : $lat;
        
        $latDeg = floor($x);
        $latMin = floor(($x - $latDeg) * 60);
        $latSec = round(((($x - $latDeg) * 60) - $latMin) * 60, 2);
        $latDir = $lat > 0 ? 'N' : 'S';
        
        // return $latDeg.'� '.$latMin.'\' '.$latSec.'" '.$latDir;

        return array(
        	'degree' => $latDeg,
        	'minute' => $latMin,
        	'second' => $latSec,
        	'direction' => $latDir
        );
    }

    public static function longDecToDms($long) {
        
        $y = $long;
        
        $longDeg = floor($y);
        $longMin = floor(($y - $longDeg) * 60);
        $longSec = round(((($y - $longDeg) * 60) - $longMin) * 60, 2);
        $longDir = 'E';
        
        // return $longDeg.'� '.$longMin.'\' '.$longSec.'" '.$long_dir;

        return array(
        	'degree' => $longDeg,
        	'minute' => $longMin,
        	'second' => $longSec,
        	'direction' => $longDir
        );
        
    }

}