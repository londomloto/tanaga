<?php

namespace App\Dashboard\Controllers;

use App\Kanban\Models\KanbanSetting,
	App\Users\Models\User,
	App\Users\Models\UserKanban,
	App\Sales\Models\SalesStatus;

class DashboardController extends \Micro\Controller {

	function findAction() {
		$result = array(
			'success' => TRUE
		);

		$auth = $this->auth->user();

		$data = array();

		if ($auth) {
			$user = User::get($auth['su_id'])->data;

			if ($user) {
				foreach($user->kanban as $item) {
					$kanban = KanbanSetting::get($item->suk_ks_id)->data;

					if ($kanban) {

						$series = array();
						foreach($kanban->panels as $panel) {
							$serie = array();
							$serie[] = $panel->kp_title;

							$statuses = array_map(function($s){ return $s['kst_status']; }, $panel->statuses->toArray());
							
							if (count($statuses)) {
								$items = SalesStatus::get()
									->columns('COUNT(1) as total')
					                ->where('tss_status IN ('.implode(',', $statuses).') AND tss_deleted = 0')
					                ->execute();

					            $serie[] = (double)$items[0]->total;
							} else {
								$series[] = 0;
							}

							$series[] = $serie;
						}

						$board = array(
							'kanban' => $kanban->toArray(),
							'chart' => array(
								'title' => 'Summaries',
								'series' => $series
							)
						);
						$data[] = $board;	
					}
				}	
			}
		}

		$result['data'] = $data;

		return $result;
	}

}