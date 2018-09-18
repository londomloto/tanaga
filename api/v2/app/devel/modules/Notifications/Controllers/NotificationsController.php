<?php
namespace App\Notifications\Controllers;

use App\Notifications\Models\Notification;

class NotificationsController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getParams();
        $display = isset($params['display']) ? $params['display'] : FALSE;

        switch($display) {
            case 'top':
                return Notification::items(0, 6);
                
            default:
                return Notification::items();
        }

    }

}