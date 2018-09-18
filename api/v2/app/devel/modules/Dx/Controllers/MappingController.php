<?php
namespace App\Dx\Controllers;

use App\Dx\Models\Mapping;

class MappingController extends \Micro\Controller {

    public function findAction() {
        $opts = $this->request->getQuery();

        $query = Mapping::get();

        if (isset($opts['params'])) {
            $params = json_decode($opts['params'], TRUE);

            if (isset($params['map_profile_id'])) {
                $query->where('map_profile_id = :profile:', array('profile' => $params['map_profile_id']));
            }
        }

        return $query->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();

        if ( ! empty($post['map_table']) && ! empty($post['map_xls_col']) && ! empty($post['map_tbl_col'])) {
            $mapping = new Mapping();
            if ($mapping->save($post)) {
                return Mapping::get($mapping->map_id);
            }
        }
        return Mapping::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $mapping = Mapping::get($id);

        if ($mapping->data) {
            $mapping->data->save($post);
        }

        return $mapping;
    }

    public function deleteAction($id) {
        $mapping = Mapping::get($id);
        $success = FALSE;

        if ($mapping->data) {
            $success = $mapping->data->delete();
        }

        return array(
            'success' => $success
        );
    }

}