<?php
namespace App\Projects\Controllers;

use App\Projects\Models\Project,
	App\Projects\Models\ProjectUser,
	App\Users\Models\User,
	Micro\Helpers\Text;

class ProjectsController extends \Micro\Controller {

	public function findAction() {
		$display = $this->request->getQuery('display');

		switch($display) {
			case 'granted':
				$user = $this->auth->user();

				// find public projects
				$public = $this->db->fetchAll(
					'SELECT sp_id FROM sys_projects WHERE NOT EXISTS (SELECT 1 FROM sys_projects_users WHERE sp_id = spu_sp_id)',
					\Phalcon\Db::FETCH_ASSOC
				);

				$public = array_map(function($e){ return $e['sp_id']; }, $public);
				$public = implode(',', $public);

				$query = Project::get()
					->alias('a')
					->columns(array('a.sp_id',  'MIN(b.spu_id) AS spu_id')) 
					->join('App\Projects\Models\ProjectUser', 'a.sp_id = b.spu_sp_id', 'b')
					->filterable();

				if ( ! empty($public)) {
					$query->andWhere('(b.spu_su_id = :user: OR a.sp_creator_id = :user: OR a.sp_id IN ('.$public.'))', array('user' => $user['su_id']));
				} else {
					$query->andWhere('(b.spu_su_id = :user: OR a.sp_creator_id = :user:)', array('user' => $user['su_id']));
				}
					
					// ->orWhere('a.sp_creator_id = :user:', array('user' => $user['su_id']))
				$query->groupBy('a.sp_id')->sortable();

				$result = $query->paginate()->map(function($rec){
					$project = Project::get($rec->sp_id)->data;
					return $project->toArray();
				});

				return $result;

			default:
				return Project::get()->filterable()->sortable()->paginate();
		}
	}

	public function loadAction($name) {
		$project = Project::findFirst(array(
			'sp_name = :name:',
			'bind' => array('name' => $name)
		));

		if ($project) {
			return array(
				'success' => TRUE,
				'data' => $project
			);
		}

		return Project::none();
	}

	public function createAction() {
		$post = $this->request->getJson();
		$user = $this->auth->user();

		$post['sp_name'] = Text::slug($post['sp_title']);
		$post['sp_created_date'] = date('Y-m-d H:i:s');
		$post['sp_creator_id'] = $user['su_id'];

		// check by title
		$found = Project::find(array(
			'sp_title = :title:',
			'bind' => array(
				'title' => $post['sp_title']
			)
		));

		$count = $found->count();

		if ($count > 0) {
			$post['sp_name'] .= '-'.($count + 1);
		}
		
		$project = new Project();

		if ($project->save($post)) {
			if (isset($post['members'])) {
				
				foreach($post['members'] as $m) {
					$pu = new ProjectUser();
					$pu->spu_sp_id = $project->sp_id;
					$pu->spu_su_id = $m;
					$pu->save();
				}
				
			}
			return Project::get($project->sp_id);
		}

		return Project::none();
	}

	public function updateAction($id) {
		$query = Project::get($id);

		if ($query->data) {
			$data = $query->data;
			$post = $this->request->getJson();

			// check name
			$name1 = strtolower(trim($post['sp_title']));
			$name2 = strtolower(trim($data->sp_title));

			if ($name1 != $name2) {
				// title has been changed
				
				$slug = Text::slug($post['sp_title']);

				$found = Project::find(array(
					'sp_title = :title: AND sp_id <> :id:',
					'bind' => array(
						'title' => $post['sp_title'],
						'id' => $data->sp_id
					)
				));

				$count = $found->count();

				if ($count > 0) {
					$slug .= '-'.($count + 1);
				}

				$post['sp_name'] = $slug;
			}

			$data->save($post);
		}

		return $query;
	}

	public function deleteAction($id) {
		$project = Project::get($id)->data;

		if ($project) {
			$project->delete();
		}

		return array(
			'success' => TRUE
		);
	}

	public function addMemberAction($id) {

	}

	public function removeMemberAction($id) {

	}
}