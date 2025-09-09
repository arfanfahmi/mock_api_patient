<?php
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Tahun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/*
* Mencari unit bawahan misalnya mencari unit yang dibawah manager XYZ sampe level terendah
* Unit harus memiliki akses untuk input RKA
*/

function getUnitLevel($unitId) {
  $levelUnit = Unit::where('id', $unitId)->first()->level_unit;

  if ($levelUnit == 1 || $levelUnit == 2) {
    return 'direksi';
  } else if ($levelUnit == 3) {
    return 'manager';
  } else {
    return 'unit';
  }
}

function getAllUnitLevel($pegawaiId = null) {
  if ($pegawaiId === null) {
    $pegawaiId = session('user_data.id');
  }

  $units = Unit::where('pegawai_id', $pegawaiId)->get();

  $unitLevels = [];
  foreach($units as $unit) {
    $unitLevel = getUnitLevel($unit->id);
    if(!in_array($unitLevel, $unitLevels)) {
      $unitLevels[] = $unitLevel;
    }
  }

  return $unitLevels;
}

function getAllUnitId() {
  $userId = session('user_data.id');
  $unitIds = Unit::where('pegawai_id', $userId)->get()->pluck('id');
  
  return $unitIds;
}

function hasApprovalRight() { 
  return Unit::where('pegawai_id', session('user_data.id')) //pegawai_id = ID pimpinan unit tsb.
    ->where('aktif', 1)
    ->whereIn('level_unit', [1,2,3]) //direktur, wadir, manager
    ->exists();  
}

function isDireksi() {
  return Unit::where('pegawai_id', session('user_data.id')) //pegawai_id = ID pimpinan unit tsb.
    ->where('aktif', 1)
    ->whereIn('level_unit', [1,2]) //direktur, wadir
    ->exists();  
}

function isDirektur() {
  return Unit::where('pegawai_id', session('user_data.id')) //pegawai_id = ID pimpinan unit tsb.
    ->where('aktif', 1)
    ->whereIn('level_unit', [1]) //direktur, wadir
    ->exists();  
}

function isWadirUmum() {
  return Unit::where('pegawai_id', session('user_data.id'))
    ->where('is_wadir_umum', 1)
    ->exists();  
}

function isAllowInputRka() {
  return Unit::where('pegawai_id', session('user_data.id')) //pegawai_id = ID pimpinan unit tsb.
    ->where('aktif', 1)
    ->where('is_rka', 1)
    ->exists();  
}

function isRka() {
  $userId = session('user_data.id');
  $units = Unit::where('pegawai_id', $userId)->get();
  $isRka = 0;

  foreach($units as $unit) {
    if ($unit->is_rka == 1) {
      $isRka = 1;
      break;
    }
  }

  return $isRka;
}

