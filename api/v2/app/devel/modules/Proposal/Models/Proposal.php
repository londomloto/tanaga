<?php
namespace App\Proposal\Models;

use App\Ponpes\Models\Ponpes;
use App\Masjid\Models\Masjid;

class Proposal extends \Micro\Model {

    public function initialize() {
        $this->hasMany(
            'id_proposal',
            'App\Proposal\Models\Attachment',
            'id_proposal',
            array(
                'alias' => 'Attachments',
                'foreignKey' => array(
                    'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
                )
            )
        );
    }

    public function getSource() {
        return 'x_tbl_proposal';
    }

    public function getEditorName() {
        $user = \Micro\App::getDefault()->auth->user();
        $part = explode(' ', $user['su_fullname']);
        $name = $part[0];

        return $name; 
    }

    public function beforeCreate() {
        $this->create_date = date('Y-m-d H:i:s');
        $this->create_user = $this->getEditorName();
    }

    public function beforeUpdate() {
        $this->last_edit_date = date('Y-m-d H:i:s');
        $this->last_edit_user = $this->getEditorName();
    }

    public function saveAttachments($items) {
        $this->getAttachments()->delete();
        foreach($items as $item) {
            $att = new Attachment();

            $att->id_proposal = $this->id_proposal;
            $att->nama_file = $item['nama_file'];
            $att->tipe_file = 'IMAGE';
            
            $att->save();
        }
    }
}