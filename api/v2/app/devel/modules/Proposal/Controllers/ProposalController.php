<?php
namespace App\Proposal\Controllers;

use App\Proposal\Models\Proposal;
use App\Ponpes\Models\Ponpes;
use App\Masjid\Models\Masjid;

class ProposalController extends \Micro\Controller {

    public function findAction() {
        $result = Proposal::get()->filterable()->sortable()->paginate();

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

}