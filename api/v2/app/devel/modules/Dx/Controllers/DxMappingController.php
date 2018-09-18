<?php
namespace App\Dx\Controllers;

use App\Dx\Models\DxMapping;

class DxMappingController extends \Micro\Controller {

	function findAction() {
		return DxMapping::query()->sortable()->paginate();
	}

}