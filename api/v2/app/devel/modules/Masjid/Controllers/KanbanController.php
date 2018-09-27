<?php
namespace App\Masjid\Controllers;

use App\Tasks\Models\Task,
    App\Tasks\Models\TaskStatus,
    App\Tasks\Models\TaskUser,
    App\Tasks\Models\TaskLabel,
    App\Tasks\Models\TaskComment,
    App\Projects\Models\Project,
    App\System\Models\Autonumber,
    App\Masjid\Models\Masjid,
    App\Config\Models\Config,
    Micro\Helpers\Theme;

class KanbanController extends \Micro\Controller {

    public function findAction() {
        $payload = $this->request->getQuery();

        $params = isset($payload['params']) ? json_decode($payload['params'], TRUE) : array();
        $project = isset($payload['project']) ? $payload['project'] : FALSE;
        $statuses = isset($payload['statuses']) ? json_decode($payload['statuses']) : FALSE;

        $columns = array(
            'task_status.tts_id',
            'task_status.tts_slug',
            'task_status.tts_tt_id',
            'task_status.tts_status',
            'task_status.tts_target',
            'task_status.tts_worker',
            'task_status.tts_deleted',
            'task_status.tts_created'
        );

        $query = TaskStatus::get()
            ->alias('task_status')
            ->columns($columns) 
            ->join('App\Tasks\Models\Task', 'task_status.tts_tt_id = task.tt_id', 'task')
            ->join('App\Users\Models\User', 'task.tt_creator_id = creator.su_id', 'creator', 'left')
            ->join('App\Tasks\Models\TaskLabel', 'task.tt_id = task_label.ttl_tt_id', 'task_label', 'left')
            ->join('App\Labels\Models\Label', 'task_label.ttl_sl_id = label.sl_id', 'label', 'left')
            ->join('App\Tasks\Models\TaskUser', 'task.tt_id = task_user.ttu_tt_id', 'task_user', 'left')
            ->join('App\Users\Models\User', 'task_user.ttu_su_id = assignee.su_id', 'assignee', 'left')
            ->filterable()
            ->groupBy('task_status.tts_id');

        if ($project) {
            $query->andWhere('task.tt_sp_id = :project:', array('project' => $project));
        }

        if (is_array($statuses)) {
            $statuses = count($statuses) == 0 ? array(-1) : $statuses;
            $query->inWhere('task_status.tts_status', $statuses);
        }

        $auth = $this->auth->user();
        $query->inWhere('task_user.ttu_su_id', array($auth['su_id']));

        $query->andWhere('task_status.tts_deleted = 0');

        $this->applySearch($query, $payload);
        $this->applyFilter($query, $payload);
        $this->applySorter($query, $payload, $columns);

        $result = $query->paginate()
            ->filter(function($status) use ($params) {
                $status = $status->toArray();
                $task = Task::findFirst($status['tts_tt_id']);

                $item = array();

                $item['task'] = NULL;
                $item['status'] = $status;
                $item['labels'] = array();
                $item['users'] = array();
                $item['document'] = array();
                
                if ($task) {
                    $item['task'] = $task->toArray();
                    
                    $item['labels'] = $task->labels->filter(function($label){ 
                        return array(
                            'sl_id' => $label->sl_id,
                            'sl_label' => $label->sl_label,
                            'sl_color' => $label->sl_color
                        );
                        // return $label->toArray(); 
                    });

                    $item['users'] = $task->users->filter(function($user){ 
                        return array(
                            'su_id' => $user->su_id,
                            'su_email' => $user->su_email,
                            'su_fullname' => $user->getName(),
                            'su_avatar_thumb' => $user->getAvatarThumb()
                        );

                        // return $user->toArray(); 
                    });

                    if (isset($params['tts_id'])) {
                        // load document to
                        $document = Masjid::findFirst($task->tt_document);

                        if ($document) {
                            $item['document'] = $document->toArray();
                        }
                        
                    }
                }

                return $item;
            });

        return $result;
    }

    public function applySearch($query, $params) {
        if (isset($params['query'], $params['fields']) && $params['query'] != '') {
            $pf = array_flip(json_decode($params['fields']));
            $pq = strtoupper($params['query']);
            
            $where = array();
            $bind = array();

            if (isset($pf['author'])) {
                unset($pf['author']);
                $where[] = 'UPPER(creator.su_fullname) LIKE :author:';
                $bind['author'] = '%'.$pq.'%';
            }

            if (isset($pf['label'])) {
                unset($pf['label']);
                $where[] = 'UPPER(label.sl_label) LIKE :label:';
                $bind['label'] = '%'.$pq.'%';
            }

            if (isset($pf['assignee'])) {
                unset($pf['assignee']);
                $where[] = 'UPPER(assignee.su_fullname) LIKE :assignee:';
                $bind['assignee'] = '%'.$pq.'%';
            }

            // extra ?
            if (count($pf) > 0)  {
                $fields = $query->getFields();
                $driver = $this->db->getType();
                $valid  = FALSE;

                if ($driver == 'pgsql') {
                    foreach($pf as $k => $v) {
                        $attr = isset($fields->{$k}) ? $fields->{$k} : FALSE;
                        if ($attr) {
                            $where[] = 'UPPER(CAST('.$attr['field'].' AS VARCHAR)) LIKE :q:';
                            $valid = TRUE;
                        }
                    }
                } else {
                    foreach($pf as $k => $i) {
                        $attr = isset($fields->{$k}) ? $fields->{$k} : FALSE;
                        if ($attr) {
                            $where[] = 'UPPER('.$attr['field'].') LIKE :q:';
                            $valid = TRUE;
                        }
                    }
                }

                if ($valid) {
                    $bind['q'] = '%'.$pq.'%';
                }
            }

            if (count($where) > 0) {
                $where = '('.implode(' OR ', $where).')';
                $query->andWhere($where, $bind);
            }
        }
    }

    public function applyFilter($query, $params) {
        if (isset($params['params'])) {
            $json = json_decode($params['params']);

            if (isset($json->assignee) && count($json->assignee[1]) > 0) {
                $query->inWhere('task_user.ttu_su_id', $json->assignee[1]);
            }
            
            if (isset($json->label) && count($json->label) > 0) {
                $query->inWhere('task_label.ttl_sl_id', $json->label[1]);
            }
            
            if (isset($json->author) && count($json->author) > 0) {
                $query->inWhere('task.tt_creator_id', $json->author[1]);
            }

            if (isset($json->deadline) && count($json->deadline) > 0) {
                $query->inWhere('task.tt_due_date', $json->deadline[1]);
            }

            if (isset($json->status) && count($json->status) > 0) {
                $query->inWhere('task.tt_flag', $json->status[1]);
            }
        }
    }

    public function applySorter($query, $params, $cols) {
        if ( ! isset($params['sort'])) {
            $cols[] = 'MAX(task.tt_created_dt) AS tt_created_dt';
            $query->columns($cols);
            $query->orderBy('tt_created_dt DESC');
        } else {
            $ps = json_decode($params['sort']);

            $sort = array();
            $maps = array(
                'title' => 'tt_title',
                'due' => 'tt_due_date',
                'status' => 'tt_flag'
            );

            foreach($ps as $e) {
                $dirs = $e->direction;
                $aggr = strtoupper($dirs) == 'ASC' ? 'MIN' : 'MAX';

                if (isset($maps[$e->property])) {
                    $name = $maps[$e->property];
                    $sort[] = $name.' '.$dirs;
                    $cols[] = $aggr.'(task.'.$name.') AS '.$name;
                } else if ($e->property == 'created') {
                    $sort[] = 'tt_created_dt '.$dirs;
                    $cols[] = $aggr.'(task.tt_created_dt) AS tt_created_dt';
                } else if ($e->property == 'creator') {
                    $sort[] = 'su_fullname '.$dirs;
                    $cols[] = $aggr.'(creator.su_fullname) AS su_fullname';
                }
            }

            if (count($sort) > 0) {
                $sort = implode(', ', $sort);

                $query->columns($cols);
                $query->orderBy($sort);
            }
        }
    }

    public function gridAction() {
        $params = $this->request->getQuery();
        $query = Task::get()
            ->alias('task')
            ->columns('task.tt_id')
            ->join('App\Users\Models\User', 'task.tt_creator_id = creator.su_id', 'creator', 'left')
            ->join('App\Tasks\Models\TaskLabel', 'task.tt_id = task_label.ttl_tt_id', 'task_label', 'left')
            ->join('App\Labels\Models\Label', 'task_label.ttl_sl_id = label.sl_id', 'label', 'left')
            ->join('App\Tasks\Models\TaskUser', 'task.tt_id = task_user.ttu_tt_id', 'task_user', 'left')
            ->join('App\Users\Models\User', 'task_user.ttu_su_id = assignee.su_id', 'assignee', 'left')
            ->groupBy('task.tt_id');

        $colors = FALSE;

        if (isset($params['project'])) {
            $query->andWhere('task.tt_sp_id = :project:', array('project' => $params['project']));

            $project = Project::findFirst($params['project']);

            if ($project) {
                $worksheet = \App\Kanban\Models\KanbanSetting::findFirst($project->sp_worksheet_id);
                if ($worksheet) {
                    $ws = $worksheet->getWorkspaces();
                    $wp = count($ws) > 0 ? $ws[0] : FALSE;

                    if ($wp) {
                        foreach($wp['deploy'] as $stat => $keys) {
                            if (count($keys) > 0) {
                                $panel = \App\Kanban\Models\KanbanPanel::findFirst($keys[0]);
                                if ($panel) {
                                    $colors[$stat] = $panel->kp_accent;
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->applySearch($query, $params);
        $this->applyFilter($query, $params);
        $this->applySorter($query, $params, array('task.tt_id'));

        $result = $query->paginate()->map(function($row) use ($colors){
            $task = Task::findFirst($row->tt_id);
            $data = $task->toArray();

            $data['statuses'] = $task->getCurrentStatuses()->filter(function($e) use ($colors){ 
                $stat = $e->toArray();
                $stat['status_color'] = Theme::val('var(--paper-blue-grey-500)');
                
                if (isset($colors[$stat['tts_status']])) {
                    $stat['status_color'] = $colors[$stat['tts_status']];
                }
                
                return $stat;
            });

            return $data;
        });

        return $result;
    }

    public function createAction() {
        $post = $this->request->getJson();
        $auth = $this->auth->user();
        
        if (isset($post['worker'], $post['record'], $post['record']['task'])) {
            $worker = $this->bpmn->worker($post['worker']);

            if ($worker) {

                $task = new Task();
                $form = $post['record'];

                if (empty($form['task']['tt_flag'])) {
                    $stat = $worker->statuses();
                    if (isset($stat['data']) && count($stat['data']) > 0) {
                        $init = $stat['data'][0];
                        $form['task']['tt_flag'] = $init['text'];
                    }
                }

                if (empty($form['task']['tt_due_date'])) {
                    $today = new \DateTime();
                    $today->modify('+1 day');
                    $form['task']['tt_due_date'] = $today->format('Y-m-d');
                }

                $form['task']['tt_creator_id'] = $auth['su_id'];
                $form['task']['tt_created_dt'] = date('Y-m-d H:i:s');
                
                if ($task->save($form['task'])) {

                    if (isset($form['document'])) {
                        
                        $document = new Masjid();

                        if ($document->save($form['document'])) {
                            $task->tt_document = $document->id_rumah_ibadah;
                            $task->save();
                        }
                    }

                    if (isset($form['labels'])) {
                        $task->saveLabels($form['labels']);
                    }

                    if (isset($form['users'])) {
                        $task->saveUsers($form['users']);
                    }
                    
                    $data = $task->toArray();
                    $init = $worker->start($data);
                    
                    $affected = array();

                    if ($init['data']) {
                        $status = new TaskStatus();

                        $create = array(
                            'tts_tt_id' => $task->tt_id,
                            'tts_status' => $init['data']['id'],
                            'tts_target' => $init['data']['target'],
                            'tts_worker' => $worker->name(),
                            'tts_deleted' => 0,
                            'tts_created' => date('Y-m-d H:i:s')
                        );

                        if ($status->save($create)) {
                            $affected[] = $status->tts_status;
                        }

                    }

                    $affected = array_values(array_unique($affected));

                    $this->socket->broadcast('notify', array(
                        'type' => 'task-create',
                        'data' => $data
                    ));

                    return array(
                        'success' => TRUE,
                        'data' => array(
                            'affected' => $affected,
                            'task' => $data
                        )
                    );
                }
            }

            return array(
                'success' => FALSE,
                'message' => 'Unable to create task'
            );
        }
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $send = isset($post['send']) ? $post['send'] : FALSE;

        if (isset($post['worker'], $post['record'], $post['record']['task'])) {
            $task = Task::findFirst($id);
            $form = $post['record'];

            if (empty($form['task']['tt_due_date'])) {
                $form['task']['tt_due_date'] = NULL;
            }

            $affected = array();
            $status = NULL;
            $data = array();

            if ($task->save($form['task'])) {

                if (isset($form['document'])) {
                    $document = Masjid::findFirst($form['task']['tt_document']);
                    if ($document) {
                        
                        $document->save($form['document']);

                        if ($send && $form['task']['tt_flag'] == 'DISETUJUI') {
                            $document->aktif = 1;
                            $document->save();
                        }
                    }
                }

                if (isset($form['labels'])) {
                    $task->saveLabels($form['labels']);
                }

                if (isset($form['users'])) {
                    $task->saveUsers($form['users']);
                }

                if ($send) {
                    $worker = $this->bpmn->worker($post['worker']);
                    $remove = array();
                        
                    $curr = $task->getCurrentStatuses();
                    $data = array_merge($form['task'], $task->toArray());

                    foreach($curr as $c) {
                        $next = $worker->next($c->tts_status, $data);
                        
                        $affected[] = $c->tts_status;

                        if (count($next['data']) > 0) {

                            $nextids = array_map(function($n){ return $n['id']; }, $next['data']);
                            
                            // hapus redudansi status jika sudah dibuat oleh orang lain
                            TaskStatus::get()
                                ->inWhere('tts_status', $nextids)
                                ->andWhere('tts_tt_id = :id: and tts_deleted = 0', array('id' => $task->tt_id))
                                ->execute()
                                ->delete();

                            foreach($next['data'] as $n) {

                                $affected[] = $n['id'];

                                $found = TaskStatus::findFirst(array(
                                    'tts_tt_id = :id: AND tts_status = :status: AND tts_deleted = 0',
                                    'bind' => array(
                                        'id' => $task->tt_id,
                                        'status' => $n['id']
                                    )
                                ));

                                if ( ! $found) {
                                    $model = new TaskStatus();
                                    $create = array(
                                        'tts_tt_id' => $task->tt_id,
                                        'tts_status' => $n['id'],
                                        'tts_target' => $n['target'],
                                        'tts_worker' => $worker->name(),
                                        'tts_deleted' => 0,
                                        'tts_created' => date('Y-m-d H:i:s')
                                    );
                                    
                                    if ($model->save($create)) {
                                        $remove[] = $c;

                                        if (is_null($status)) {
                                            $status = $model->toArray();
                                        }
                                    }
                                } else {
                                    // $remove[] = $c;
                                }
                            }
                        }

                    }

                    // nothing changed
                    if (count($remove) > 0) {
                        foreach($remove as $m) {
                            $m->save(array('tts_deleted' => 1));
                        }
                    }
                } else {
                    $curr = $task->getCurrentStatuses();
                    $data = $task->toArray();

                    foreach ($curr as $c) {
                        $affected[] = $c->tts_status;                 
                        
                        if (is_null($status)) {
                            $status = $c->toArray();
                        }
                    }
                }
            }

            $affected = array_values(array_unique($affected));
            
            $this->socket->broadcast('notify', array(
                'type' => 'task-update',
                'data' => $data
            ));

            return array(
                'success' => TRUE,
                'data' => array(
                    'affected' => $affected,
                    'task' => $data,
                    'status' => $status
                )
            ); 
        }

        return array(
            'success' => FALSE
        );

    }

    public function deleteAction($id) {
        $task = Task::get($id)->data;
        $done = FALSE;

        if ($task) {

            $document = Masjid::findFirst($task->tt_document);
            $remove = FALSE;

            if ($document) {
                if ($task->tt_flag != 'DISETUJUI') {
                    $document->delete();
                    $remove = TRUE;
                }
            } else {
                $remove = TRUE;
            }

            if ($remove) {
                $data = $task->toArray();
                $done = $task->delete();

                if ($done) {
                    $this->socket->broadcast('notify', array(
                        'type' => 'task-delete',
                        'data' => $data
                    ));
                }
            }
        }

        return array(
            'success' => $done
        );
    }

}