<?php

namespace App\Mapss\Controllers;

use App\Mapss\Models\Mapss;

class MapssController extends \Micro\Controller {

	public function findAction() {

		$data = Mapss::query()->sortable()->paginate();

		return $data;
    }

}