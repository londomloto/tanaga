<?php
namespace App\Proposal\Models;

class Attachment extends \Micro\Model {

    public function getSource() {
        return 'x_tbl_proposal_file';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);
        if ( ! empty($data['nama_file'])) {
            $data['thumbnail'] = \Micro\App::getDefault()->url->getSiteUrl('assets/thumb').'?s=public/resources/proposal/'.$data['nama_file'];
            $data['url_file'] = \Micro\App::getDefault()->url->getBaseUrl().'public/resources/proposal/'.$data['nama_file'];
        }
        return $data;
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

}