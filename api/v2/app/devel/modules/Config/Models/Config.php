<?php
namespace App\Config\Models;

use App\Users\Models\User;

class Config extends \Micro\Model {

    public function getSource() {
        return 'sys_config';
    }

    public static function data() {
        $rows = self::get()->execute();
        $data = array();

        foreach($rows as $row) {
            $data[$row->sc_name] = $row->sc_value;
        }

        return $data;
    }

    public static function userLimit(){
        $limit = self::get()
            ->where("sc_name = 'app_limit'")
            ->execute()
            ->getFirst();

        $usage = User::get()
                    ->where("su_active = 1 or (su_active = 0 and su_invite_token is not null)")
                    ->execute();

        if($limit && $usage){
            return $limit->sc_value - (count($usage));
        }
        return 0;
    }
}