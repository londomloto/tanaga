<?php
namespace App\Proposal\Controllers;

use App\Proposal\Models\Proposal;
use App\Ponpes\Models\Ponpes;
use App\Masjid\Models\Masjid;

class ProposalController extends \Micro\Controller {

    public function findAction() {
        return Proposal::get()->filterable()->sortable()->paginate();

        $result->data = $result->data->filter(function($row){
            $arr = $row->toArray();
            if ($arr['lembaga'] == 'Pendidikan') {
                $lembaga = Ponpes::findFirst($arr['id_lembaga']);
                if ($lembaga) {
                    $arr['nama_lembaga'] = $lembaga->nama_ponpes;
                }
            } else {
                $lembaga = Masjid::findFirst($arr['id_lembaga']);
                if ($lembaga) {
                    $arr['nama_lembaga'] = $lembaga->nama_rumah_ibadah;
                }
            }
            return $arr;
        });

        return $result;
    }

    public function uploadAction() {
        $done = FALSE;
        $data = NULL;
        $message = NULL;

        if ($this->request->hasFiles()) {
            $this->uploader->initialize(array(
                'path' => APPPATH.'public/resources/proposal/',
                'types' => array('jpg', 'jpeg', 'png'),
                'encrypt' => TRUE
            ));

            if ($this->uploader->upload()) {
                $info = $this->uploader->getResult();
                $done = TRUE;
                $data = array(
                    'nama_file' => $info->filename,
                    'thumbnail' => $this->url->getSiteUrl('assets/thumb').'?s=public/resources/proposal/'.$info->filename
                );
            } else {
                $message = $this->uploader->getMessage();
            }
        }

        return array(
            'success' => $done,
            'data' => $data,
            'message' => $message
        );
    }

}