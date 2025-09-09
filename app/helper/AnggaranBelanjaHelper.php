<?php
use App\Models\AnggaranBelanja;
use App\Models\UraianRealisasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

function getUraian($realisasiId) {
  $uraians = UraianRealisasi::from('uraian_realisasis as ur')
    ->leftJoin('sumber_anggaran_realisasis as sar', 'sar.uraian_realisasi_id', '=', 'ur.id')
    ->join('rka_diajukans as rka', 'rka.uraian_realisasi_id', '=', 'ur.id')
    ->leftJoin('anggaran_belanjas as ab', 'sar.anggaran_belanja_id', '=', 'ab.id')
    ->leftJoin('anggaran_belanjas as ab2', 'rka.rka_id', '=', 'ab2.id')
    ->join('realisasis as r', 'r.id', 'ur.realisasi_id')
    ->join('prokers as p', 'p.id', '=', 'ab2.proker_id')
    ->join('units as u', 'u.id', '=', 'p.unit_id')
    ->where('ur.realisasi_id', $realisasiId)
    ->whereNotIn('r.status', ['dibatalkan', 'ditolak'])
    ->select('ur.id as uraian_realisasi_id', 'u.nama_unit', 'p.id as proker_id', 'p.nama_proker', 'ab.*')
    //->groupBy('sar.anggaran_belanja_id')
    ->orderBy('ur.id')
    ->get();
    
  return $uraians;
} 

/*
 * 
 */
function getAnggaranBelanja($realisasiId) {
  $anggaranBelanjas = AnggaranBelanja::with([
    // 'sumberAnggaranRealisasi.uraianRealisasi' => function ($query) {
    //   $query->select('uraian_realisasis.*')
    //         ->leftJoin('sumber_anggaran_realisasis as sar', 'sar.uraian_realisasi_id', '=', 'uraian_realisasis.id')
    //         ->leftJoin('realisasis as r', 'r.id', '=', 'uraian_realisasis.realisasi_id')
    //         ->join('anggaran_belanjas as ab', 'ab.id', '=', 'sar.anggaran_belanja_id')
    //         ->whereNotIn('r.status', ['dibatalkan', 'ditolak'])
    //         ;
    // }, 
    'rkaDiajukan' => function($query) use ($realisasiId) {
      $query->leftJoin('uraian_realisasis', 'rka_diajukans.uraian_realisasi_id', '=', 'uraian_realisasis.id')
        ->leftJoin('realisasis as r', 'r.id', '=', 'uraian_realisasis.realisasi_id')
        ->join('anggaran_belanjas as ab', 'ab.id', '=', 'rka_diajukans.rka_id')
        ->where('uraian_realisasis.realisasi_id', $realisasiId)
        ;
    },
    'rkaDiajukan.uraianRealisasi' => function ($query) use ($realisasiId) {
      $query->select('uraian_realisasis.*')
            ->leftJoin('rka_diajukans as rka', 'rka.uraian_realisasi_id', '=', 'uraian_realisasis.id')
            ->leftJoin('realisasis as r', 'r.id', '=', 'uraian_realisasis.realisasi_id')
            ->join('anggaran_belanjas as ab', 'ab.id', '=', 'rka.rka_id')
            ->where('uraian_realisasis.realisasi_id', $realisasiId)
            ;
    }, 
    'rkaDiajukan.uraianRealisasi.sumberAnggaranRealisasi.anggaranBelanja'
    ]
  )
  ->distinct('sar.anggaran_belanja_id')
  
  ->leftJoin('sumber_anggaran_realisasis as sar', 'sar.anggaran_belanja_id', '=', 'anggaran_belanjas.id')
  ->leftJoin('uraian_realisasis as ur', 'ur.id', '=', 'sar.uraian_realisasi_id')
  
  ->leftJoin('rka_diajukans as rka', 'rka.rka_id', '=', 'anggaran_belanjas.id')
  ->leftJoin('uraian_realisasis as ur2', 'ur2.id', '=', 'rka.uraian_realisasi_id')
  
  ->join('realisasis as r', 'r.id', 'ur2.realisasi_id')
  ->join('prokers as p', 'p.id', '=', 'anggaran_belanjas.proker_id')
  ->join('units as u', 'u.id', '=', 'p.unit_id')

  ->where('ur2.realisasi_id', $realisasiId)
  ->whereNotIn('r.status', ['dibatalkan', 'ditolak'])
  
  ->select('ur2.id as uraian_realisasi_id', 'u.nama_unit', 'p.id as proker_id', 'p.nama_proker', 'anggaran_belanjas.*', 'sar.anggaran_belanja_id')
  ->groupBy('anggaran_belanjas.id')
  ->orderBy('ur2.id')
  ->get();

  //uraian realisasi
  $uraianRealisasis = getUraianRealisasi($realisasiId);
   
  $uraianIds = $uraianRealisasis->pluck('id')->toArray(); // f in RealizationHelper

  //tahap dibawah ini untuk meng-handle nilai pengajuan dari unit, tim pengadaan dan keuangan
  $isPengajuanUnit = count($uraianRealisasis->filter(function ($f) {
      return $f['qty'] !== null;
  })) > 0;

  $isPengajuanTim = count($uraianRealisasis->filter(function ($f) {
      return $f['qty_tim'] !== null;
  })) > 0;          

  $isPengajuanKeu = count($uraianRealisasis->filter(function ($f) {
      return $f['qty_keu'] !== null;
  })) > 0;          

  $tahapanPengajuan = [$isPengajuanUnit, $isPengajuanTim, $isPengajuanKeu]; // 1 = sudah dilalui, 0 = belum dilalui

  $colSuffix = ['', '_tim', '_keu'];

  $currentApprovalIndex = 0;

  for ($i = 2; $i >= 0; $i--) {
    if ($tahapanPengajuan[$i] == 1) {
      $currentApprovalIndex = $i; // 2 = keuangan, 1 = tim pengadaan, 0 = unit
      break;
    }
  }

  //hitung anggaran yang sedang digunakan, digunakan di pengajuan lain dan sisa anggaran
  foreach($anggaranBelanjas as $anggaran) {
    $usedOnOthers = 0; 
    $usedNow = 0; 

    if ($anggaran->status_pengajuan == 'selesai') { //jika status anggaran belanja (RKA) selesai, berarti sudah digunakan semua
      $usedOnOthers = $anggaran->nilai_acc;
    } 
    elseif ($anggaran->status_pengajuan != 'ditolak') {
      $tahapanPengajuanLain = [];
      
      foreach($anggaran->sumberAnggaranRealisasi as $sumberAnggaran) {
        $uraian = $sumberAnggaran->uraianRealisasi;

        if ($uraian !== null) {
          if (!in_array($uraian->id, $uraianIds)) {
            //cari progress "pengajuan lain" dari RKA ini
            $index = 0;
            if (isset($tahapanPengajuanLain[$uraian->realisasi_id]) && $tahapanPengajuanLain[$uraian->realisasi_id] != '') {
              $index = $tahapanPengajuanLain[$uraian->realisasi_id];
            } else {
              $index = getTahapanPengajuan($uraian->realisasi_id); // f in Realization Helper
              $tahapanPengajuanLain[$uraian->realisas_id] = $index;
            }

            $qty = 'qty'.$colSuffix[$index];
            $hargaSatuan = 'harga_satuan'.$colSuffix[$index];

            $usedOnOthers += $uraian->$qty * $uraian->$hargaSatuan;
          } else {
            $qty = 'qty'.$colSuffix[$currentApprovalIndex];
            $hargaSatuan = 'harga_satuan'.$colSuffix[$currentApprovalIndex];
            $usedNow += $uraian->$qty * $uraian->$hargaSatuan;
          }
        }
      }
    }
    
    $anggaran->usedOnOthers = $usedOnOthers;
    $anggaran->usedNow = $usedNow;
    $anggaran->sisaAnggaran = max(0, $anggaran->nilai_acc - $usedOnOthers - $usedNow);
  }

  //data RKA diajukan
  foreach($anggaranBelanjas as $anggaran) {
    $usedOnOthers = 0; 
    $usedNow = 0; 

    if ($anggaran->status_pengajuan == 'selesai') { //jika status anggaran belanja (RKA) selesai, berarti sudah digunakan semua
      $usedOnOthers = $anggaran->nilai_acc;
    } 
    elseif ($anggaran->status_pengajuan != 'ditolak') {
      $tahapanPengajuanLain = [];
      
      foreach($anggaran->rkaDiajukan as $rkaDiajukan) {
        $uraian = $rkaDiajukan->uraianRealisasi;

        if ($uraian !== null) {
          if (!in_array($uraian->id, $uraianIds)) {
            //cari progress "pengajuan lain" dari RKA ini
            $index = 0;
            if (isset($tahapanPengajuanLain[$uraian->realisasi_id]) && $tahapanPengajuanLain[$uraian->realisasi_id] != '') {
              $index = $tahapanPengajuanLain[$uraian->realisasi_id];
            } else {
              $index = getTahapanPengajuan($uraian->realisasi_id); // f in Realization Helper
              $tahapanPengajuanLain[$uraian->realisas_id] = $index;
            }

            $qty = 'qty'.$colSuffix[$index];
            $hargaSatuan = 'harga_satuan'.$colSuffix[$index];

            $usedOnOthers += $uraian->$qty * $uraian->$hargaSatuan;
          } else {
            $qty = 'qty'.$colSuffix[$currentApprovalIndex];
            $hargaSatuan = 'harga_satuan'.$colSuffix[$currentApprovalIndex];
            $usedNow += $uraian->$qty * $uraian->$hargaSatuan;
          }
        }
      }
    }
    
    $anggaran->realisasiId = $realisasiId;
    $anggaran->usedOnOthers = $usedOnOthers;
    $anggaran->usedNow = $usedNow;
    $anggaran->sisaAnggaran = max(0, $anggaran->nilai_acc - $usedOnOthers - $usedNow);
  }
  
  return $anggaranBelanjas;
}

