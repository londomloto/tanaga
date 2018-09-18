<?php
namespace App\Menus\Controllers;

use App\Menus\Models\Menu;

class MenusController extends \Micro\Controller {

    public function findAction() {
        $params = $this->request->getQuery();
        $display = isset($params['display']) ? $params['display'] : 'grid';
        
        if ($display == 'tree') {
            return Menu::tree($params);
        } else {
            return Menu::grid($params);
        }
    }

    public function findByIdAction($id) {
        return Menu::get($id);
    }

    public function createAction() {
        $post = $this->request->getJson();
        $menu = new Menu();

        if ($menu->save($post)) {
            return Menu::get($menu->smn_id);
        }

        return Menu::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $menu = Menu::get($id);

        if ($menu->data) {

            if (isset($post['smn_default']) && $post['smn_default'] == 1) {
                foreach(Menu::find() as $row) {
                    $row->smn_default = 0;
                    $row->save();
                }
            }

            $menu->data->save($post);
        }

        return $menu;
    }

    public function deleteAction($id) {
        $query = Menu::get($id);
        $done = FALSE;

        if ($query->data) {
            $offset = $query->data->smn_id;

            $descendants = array();

            $query->data->cascade(function($menu) use (&$descendants, $offset) {
                if ($menu->smn_id != $offset) {
                    $descendants[] = $menu;
                }
            });
            
            $done = $query->data->delete();

            if ($done) {
                // move all descendants to root
                foreach($descendants as $menu) {
                    $menu->smn_parent_id = 0;
                    $menu->save();
                }
            }
        }

        return array(
            'success' => $done
        );
    }

    public function fallbackAction() {
        
    }
}