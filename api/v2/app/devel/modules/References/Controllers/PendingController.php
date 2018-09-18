<?php

namespace App\References\Controllers;

use
	App\References\Models\RefPending;

class PendingController extends \Micro\Controller {

	function findAction() {
		return RefPending::get()->sortable()->paginate();
	}

	function findByIdAction($id) {
		return RefPending::get($id)->sortable()->paginate();
	}

	function createAction() {
		$post = $this->request->getJson();

        $model = new RefPending();
        if ($model->save($post)) {
            return RefPending::get($model->rp_id);
        }

        return RefPending::none();
	}

	public function updateAction($id) {
        $post = $this->request->getJson();
        $model = RefPending::get($id);

        if ($model->data) {
            $model->data->save($post);
        }

        return $model;
    }

    public function deleteAction($id) {
        $model = RefPending::get($id);
        $success = FALSE;

        if ($model->data) {
            $success = $model->data->delete();
        }

        return array(
            'success' => $success
        );
    }

}