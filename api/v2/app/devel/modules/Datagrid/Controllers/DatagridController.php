<?php

namespace App\Datagrid\Controllers;

use App\Datagrid\Models\Datagrid;

class DatagridController extends \Micro\Controller {

	function findAction() {
		return Datagrid::query()->sortable()->paginate();
	}

	function debugAction() {
		$this->dx->setup(array(
			'profile' => 'fiberstar',
			'source' => 'userfile',
			'dbtype' => 'postgre'
		));
	}
	function uploadAction() {
		$this->dx->setup(array(
			'profile' => 'fiberstar',
			'source' => 'userfile',
			'dbtype' => 'postgre',
			'listeners' => array(
				'beforeupdaterow' => function($a, $b, $c) {
					
					if($b) {
						foreach($b as $key => $col) {
							if($col->dg_fullname == 'Rio') {
								$col->dg_job = 'Boss';
							}
						}
					}
				},
				'beforeinsertrow' => function($a, $b, $c) {
					if($b) {
						foreach($b as $key => $col) {
							if($col->dg_fullname == 'Joko') {
								$col->dg_job = 'Siapa Sih?';
							}
						}
					}
				}
			)
		));
		
		$this->dx->save();

		return array(
			'success' => $this->dx->success(),
			'error' => $this->dx->error(),
			'info' => $this->dx->info()
		);
	}

	function uploadDocumentAction() {
		echo "masuk sini"; exit();
		print_r($_FILES); exit();
	}

}