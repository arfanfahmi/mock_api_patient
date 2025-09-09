<?php
use Carbon\Carbon;
use App\Models\Unit;
use App\Models\UraianRealisasi;
use App\Models\Realisasi;
use App\Models\Disposisi;
use App\Models\KategoriAnggaran;
use App\Models\RelasiDisposisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/*
 * REALISASI (RETURN SINGLE ROW)
 */
function getRealisasi($uuid) {
  $pengajuanRealisasi = Realisasi::from('realisasis as r')
    ->leftJoin('units as u', 'u.id', '=', 'r.unit_id')
    ->leftJoin('prokers as p', 'p.id', '=', 'r.proker_id')
    ->leftJoin('kategori_anggarans as ka', 'ka.id', '=', 'kategori_anggaran_id')
    ->leftJoin('disposisis as d', 'd.realisasi_id', '=', 'r.id')
    ->leftJoin('relasi_disposisis as rd', 'd.relasi_disposisi_id', '=', 'rd.id')
    ->leftJoin(DB::raw('
        (SELECT realisasi_id, 
            SUM(qty*harga_satuan) as total_pengajuan,
            SUM(qty_tim*harga_satuan_tim) as total_pengajuan_tim,
            SUM(qty_keu*harga_satuan_keu) as total_pengajuan_keu
        FROM uraian_realisasis as ur
        GROUP BY realisasi_id
        ) as data_nilai_pengajuan'
      ), 'data_nilai_pengajuan.realisasi_id', '=', 'r.id'
    )
    ->leftJoin(DB::raw('
        (SELECT realisasi_id, SUM(qty_keu * harga_satuan_keu) as total_acc from uraian_realisasis as ur
          GROUP BY realisasi_id
        ) as data_nilai_acc'
      ), 'data_nilai_acc.realisasi_id', '=', 'r.id'
    )
    ->leftJoin(DB::raw(
        '(SELECT realisasi_id, max(d.id), status, pengirim, penerima, jenis_relasi FROM disposisis as d
          LEFT JOIN relasi_disposisis as rd ON d.relasi_disposisi_id = rd.id
          GROUP BY realisasi_id
          ORDER BY d.id DESC
        ) as disposisi'
      ), 'disposisi.realisasi_id', '=', 'r.id'        
    )
    ->where('r.uuid', $uuid)
    ->orderBy('r.created_at', 'DESC')
    ->groupBy('r.id') // Grouping by the primary key of realisasis table
    ->select('r.*', 'u.id as unit_id', 'u.nama_unit', 'r.proker_id as proker_id', 'p.nama_proker', 
      'ka.id as kategori_anggaran_id', 'ka.nama_kategori', 'ka.lingkup as lingkup_kategori_anggaran',
      'data_nilai_pengajuan.*', 'data_nilai_acc.total_acc',
      'disposisi.status as status_disposisi', 'disposisi.pengirim', 'disposisi.penerima', 'disposisi.jenis_relasi as jenis_relasi_disposisi'
      )
    ->first();
  
  return $pengajuanRealisasi;
}

/*
 * URAIAN REALISASI
 */

function getUraianRealisasi($realisasiId) {
  $uraianRealisasis = UraianRealisasi::from('uraian_realisasis as ur')
    ->with('rkaDiajukan', 'sumberAnggaranRealisasi')
    ->leftJoin('sumber_anggaran_realisasis as sar', 'ur.id', '=', 'sar.uraian_realisasi_id')
    ->leftJoin('anggaran_belanjas as ab', 'ab.id', '=', 'sar.anggaran_belanja_id')
    ->join('realisasis as r', 'r.id', 'ur.realisasi_id')
    ->where('ur.realisasi_id', $realisasiId)
    ->select('ur.*', 'ab.id as anggaran_belanja_id', 'ab.id as anggaran_belanja_id')
    ->orderBy('ur.id')
    ->get();
    
  return $uraianRealisasis;
}

/*
 * DISPOSISI
 */
function getDisposisi($realisasiId) {
  $disposisi = Disposisi::from('disposisis as d')
        ->with('revisi', 'revisi.uraianRevisi', 'revisi.uraianRevisi.sumberAnggaranRevisi', 'revisi.uraianRevisi.uraianRealisasi.rkaDiajukan')
        ->join('relasi_disposisis as rd', 'rd.id', '=', 'd.relasi_disposisi_id')
        ->where('d.realisasi_id', $realisasiId)
        ->orderBy('d.created_at')
        ->select('d.*', 'rd.pengirim', 'rd.penerima', 'rd.jenis_relasi')
        ->get();

  return $disposisi;
}

/* Create Next Disposition (Approval) */
function createNextDisposition($lastDisposition, $nextDispositionNote, $nilaiPengajuan) {
  $lastReceiver = $lastDisposition->penerima;
  $lastSender = $lastDisposition->pengirim;

  $realisasi = Realisasi::where('id', $lastDisposition->realisasi_id)->first();
  
  $kategoriAnggaran = KategoriAnggaran::where('id', $realisasi->kategori_anggaran_id)->first();
  $pelaksana = $kategoriAnggaran->pelaksana;

  $requireProcurementTeamApproval = requireProcurementTeamApproval($realisasi->kategori_anggaran_id);
  
  $statusRealisasi = 'diajukan';
  
  if ($lastReceiver == 'manager') {
    $nextReceiver = $requireProcurementTeamApproval ? 'tim pengadaan' : 'keuangan';
    $nextRelation = 'approval';
  } else if ($lastReceiver == 'tim pengadaan') {            
    $nextReceiver = 'keuangan';
    $nextRelation = 'approval';
  } else if ($lastReceiver == 'direksi') {
    $nextReceiver = 'keuangan';
    $nextRelation = 'pencairan';
    $statusRealisasi = 'disetujui';
  } else if ($lastReceiver == 'keuangan') {
    if ($lastSender != 'direksi') {
      if (in_array(checkFinalApprover($nilaiPengajuan), ['wadir_umum', 'direktur'])) {
        $nextReceiver = 'direksi';
        $nextRelation = 'approval';
      } else if (checkFinalApprover($nilaiPengajuan) == 'keuangan') {
        if ($lastSender == 'keuangan') {
          $nextReceiver = ($pelaksana == 'keuangan') ? null : $pelaksana;
          $nextRelation = ($pelaksana == 'keuangan') ? 'penyelesaian' : 'pelaksanaan';
          $statusRealisasi = ($pelaksana == 'keuangan') ? 'selesai' : null;
        } else {
          $nextReceiver = 'keuangan';
          $statusRealisasi = 'disetujui';
          $nextRelation = 'pencairan';
        }
      }
    } else {
      $nextReceiver = ($pelaksana == 'keuangan') ? null : $pelaksana;
      $nextRelation = ($pelaksana == 'keuangan') ? 'penyelesaian' : 'pelaksanaan';
      $statusRealisasi = ($pelaksana == 'keuangan') ? 'selesai' : null;
    }
  }
  
  //update disposisi lama
  if (in_array($lastDisposition->status, ['diajukan', 'direvisi'])) {
    $lastDisposition->update(['status' => 'disetujui']);
  } else if ($lastDisposition->status == 'dicairkan') {          
    $lastDisposition->update(['status' => 'sukses_dicairkan']);
  } else if ($lastDisposition->status == 'dilaksanakan') {          
    $lastDisposition->update(['status' => 'sukses_dilaksanakan']);
  }
  
  //Buat disposisi baru
  $relasiDisposisiId = RelasiDisposisi::where([
    'pengirim' => $lastReceiver,
    'penerima' => $nextReceiver,
    'jenis_relasi' => $nextRelation
  ])
  ->first()
  ->id;

  $statusDisposisiSelanjutnya = 'diajukan'; //default value

  switch ($nextRelation) {
    case 'revisi':
      $statusDisposisiSelanjutnya = 'direvisi'; break;
    case 'pencairan':
      $statusDisposisiSelanjutnya = 'dicairkan'; break;
    case 'pelaksanaan':
      $statusDisposisiSelanjutnya = 'dilaksanakan'; break;
    case 'penyelesaian':
      $statusDisposisiSelanjutnya = 'selesai'; break;
    default:
      $statusDisposisiSelanjutnya = 'diajukan'; break;
  }

  $nextDisposition = Disposisi::create([
    'realisasi_id' => $realisasi->id,
    'relasi_disposisi_id' => $relasiDisposisiId,
    'status' => $statusDisposisiSelanjutnya,
    'catatan' => $nextDispositionNote
  ]);

  //update realisasi
  if ($statusRealisasi == 'disetujui') {
    $realisasi->update(['status' => 'disetujui']);
  } else if ($statusRealisasi == 'selesai') {
    $realisasi->update(['status' => 'selesai']);
  } else {          
    if ($statusDisposisiSelanjutnya == 'dilaksanakan') {          
      $realisasi->update(['status' => 'dilaksanakan']);
    }
  }

  return $nextDisposition;
}

/*
 * get Tahapan Pengajuan (apakah di 0=unit, 1=tim pengadaan, 2=keuangan)
 */
function getTahapanPengajuan($realisasiId) {
  $uraianRealisasis = getUraianRealisasi($realisasiId);

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
  
  return $currentApprovalIndex;
}

function requireManagerApproval($unitId) {
  $unit = DB::table('units as u1')
    ->leftJoin('units as u2', 'u1.parent_unit', '=', 'u2.unit_id')
    ->select('u1.*',  'u2.nama_unit as nama_parent_unit', 'u2.id as parent_id', 'u2.level_unit as parent_level_unit')
    ->where('u1.id', $unitId)
    ->orderBy('level_unit', 'asc')
    ->orderBy('u1.nama_unit', 'asc')
    ->first();

  if ($unit->parent_level_unit == 3) {
    return true;
  } else if ($unit->parent_level_unit > 3) {
    requireManagerApproval($unit->parent_id);
  } else {
    return false;
  }
}

function requireProcurementTeamApproval($kategoriAnggaranId) {
  return KategoriAnggaran::where('id', $kategoriAnggaranId)
    ->first()
    ->approval_tim_pengadaan;
}

function checkFinalApprover($nilaiPengajuan) {
  // return KategoriAnggaran::where('id', $kategoriAnggaranId)
  //   ->first()
  //   ->approval_direksi;
  if ($nilaiPengajuan < 100000000) {
    return 'keuangan';
  } else if ($nilaiPengajuan >= 100000000 and $nilaiPengajuan < 500000000) {
    return 'wadir_umum';
  } else if ($nilaiPengajuan >= 500000000) {
    return 'direktur';
  }
}

function canApproveRealization($realisasiId, $kategoriAnggaranId = null) {
  if ($kategoriAnggaranId !== null) { //admin
    $kategoriAnggaran = KategoriAnggaran::where('id', $kategoriAnggaranId)->first();

    //superadmin always able to approve
    if (Auth::guard('admin')->user()) { 
      if(Gate::allows('isSuperAdmin')) {
        return true;
      } else if (Gate::allows('isKeuangan')) {
        $isExist = Disposisi::where('realisasi_id', $realisasiId)
          ->join('relasi_disposisis as rd', 'rd.id', '=', 'disposisis.relasi_disposisi_id')
          ->where('penerima', 'keuangan')
          ->whereIn('disposisis.status', ['diajukan', 'direvisi'])
          ->exists();

        return $isExist;
      }
      else if (Auth::guard('admin')->user()->role_id == $kategoriAnggaran->role_id) {
        $isExist = Disposisi::where('realisasi_id', $realisasiId)
          ->join('relasi_disposisis as rd', 'rd.id', '=', 'disposisis.relasi_disposisi_id')
          ->where('penerima', 'tim pengadaan')
          ->whereIn('disposisis.status', ['diajukan', 'direvisi'])
          ->exists();

        return $isExist;
      } 

      return false;
    }
  }

  //user
  $unitPegawais = getAllUnitLevel(session('user_data.id'));
  
  return 
    Disposisi::where('realisasi_id', $realisasiId)
      ->join('relasi_disposisis as rd', 'rd.id', '=', 'disposisis.relasi_disposisi_id')
      ->whereIn('penerima', $unitPegawais)
      ->whereIn('disposisis.status', ['diajukan', 'direvisi'])
      ->exists();
}

  
function canSetBudget($realisasiId, $kategoriAnggaranId = null) {
  if ($kategoriAnggaranId !== null) { //admin

    //superadmin always able to approve
    if (Auth::guard('admin')->user()) { 
      if(Gate::allows('isSuperAdmin')) {
        return true;
      } else if (Gate::allows('isKeuangan')) {
        $isExist = Disposisi::where('realisasi_id', $realisasiId)
          ->join('relasi_disposisis as rd', 'rd.id', '=', 'disposisis.relasi_disposisi_id')
          ->where('penerima', 'keuangan')
          ->whereIn('disposisis.status', ['dicairkan'])
          ->exists();

        return $isExist;
      }
    }
  }

  return false;
}

function canCompleteRealization($realisasiId) {
  $realisasi = Realisasi::where('id', $realisasiId)->first();
  $kategoriAnggaran = KategoriAnggaran::where('id', $realisasi->kategori_anggaran_id)->first();
  $lastDisposition = Disposisi::where('realisasi_id', $realisasi->id)
    ->join('relasi_disposisis as rd', 'rd.id', '=', 'disposisis.relasi_disposisi_id')
    ->orderByDesc('disposisis.id')
    ->first();

  if (Auth::guard('admin')->user()) { 
    if(Gate::allows('isSuperAdmin')) {
      return true;
    } else {
      if ($kategoriAnggaran->role_id == Auth::guard('admin')->user()->role_id) {          
        return ($lastDisposition->status == 'dilaksanakan' ? 1 : 0);
      }
    }
  }

  //cek mungkinkah complete realization dari sisi user (unit)
  $pegawaiId = session('user_data.id');

  $unitPegawaiIds = Unit::where('pegawai_id', $pegawaiId)->pluck('id')->toArray();

  if ($lastDisposition->status == 'dilaksanakan' && in_array($realisasi->unit_id, $unitPegawaiIds)) {
    return 1;
  }

  return false;
}

function getRevisionReceiver($realisasiId) {
  $realization = Realisasi::where('id', $realisasiId)->first();

  $lastDisposition = Disposisi::leftJoin('relasi_disposisis', 'relasi_disposisis.id', '=', 'disposisis.relasi_disposisi_id')
    ->where('disposisis.realisasi_id', $realisasiId)
    ->orderByDesc('disposisis.id')
    ->first();

  $receivers = [];
  
  if (!in_array($realization->status, ['diajukan']) || $lastDisposition->status != 'diajukan') {
    return null;
  }

  if ($lastDisposition->penerima == 'manager') {
    $receivers[] = 'unit';
  } else if ($lastDisposition->penerima == 'direksi') {
    $receivers[] = 'keuangan';
  } else if ($lastDisposition->penerima == 'tim pengadaan')  {
    $receivers[] = 'unit';
  } else if ($lastDisposition->penerima == 'keuangan') {
    if (requireProcurementTeamApproval($realization->kategori_anggaran_id)) {
      $receivers[] = 'tim pengadaan';
    } else {
      $receivers[] = 'unit';
    }

    // $index = getTahapanPengajuan($realisasiId);
    // if ($index == 0) {
    //   $receivers[] = 'unit';
    // }
  }
  return $receivers;
}