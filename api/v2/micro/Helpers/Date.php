<?php
namespace Micro\Helpers;

use Moment\Moment;

class Date {

    public static function today() {
        return new Moment();
    }

    public static function create($date) {
        return new Moment($date);
    }

    public static function format($date, $format = 'M d, Y H:i') {
        if (empty($date)) {
            return '';
        }
        $date = new Moment(strtotime($date));
        return $date->format($format);
    }

    public static function formatRelative($date, $format = 'M d, Y H:i') {
        if (empty($date)) {
            return '';
        }
        
        $date = new Moment(strtotime($date));
        $diff = $date->fromNow();

        if ($diff->getDirection() == 'past') {
            return $diff->getRelative();
        } else {
            return $date->format($format);
        }
    }

    public static function formatPeriod($start, $end, $separator = ' - ') {
        if ( ! empty($start) && ! empty($end)) {
            $start = self::create($start);
            $end = self::create($end);

            $m1 = $start->format('F');
            $m2 = $end->format('F');
            $y1 = $start->format('Y');
            $y2 = $end->format('Y');    

            if ($y1 == $y2) {
                if ($m1 == $m2) {
                    $period = $m1.' '.$y1;
                } else {
                    $period = $m1.' '.$separator.' '.$m2.' '.$y1;
                }
            } else {
                $period = $m1.' '.$y1.' '.$separator.' '.$m1.' '.$y2;
            }

            return $period;
        }

        return '';
    }

    public static function greg2hijri($date) {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        $y = (int) $date->format('Y');
        $m = (int) $date->format('m');
        $d = (int) $date->format('d');

        if (($y > 1582) OR (($y == 1582) && ($m > 10)) OR (($y == 1582) && ($m == 10) && ($d > 14))) {
            $j = self::__int((1461*($y+4800+self::__int(($m-14)/12)))/4)+self::__int((367*($m-2-12*(self::__int(($m-14)/12))))/12)-
                 self::__int( (3* (self::__int(  ($y+4900+    self::__int( ($m-14)/12)     )/100)    )   ) /4)+$d-32075;
        } else {
            $j = 367*$y-self::__int((7*($y+5001+self::__int(($m-9)/7)))/4)+self::__int((275*$m)/9)+$d+1729777;
        }

        $l = $j-1948440+10632;
        $n = self::__int(($l-1)/10631);
        $l = $l-10631*$n+354;
        $j = (self::__int((10985-$l)/5316))*(self::__int((50*$l)/17719))+(self::__int($l/5670))*(self::__int((43*$l)/15238));
        $l = $l-(self::__int((30-$j)/15))*(self::__int((17719*$j)/50))-(self::__int($j/16))*(self::__int((15238*$j)/43))+29;

        $m = self::__int((24*$l)/709);
        $d = $l-self::__int((709*$m)/24);
        $y = 30*$n+$j-30;

        return $y.'-'.$m.'-'.$d;
    }

    private static function __int($float) {
        if ($float < -0.0000001) {
            return ceil($float - 0.0000001);
        } else {
            return floor($float + 0.0000001);
        }
    }

}