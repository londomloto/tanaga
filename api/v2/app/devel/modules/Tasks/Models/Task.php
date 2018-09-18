<?php
namespace App\Tasks\Models;

use App\Tasks\Models\TaskLabel,
    App\Tasks\Models\TaskUser,
    App\Tasks\Models\TaskActivity,
    Phalcon\Mvc\Model\Relation;

class Task extends \Micro\Model {

    private $__snapshot = NULL;
    private $__loggable = TRUE;

    public function initialize() {
        
        $this->hasMany(
            'tt_id',
            'App\Tasks\Models\TaskStatus',
            'tts_tt_id',
            array(
                'alias' => 'Statuses',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasOne(
            'tt_creator_id',
            'App\Users\Models\User',
            'su_id',
            array(
                'alias' => 'Creator'
            )
        );

        $this->belongsTo(
            'tt_sp_id',
            'App\Projects\Models\Project',
            'sp_id',
            array(
                'alias' => 'Project'
            )
        );

        $this->hasMany(
            'tt_id',
            'App\Tasks\Models\TaskLabel',
            'ttl_tt_id',
            array(
                'alias' => 'TaskLabels',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasManyToMany(
            'tt_id',
            'App\Tasks\Models\TaskLabel',
            'ttl_tt_id',
            'ttl_sl_id',
            'App\Labels\Models\Label',
            'sl_id',
            array(
                'alias' => 'Labels'
            )
        );

        $this->hasManyToMany(
            'tt_id',
            'App\Tasks\Models\TaskUser',
            'ttu_tt_id',
            'ttu_su_id',
            'App\Users\Models\User',
            'su_id',
            array(
                'alias' => 'Users'
            )
        );

        $this->hasMany(
            'tt_id',
            'App\Tasks\Models\TaskUser',
            'ttu_tt_id',
            array(
                'alias' => 'TaskUsers',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'tt_id',
            'App\Tasks\Models\TaskActivity',
            'tta_tt_id',
            array(
                'alias' => 'Activities',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );
    }

    public function getSource() {
        return 'trx_tasks';
    }

    public function suspendLog() {
        $this->__loggable = FALSE;
    }

    public function resumeLog() {
        $this->__loggable = TRUE;
    }

    public function afterFetch() {
        
        if (isset(
            $this->tt_id, 
            $this->tt_title,
            $this->tt_desc,
            $this->tt_due_date,
            $this->tt_flag
        )) {
            $this->__snapshot = array(
                'tt_id' => $this->tt_id,
                'tt_title' => $this->tt_title,
                'tt_desc' => $this->tt_desc,
                'tt_due_date' => $this->tt_due_date,
                'tt_flag' => $this->tt_flag
            );    
        }
    }

    public function afterCreate() {
        if ($this->__loggable) {
            TaskActivity::log('create', array(
                'tta_tt_id' => $this->tt_id,
                'tta_data' => $this->tt_title
            ));
        }
    }

    public function afterUpdate() {
        if ($this->__loggable) {
            $snapshot = $this->__snapshot;
            if ( ! is_null($snapshot) && ! empty($snapshot['tt_id'])) {
                if ($snapshot['tt_title'] != $this->tt_title) {
                    TaskActivity::log('update_title', array(
                        'tta_tt_id' => $this->tt_id,
                        'tta_data' => $this->tt_title
                    ));
                }

                if ($snapshot['tt_desc'] != $this->tt_desc) {
                    TaskActivity::log('update_detail', array(
                        'tta_tt_id' => $this->tt_id,
                        'tta_data' => $this->tt_desc
                    ));
                }

                if ($snapshot['tt_flag'] != $this->tt_flag) {
                    TaskActivity::log('update_flag', array(
                        'tta_tt_id' => $this->tt_id,
                        'tta_data' => $this->tt_flag
                    ));
                }

                if ($snapshot['tt_due_date'] != $this->tt_due_date) {
                    TaskActivity::log('update_due', array(
                        'tta_tt_id' => $this->tt_id,
                        'tta_data' => $this->tt_due_date
                    ));
                }
            }
        }
    }

    public function toArray($columns = NULL) {
        $auth = \Micro\App::getDefault()->auth->user();
        $data = parent::toArray($columns);
        $data['creator_su_fullname'] = '';

        if ($this->creator) {
            $data['creator_su_fullname'] = $this->creator->getName();
        }

        $data['tt_created_dt_relative'] = self::__relativeTime($this->tt_created_dt);
        $data['tt_created_dt_formatted'] = self::__formatDate($this->tt_created_dt);
        $data['tt_due_date_relative'] = self::__relativeTime($this->tt_due_date, 'M dS, Y');
        $data['tt_due_date_formatted'] = self::__formatDate($this->tt_due_date, 'M dS, Y');

        $data['tt_is_editable'] = FALSE;

        if ($auth['su_id'] == $data['tt_creator_id']) {
            $data['tt_is_editable'] = TRUE;
        }

        return $data;
    }

    public function getRelativeDue() {
        return self::__relativeTime($this->tt_due_date, 'M dS, Y');
    }

    public function getFormattedDue() {
        return self::__formatDate($this->tt_due_date, 'M dS, Y');   
    }

    public function getRelativeCreated() {
        return self::__relativeTime($this->tt_created_dt);
    }

    public function getCurrentStatuses() {
        return $this->getStatuses(array(
            'conditions' => 'tts_deleted = 0',
            'orderBy' => 'tts_created DESC'
        ));
    }

    public function saveLabels($post) {
        $created = array();
        $updated = array();
        $current = array();

        foreach($this->getLabels() as $e) {
            $current[$e->sl_id] = TRUE;
        }

        foreach($post as $e) {
            if (isset($current[$e['sl_id']])) {
                $updated[] = $e;
                unset($current[$e['sl_id']]);
            } else {
                $created[] = $e;
            }
        }

        $current = array_values(array_keys($current));

        if (count($current) > 0) {
            TaskLabel::get()
                ->inWhere('ttl_sl_id', $current)
                ->andWhere('ttl_tt_id = :task:', array('task' => $this->tt_id))
                ->execute()
                ->delete();

            TaskActivity::log('remove_label', array(
                'tta_tt_id' => $this->tt_id,
                'tta_data' => json_encode($current)
            ));
        }

        if (count($created) > 0) {
            $indexes = array();

            foreach($created as $e) {
                $r = new TaskLabel();
                $r->ttl_tt_id = $this->tt_id;
                $r->ttl_sl_id = $e['sl_id'];
                $r->save();

                $indexes[] = $e['sl_id'];
            }

            TaskActivity::log('add_label', array(
                'tta_tt_id' => $this->tt_id,
                'tta_data' => json_encode($indexes)
            ));
        }
    }

    public function saveUsers($post) {
        $created = array();
        $updated = array();
        $current = array();

        foreach($this->getUsers() as $e) {
            $current[$e->su_id] = TRUE;
        }

        foreach($post as $e) {
            if (isset($current[$e['su_id']])) {
                $updated[] = $e;
                unset($current[$e['su_id']]);
            } else {
                $created[] = $e;
            }
        }

        $current = array_values(array_keys($current));

        if (count($current) > 0) {
            TaskUser::get()
                ->inWhere('ttu_su_id', $current)
                ->andWhere('ttu_tt_id = :task:', array('task' => $this->tt_id))
                ->execute()
                ->delete();

            TaskActivity::log('remove_user', array(
                'tta_tt_id' => $this->tt_id,
                'tta_data' => json_encode($current)
            ));
        }

        if (count($created) > 0) {
            $indexes = array();

            foreach($created as $e) {
                $r = new TaskUser();

                $r->ttu_tt_id = $this->tt_id;
                $r->ttu_su_id = $e['su_id'];
                $r->save();

                $indexes[] = $e['su_id'];
            }

            TaskActivity::log('add_user', array(
                'tta_tt_id' => $this->tt_id,
                'tta_data' => json_encode($indexes)
            ));
        }
    }

    private static function __timezone() {
        static $zone;

        if (is_null($zone)) {
            $conf = \Micro\App::getDefault()->config->app;
            $zone = 'Asia/Jakarta';
            if ($conf->offsetExists('timezone')) {
                $zone = $conf->timezone;
            }
        }
        return $zone;
    }

    private static function __formatDate($date, $format = "M dS, Y h:i a") {
        if (empty($date)) {
            return '';
        }
        
        $zone = self::__timezone();
        $date = new \Moment\Moment(strtotime($date), $zone);
        return $date->format($format);
    }

    private static function __relativeTime($date, $format = "M dS, Y h:i a") {
        $zone = self::__timezone();
        $date = new \Moment\Moment(strtotime($date), $zone);
        $diff = $date->fromNow();

        if ($diff->getDirection() == 'past') {
            return 'about '.$diff->getRelative();
        } else {
            return 'at '.$date->format($format);
        }
    }
}