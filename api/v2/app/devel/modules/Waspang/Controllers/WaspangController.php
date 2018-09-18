<?php
namespace App\Waspang\Controllers;

use 
	Phalcon\Mvc\Model\Query,
	App\Waspang\Models\Waspang,
	App\Waspang\Models\LogWaspang,
	App\User\Models\SysWaspangUser;

class WaspangController extends \Micro\Controller {

	function findAction() {

		return Waspang::query()->sortable()->paginate();
	}

	function createAction() {
		$post = $this->request->getJson();

		if($post) {

			$userLogin = $this->auth->user();

			$dataInsert = $post;
			$dataInsert['tw_created_date'] = date('Y-m-d H:i:s');
			$dataInsert['tw_su_id'] = $userLogin['su_id'];

			$model = new Waspang;
			$doInsert = $model->save($dataInsert);

			$logModel = new LogWaspang;
			$dataInsertLog = array();
			$dataInsertLog['ltw_date'] = date('Y-m-d H:i:s');
			$dataInsertLog['ltw_su_id'] = $userLogin['su_id'];
			$dataInsertLog['ltw_activity'] = 'INSERT';

			foreach($doInsert as $key => $value) {
				$dataInsertLog['ltw_'.$key] = $value;
			}

			$logModel->save($dataInsertLog);

			return array(
				'success' => ($doInsert ? true : false)
			);
		}
		else {
			return array(
				'success' => false
			);
		}

	}

	function putAction($id) {

		$post = $this->request->getJson();

		if($post) {

			$userLogin = $this->auth->user();

			$dataUpdate = $post;
			// $dataUpdate['tw_created_date'] = date('Y-m-d H:i:s');
			// $dataUpdate['tw_su_id'] = $userLogin['su_id'];

			$model = Waspang::findFirst([
				'conditions' => 'tm_parent_id = ?1',
				'bind' => array(
					1 => $post['parent_id']
				)
			]);

			$logModel = new LogWaspang;
			$dataInsertLog = array();
			$dataInsertLog['ltw_date'] = date('Y-m-d H:i:s');
			$dataInsertLog['ltw_su_id'] = $userLogin['su_id'];
			$dataInsertLog['ltw_activity'] = 'UPDATE';

			foreach($model->toArray() as $key => $value) {
				$dataInsertLog['ltw_'.$key] = $value;
			}

			$logModel->save($dataInsertLog);

			$doUpdate = $model->save($dataUpdate);			

			return array(
				'success' => ($doUpdate ? true : false)
			);
		}
		else {
			return array(
				'success' => false
			);
		}

	}

	function getProjectParentAction() {
		$post = $_POST;

		$getParent = Waspang::find(array(
			[
				'conditions' => 'tm_project_id = ?1',
				'bind' => array(
					1 => $post['project_id']
				)
			]
		));

		$arrGetParent = $getParent->toArray();

		$resultParent = [];

		if($arrGetParent) {
			$resultParent = array(
				'parent_id' => $arrGetParent['tp_parent_id'],
				'parent_type' => $arrGetParent['tp_parent_type'],
				'parent_status' => $arrGetParent['tp_parent_status'],
				'lokasi' => $arrGetParent['tp_lokasi'],
				'longitude' => $arrGetParent['tp_longitude'],
				'latitude' => $arrGetParent['tp_latitude']
			);
		}

		return array(
			'success' => ($resultParent ? true : false),
			'data' => $resultParent
		);
	}

	function getProjectChildAction() {
		$post = $_POST;

		$getChild = Waspang::find(array(
			[
				'conditions' => 'tm_parent_id = ?1',
				'bind' => array(
					1 => $post['parent_id']
				)
			]
		));

		$arrGetChild = $getChild->toArray();

		$resultChild = [];

		if($arrGetChild) {
			$resultChild = array(
				'parent_id' => $arrGetChild['tp_parent_id'],
				'parent_type' => $arrGetChild['tp_parent_type'],
				'parent_status' => $arrGetChild['tp_parent_status'],
				'lokasi' => $arrGetChild['tp_lokasi'],
				'longitude' => $arrGetChild['tp_longitude'],
				'latitude' => $arrGetChild['tp_latitude'],
				'jb' => $arrGetChild['tp_jb'],
				'ms' => $arrGetChild['tp_ms'],
				'dpfo' => $arrGetChild['tp_dpfo'],
				'splitter' => array(
					'name' => $arrGetChild['tp_splitter'],
					'type' => $arrGetChild['splitter_type']
				)
			);
		}

		return array(
			'success' => ($resultChild ? true : false),
			'data' => $resultChild
		);
	}
}