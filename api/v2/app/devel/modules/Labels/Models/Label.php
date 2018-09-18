<?php
namespace App\Labels\Models;

class Label extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'sl_sp_id',
            'App\Projects\Models\Project',
            'sp_id',
            array(
                'alias' => 'Project'
            )
        );
    }

    public function getSource() {
        return 'sys_labels';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        if ($this->project) {
            $data['sl_sp_title'] = $this->project->sp_title;
        }

        return $data;
    }
}