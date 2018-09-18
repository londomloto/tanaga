<?php
namespace App\Users\Models;

class UserKanban extends \Micro\Model {

    public function initialize() {
        $this->belongsTo(
            'suk_ks_id',
            'App\Kanban\Models\KanbanSetting',
            'ks_id',
            array(
                'alias' => 'Kanban'
            )
        );
    }

    public function getSource() {
        return 'sys_users_kanban';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        $kans = $this->kanban;

        $data['suk_ks_name'] = $kans ? $kans->ks_name : NULL;
        $data['suk_ks_description'] = $kans ? $kans->ks_description : NULL;
        
        return $data;
    }
}