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

}