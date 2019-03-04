<?php
namespace App\Reports\Controllers;

use App\Masjid\Models\Masjid;
use Micro\Office\Spreadsheet;

class MasjidController extends \Micro\Controller {

    public function findAction(){}
    public function findByIdAction(){}

    public function databaseAction(){
        set_time_limit(0);
        
        $post = $this->request->getJson();
        
        $query = Masjid::get()
            ->alias('a')
            ->columns(array(
                'a.id_rumah_ibadah',
                'a.nama_rumah_ibadah',
                'a.id_jenis',
                'b.nama_jenis',
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
                'a.stat_tanah_bangunan',
                'a.luas_bangunan',
                'a.luas_tanah'
            ))
            ->join('App\MasterMasjid\Models\Jenis', 'a.id_jenis = b.id_jenis', 'b', 'left')
            ->join('App\MasterWilayah\Models\Kecamatan', 'a.id_kecamatan = c.id_kecamatan', 'c', 'left')
            ->join('App\MasterWilayah\Models\Kota', 'a.id_kota = d.id_kota', 'd', 'left')
            ->andWhere('a.aktif = 1');

        if (isset($post['params'])) {
            $params = $post['params'];
            if ( ! empty($params['id_jenis'])) {
                $query->andWhere('a.id_jenis = :id_jenis:', array('id_jenis' => $params['id_jenis']));
            }

            if ( ! empty($params['id_kota'])) {
                $query->andWhere('a.id_kota = :id_kota:', array('id_kota' => $params['id_kota']));
            }
        }


        $result = $query->execute();

        $excel = new Spreadsheet(dirname(__DIR__).'/Templates/database-masjid.xlsx');
        
        $sheet = $excel->getSheet(0);

        // title & subtitle
        $title = 'DATA RUMAH IBADAH';
        $subtitle = 'TAHUN '.date('Y');
        
        $sheet->setCellValue('A1', $title);
        $sheet->setCellValue('A2', $subtitle);

        $row = 7;
        $num = 1;

        foreach($result as $item) {
            $sheet->getRowDimension($row)->setRowHeight(30);

            $sheet
                ->setCellValue('A'.$row, $num)
                ->setCellValue('B'.$row, $item->nama_rumah_ibadah)
                ->setCellValue('C'.$row, $item->nama_jenis);

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
                ->setCellValue('Y'.$row, $item->stat_tanah_bangunan)
                ->setCellValue('Z'.$row, $item->luas_bangunan)
                ->setCellValue('AA'.$row, $item->luas_tanah);

            $num++;
            $row++;
        }

        $range = 'A7:AA'.$row;
        Spreadsheet::applyBorderStyle($sheet, $range, 'allBorders');

        $excel->stream('rumahibadah.xlsx');


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