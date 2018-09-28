<?php
namespace App\Proposal\Controllers;

use App\Ponpes\Models\Ponpes;
use App\Masjid\Models\Masjid;

class LembagaController extends \Micro\Controller {

    public function findAction() {

        $payload = $this->request->getQuery();
        $lembaga = isset($payload['lembaga']) ? $payload['lembaga'] : 'Pendidikan';

        switch($lembaga) {
            case 'Pendidikan':

                return Ponpes::get()
                    ->columns(array(
                        'id_ponpes AS value',
                        'nama_ponpes AS label'
                    ))
                    ->filterable()
                    ->paginate();

            case 'Sarana Ibadah':
                
                return Masjid::get()
                    ->columns(array(
                        'id_rumah_ibadah AS value',
                        'nama_rumah_ibadah AS label'
                    ))
                    ->filterable()
                    ->paginate();
        }

    }

}