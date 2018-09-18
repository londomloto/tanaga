<?php
namespace App\Tasks\Models;

class TaskStatus extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'tts_status',
            'App\Bpmn\Models\Link',
            'id',
            array(
                'alias' => 'Status'
            )
        );

        $this->belongsTo(
            'tts_tt_id',
            'App\Tasks\Models\Task',
            'tt_id',
            array(
                'alias' => 'Task'
            )
        );

        $this->belongsTo(
            'tts_status',
            'App\Kanban\Models\KanbanStatus',
            'kst_status',
            array(
                'alias' => 'KanbanStatus'
            )
        );
    }

    public function getSource() {
        return 'trx_tasks_statuses';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $data['status_text'] = NULL;

        if ($this->status) {
            $data['status_text'] = $this->status->label;
        }


        return $data;
    }
}