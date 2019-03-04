<?php
namespace App\Reports\Controllers;

use App\Ponpes\Models\Ponpes;
use Micro\Office\Spreadsheet;

class PonpesController extends \Micro\Controller {

    public function findAction(){}
    public function findByIdAction(){}

    public function databaseAction(){
        set_time_limit(0);

        $post = $this->request->getJson();
        
        $query = Ponpes::get()
            ->alias('a')
            ->columns(array(
                'a.id_ponpes',
                'a.nama_ponpes',
                'a.id_tipe',
                'b.nama_tipe',
                'a.alamat',
                'a.kelurahan',
                'a.id_kecamatan',
                'c.nama_kecamatan',
                'a.id_kota',
                'd.nama_kota',
                'a.nomor_sk',
                'a.tanggal_sk',
                'a.nama_statistik_baru',
                'a.thn_berdiri_masehi',
                'a.yayasan',
                'a.penyelenggara'
            ))
            ->join('App\MasterPonpes\Models\Tipe', 'a.id_tipe = b.id_tipe', 'b', 'left')
            ->join('App\MasterWilayah\Models\Kecamatan', 'a.id_kecamatan = c.id_kecamatan', 'c', 'left')
            ->join('App\MasterWilayah\Models\Kota', 'a.id_kota = d.id_kota', 'd', 'left')
            ->andWhere('a.aktif = 1');

        if (isset($post['params'])) {
            $params = $post['params'];
            if ( ! empty($params['id_tipe'])) {
                $query->andWhere('a.id_tipe = :id_tipe:', array('id_tipe' => $params['id_tipe']));
            }

            if ( ! empty($params['id_kota'])) {
                $query->andWhere('a.id_kota = :id_kota:', array('id_kota' => $params['id_kota']));
            }
        }


        $result = $query->execute();

        $excel = new Spreadsheet(dirname(__DIR__).'/Templates/database-ponpes.xlsx');
        
        $sheet = $excel->getSheet(0);

        // title & subtitle
        $title = 'DATA PONDOK PESANTREN';
        $subtitle = 'TAHUN '.date('Y');

        $sheet->setCellValue('A1', $title);
        $sheet->setCellValue('A2', $subtitle);

        $row = 7;
        $num = 1;

        foreach($result as $item) {
            $sheet->getRowDimension($row)->setRowHeight(30);

            $sheet
                ->setCellValue('A'.$row, $num)
                ->setCellValue('B'.$row, $item->nama_ponpes)
                ->setCellValue('C'.$row, $item->nama_tipe);

            $addrs = array();
            
            if ( ! empty($item->alamat)) {
                $addrs[] = $item->alamat;
            }

            if ( ! empty($item->kelurahan)) {
                $addrs[] = $item->kelurahan;
            }

            $addrs = implode(', ', $addrs);

            $sheet
                ->setCellValue('D'.$row, $addrs)
                ->setCellValue('E'.$row, $item->nama_kecamatan)
                ->setCellValue('F'.$row, $item->nama_kota);

            if ( ! empty($item->nomor_sk)) {
                $parts = explode('/', $item->nomor_sk);
                
                $thn = array_pop($parts);
                $nmr = array_pop($parts);
                $kde = implode('/', $parts);

                $sheet
                    ->setCellValue('G'.$row, $kde)
                    ->setCellValue('H'.$row, $nmr)
                    ->setCellValue('I'.$row, $thn);
            }

            if ( ! empty($item->tanggal_sk)) {
                $parts = explode('/', $item->tanggal_sk);
                if (count($parts) == 3) {
                    $month = ((int)$parts[1] - 1);
                    $tanggal = $parts[0].' '.self::__monthname($month).' '.$parts[2];
                    $sheet->setCellValue('J'.$row, $tanggal);
                }
            }

            if ( ! empty($item->nama_statistik_baru)) {
                $nomor = str_replace(array('.', '/', '-'), '', (string)$item->nama_statistik_baru);
                $parts = str_split($nomor);
                if (count($parts) == 12) {
                    $col = 'K';
                    for($i = 0; $i < 12; $i++) {
                        $sheet->setCellValue($col.$row, $parts[$i]);
                        $col++;
                    }
                }
            }

            $sheet
                ->setCellValue('W'.$row, $item->thn_berdiri_masehi)
                ->setCellValue('X'.$row, trim($item->yayasan))
                ->setCellValue('AA'.$row, $item->penyelenggara);

            $num++;
            $row++;
        }

        $range = 'A7:AA'.$row;
        Spreadsheet::applyBorderStyle($sheet, $range, 'allBorders');

        $excel->stream('ponpes.xlsx');


    }

    public function documentAction() {

    }

    private static function __monthname($index) {

        $months = array(
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'Nopember',
            'Desember'
        );

        return isset($months[$index]) ? $months[$index] : '';
    }
}