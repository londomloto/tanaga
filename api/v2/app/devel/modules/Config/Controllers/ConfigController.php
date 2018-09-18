<?php
namespace App\Config\Controllers;

use App\Config\Models\Config;

class ConfigController extends \Micro\Controller {

    public function loadAction() {
        $data = Config::data();
        $data['app_session'] = str_replace('-', '', $this->security->getRandom()->uuid());
        
        $data['app_version'] = '2.0.5';
        $data['app_push_server'] = $this->socket->host;

        // handle package info
        if (isset($data['app_package']) && $data['app_package'] != 'FREE') {
            $data['notif_global'] = '';
        }

        foreach(array(
            'app_package', 
            'app_limit', 
            'app_package_approval', 
            'app_pricing', 
            'app_package_desc') as $e) 
        {
            unset($data[$e]);
        }

        return array(
            'success' => TRUE,
            'data' => $data
        );
    }
    
    public function loadPackageAction() {
        $rows = Config::data();

        $item = array(
            'app_package', 
            'app_limit', 
            'app_package_approval', 
            'app_pricing', 
            'app_pricing_pro',
            'app_package_desc'
        );

        $data = array();

        foreach($item as $e) {
            if(isset($rows[$e])) $data[$e] = $rows[$e];
        }

        return array(
            'success' => TRUE,
            'data' => $data
        );
    }

    public function savePackageAction() {

    }

    public function saveAction() {
        $post = $this->request->getJson();

        foreach($post as $key => $val) {
            $model = Config::findFirst(array(
                'sc_name = :name:',
                'bind' => array('name' => $key)
            ));

            if ($model) {
                $model->sc_value = $val;
            } else {
                $model = new Config();
                $model->sc_name = $key;
                $model->sc_value = $val;
            }

            $model->save();
        }

        return array(
            'success' => TRUE,
            'data' => $post
        );
    }

    public function routesAction() {
        
        $routes = array();
        $config = $this->getApp()->config;

        if ($config->offsetExists('routes')) {
            $routes = $config->routes->toArray();
        }   

        $fallback = \App\Menus\Models\Menu::findFirst('smn_default = 1');
        
        if ($fallback) {
            $routes['fallback'] = $fallback->smn_path;
        }

        return array(
            'success' => TRUE,
            'data' => $routes
        );

    }

    public function translationsAction($lang) {
        return array();
    }
}