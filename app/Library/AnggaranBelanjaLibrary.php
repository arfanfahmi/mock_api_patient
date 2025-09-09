<?php

namespace App\Library;

use App\Models\AnggaranBelanja;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class AnggaranBelanjaLibrary {
  function getAnggaranDigunakan() {
      $anggaranBelanjas = AnggaranBelanja::with(['sumberAnggaranRealisasi.uraianRealisasi' => function ($query) {
        $query->select('uraian_realisasis.*')
            ->leftJoin('sumber_anggaran_realisasis as sar', 'sar.uraian_realisasi_id', '=', 'uraian_realisasis.id')
            ->join('anggaran_belanjas as ab', 'ab.id', '=', 'sar.anggaran_belanja_id')
            ->whereNotIn('uraian_realisasis.approval', ['ditolak', 'ditunda']);
      }])
      ->where('proker_id', $prokerId)
      ->where('kategori_anggaran_id', $kategoriAnggaranId)
      ->whereIn('status_pengajuan', ['disetujui', 'dilaksanakan'])
      ->when ($anggaranBelanjaId !== null, function($query) use ($anggaranBelanjaId) {
        $query->where('anggaran_belanjas.id', $anggaranBelanjaId);
      })
      ->orderBy('uraian')
      ->first();

    foreach($anggaranBelanjas as $anggaran) {
      $digunakan = 0;
      foreach($anggaran->sumberAnggaranRealisasi as $sumberAnggaran) {
        $uraian = $sumberAnggaran->uraianRealisasi;
          if ($uraian->approval == 'diajukan') {
              $digunakan += $uraian->qty * $uraian->harga_satuan;
          } elseif ($uraian->approval == 'disetujui') {
              $digunakan += $uraian->nilai_realisasi_acc;
          }
      }
      
      $anggaran->sisaAnggaran = max(0, $anggaran->nilai_acc - $digunakan);
    }

  }
  
}
