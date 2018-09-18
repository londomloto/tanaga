<?php
namespace App\Dx\Controllers;

use App\Dx\Models\DxProfile;

class DxProfileController extends \Micro\Controller {

	function findAction() {
		return DxProfile::query()->sortable()->paginate();
	}

}