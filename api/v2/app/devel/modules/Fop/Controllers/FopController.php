<?php
namespace App\Fop\Controllers;

use 
	App\Fop\Models\Fop,
	App\Project\Models\Project,
	App\Waspang\Models\Waspang,
	App\Waspang\Models\LogWaspang;

class FopController extends \Micro\Controller {

	function findAction() {
		return Fop::query()->sortable()->paginate();
	}

	function createAction() {
		$post = $this->request->getJson();

		if($post) {

			$userLogin = $this->auth->user();

			$dataInsert = $post;
			$dataInsert['tf_date'] = date('Y-m-d H:i:s');
			$dataInsert['tp_su_id'] = $userLogin['su_id'];

			$modelFop = new Fop;
			$doInsert = $modelFop->save($post);

			if($doInsert) {
				$getExistFop = Fop::find([
					'conditions' => 'tf_date < ?1 AND tf_project_id = ?2',
					'bind' => array(
						1 => $dataInsert['tf_date'],
						2 => $dataInsert['tf_project_id']
					)
				]);

				$arrExistFop = $getExistFop->toArray();
				$partGroup = 1;

				if($arrExistFop) {
					$partGroup = 1 + count($arrExistFop);
				}

				$dataUpdateWaspang = array(
					'tw_part_group' => $partGroup
				);

				$dataUpdateProject = array(
					'tp_part_group' => $partGroup
				);

				$modelWaspang = Waspang::findFirst([
					'conditions' => 'tw_project_id = ?1 AND (tw_date < ?2 AND tw_date > ?3)',
					'bind' => array(
						1 => $post['tf_project_id'],
						2 => $dataInsert['tf_date'],
						3 => $arrExistFop[count($arrExistFop) - 1]['tf_date']
					)
				]);

				$modelProject = Project::findFirst([
					'conditions' => 'tp_project_id = ?1  AND (tp_date < ?2 AND tp_date > ?3)',
					'bind' => array(
						1 => $post['tf_project_id'],
						2 => $dataInsert['tf_date'],
						3 => $arrExistFop[count($arrExistFop) - 1]['tf_date']
					)
				]);

				$dataInsertLogWaspang = array();
				$dataInsertLogWaspang['ltw_su_id'] = $userLogin['su_id'];
				$dataInsertLogWaspang['ltw_date'] = date('Y-m-d H:i:s');
				$dataInsertLogWaspang['ltw_activity'] = 'UPDATE';

				foreach($modelWaspang->toArray() as $key => $value) {
					$dataInsertLogWaspang['ltw_'.$key] = $value;
				}

				$logModelWaspang = new LogWaspang;
				$logModelWaspang->save($dataInsertLogWaspang);

				$dataInsertLogProject = array();
				$dataInsertLogProject['ltp_su_id'] = $userLogin['su_id'];
				$dataInsertLogProject['ltp_date'] = date('Y-m-d H:i:s');
				$dataInsertLogProject['ltp_activity'] = 'UPDATE';

				foreach($modelProject->toArray() as $key => $value) {
					$dataInsertLogProject['ltp_'.$key] = $value;
				}

				$logProjectModel = new LogWaspang;
				$logProjectModel->save($dataInsertLogProject);

				$doUpdateWaspang = $modelWaspang->save($dataUpdateWaspang);
				$doUpdateProject = $modelProject->save($dataUpdateProject);

				return array(
					'success' => ($doUpdateWaspang && $doUpdateProject ? true : false)
				);
			}
		}
		else {
			return array(
				'success' => false
			);
		}
	}

}