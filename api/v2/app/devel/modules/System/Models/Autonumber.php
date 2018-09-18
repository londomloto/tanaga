<?php
namespace App\System\Models;

class Autonumber extends \Micro\Model {

    public function getSource() {
        return 'sys_numbers';
    }

    public static function generate($name) {

        $number = self::findFirst(array(
            'sn_name = :name:',
            'bind' => array('name' => $name)
        ));

        if ($number) {
            $value = $number->sn_value + 1;
            $length = empty($number->sn_length) ? 1 : $number->sn_length;

            $number->sn_value = $value;
            $number->save();
            
            $value = str_pad($value, (int)$length, '0', STR_PAD_LEFT);

            if ( ! empty($number->sn_prefix)) {
                $value = $number->sn_prefix.$value;
            }

            if ( ! empty($number->sn_suffix)) {
                $value = $value.$number->sn_suffix;
            }

            return $value;
        }

        return NULL;
    }

}