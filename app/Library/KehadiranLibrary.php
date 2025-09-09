<?php

namespace App\Library;

use App\Models\Presensi;
use App\Models\LogPresensi;
use App\Models\Pegawai;
use App\Models\JadwalShift;
use App\Models\JadwalNonShift;
use App\Models\AllowedIp;
use App\Models\Cuti;
use App\Models\Config;
use App\Models\TokenPresensi;
use App\Models\Lokasi;
use App\Models\LokasiPegawai;
use App\Models\PeriodeKerja;
use App\Models\PegawaiCutiBersama;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class KehadiranLibrary {
  function getKehadiran($pegawaiId, $tglAwal, $tglAkhir) {
    $cutis =  Cuti::join('jenis_cutis', 'cutis.jenis_cuti_id', '=', 'jenis_cutis.id')
      ->leftJoin('cuti_lains', 'cutis.cuti_lain_id', '=', 'cuti_lains.id')
      ->where('pegawai_id', $pegawaiId)
      ->whereIn('status', ['Disetujui', 'Selesai'])
      ->select('cutis.*', 'jenis_cutis.nama_jenis_cuti', 'cuti_lains.nama_cuti_lain')
      ->get();

    $presensis = Presensi::where('pegawai_id', $pegawaiId)
      ->where('tgl_presensi', '>=', $tglAwal)
      ->where('tgl_presensi', '<=', $tglAkhir)
      ->get();

    $cutiBersamas = PegawaiCutiBersama::join('cuti_bersamas', 'cuti_bersama_id', '=', 'cuti_bersamas.id')
      ->where('pegawai_id', $pegawaiId)
      ->where('tgl_cuti', '>=', $tglAwal)
      ->where('tgl_cuti', '<=', $tglAkhir)
      ->where('diambil', '1')
      ->where('cuti_bersamas.aktif', 1)
      ->get();

    $periods = CarbonPeriod::create(Carbon::parse($tglAwal), Carbon::parse($tglAkhir));
    $listTgl= [];

    foreach($periods as $date) {
        $listTgl[] = $date->toDateString();
    }
    

    $kehadirans = array();
    $tglKosong = array(); //menampung tanggal yang belum masuk presensi

    /*
    *cek presensi
    */
    $jmlPresensi = 0;
    $jmlHadirTepat = 0;
    $jmlTerlambat = 0;
    $jmlPulangCepat = 0;
    $jmlTidakPresensiPulang = 0;

    if ($presensis !== null) {
      $presensiArray = $presensis->toArray();
    
      foreach($listTgl as $tgl) {
        $listTglPresensis = array_column($presensiArray, 'tgl_presensi');

        if (in_array($tgl, $listTglPresensis)) {
          $indexes = array_keys($listTglPresensis, $tgl);
          
          foreach($indexes as $index) {
            //cek ketepatan hadir
            $presensi = $presensiArray[$index];
            $presensiIn = Carbon::parse($presensi['presensi_in']);
            $presensiOut = Carbon::parse($presensi['presensi_out']);
            $jadwalIn = Carbon::parse($presensi['jadwal_in']);
            $jadwalOut = Carbon::parse($presensi['jadwal_out']);

            if ($presensi['presensi_out']!= '0000-00-00 00:00:00') {
              if ($presensiIn <= $jadwalIn && $presensiOut >= $jadwalOut && $presensi['status'] == 'valid') {
                $jmlHadirTepat++;
              } else {
                if($presensiIn > $jadwalIn && $presensi['status'] == 'valid') {
                  $jmlTerlambat++;
                }

                if ($presensiOut < $jadwalOut && $presensi['status'] == 'valid') {
                  $jmlPulangCepat++;
                }
              }
            } else {
              if ($presensi['flag'] != 'masuk'  && $presensi['status'] == 'valid') {
                $jmlTidakPresensiPulang++;
              }
            }

            $kehadirans[] = [
                'tgl' => $tgl,
                'jenis_kehadiran' => 'presensi',
                'data' => $presensiArray[$index]
              ];

            if ($presensi['flag'] != 'masuk' && $presensi['status'] == 'valid') {
              $jmlPresensi++;
            }
          }
        } else {
          $tglKosong[] = $tgl;
        }
      }
    }

    /*
    * cek cuti bersama
    */
    $jmlCutiBersama = 0;
    if (count($tglKosong) > 0 && $cutiBersamas !== null) {
      $cutiBersamaArray = $cutiBersamas->toArray();

      foreach($tglKosong as $tgl) {
        $listTglCutiBersama = array_column($cutiBersamaArray, 'tgl_cuti');

        if (in_array($tgl, $listTglCutiBersama)) {
          $index = array_keys($listTglCutiBersama, $tgl)[0];
          
          $kehadirans[] = [
              'tgl' => $tgl,
              'jenis_kehadiran' => 'cuti_bersama',
              'data' => $cutiBersamaArray[$index]
            ];

          $jmlCutiBersama++;
          
          //hapus item dari array $tglKosong[$tgl]
          if (in_array($tgl, $tglKosong)) {
            $indexTglKosong = array_keys($tglKosong, $tgl)[0];
            unset($tglKosong[$indexTglKosong]);
          }
        }
      }
    }
    /*
    * cek cuti
    */
    $jmlCutiDitanggung = 0;
    $jmlCutiTidakDitanggung = 0;

    if (count($tglKosong) > 0 && $cutis !== null) {
      foreach($tglKosong as $tgl) {
        foreach($cutis as $cuti) {
          $tglMulaiCuti = Carbon::parse($cuti->tgl_mulai);
          $tglSelesaiCuti = Carbon::parse($cuti->tgl_selesai);
          $tglObj = Carbon::parse($tgl);
          
          if ($tglObj >= $tglMulaiCuti && $tglObj <= $tglSelesaiCuti) {
            //tambahkan item ke $kehadirans
            $kehadirans[] = [
              'tgl' => $tgl,
              'jenis_kehadiran' => 'cuti',
              'data' => $cuti
            ];

            if ($cuti->jenis_cuti_id == 8) {
              $jmlCutiTidakDitanggung++;
            } else {
              $jmlCutiDitanggung++;
            } 

            //hapus item dari array $tglKosong[$tgl]
            if (in_array($tgl, $tglKosong)) {
              $indexTglKosong = array_keys($tglKosong, $tgl)[0];
              unset($tglKosong[$indexTglKosong]);
            }
          }
        }
      }
    }    

    /*
    * sisa hari di $tglKosong dimasukkan ke kehadiran
    */
    foreach($tglKosong as $tgl) {
      $kehadirans[] = [
        'tgl' => $tgl,
        'jenis_kehadiran' => 'tidak_hadir',
        'data' => null
      ];
    }
   
    array_multisort(array_column($kehadirans, 'tgl'), SORT_ASC, $kehadirans);

    $jumlah['pegawai_id'] = $pegawaiId;
    $jumlah['presensi'] = $presensis !== null ? $jmlPresensi : 0;
    $jumlah['cuti_bersama'] = $jmlCutiBersama;
    $jumlah['cuti_ditanggung'] = $jmlCutiDitanggung;
    $jumlah['cuti_tidak_ditanggung'] = $jmlCutiTidakDitanggung;
    $jumlah['hadir_tepat'] = $jmlHadirTepat;
    $jumlah['pulang_cepat'] = $jmlPulangCepat;
    $jumlah['terlambat'] = $jmlTerlambat;
    $jumlah['tidak_presensi_pulang'] = $jmlTidakPresensiPulang;

    return json_encode(compact('kehadirans', 'jumlah'));
  }
}
