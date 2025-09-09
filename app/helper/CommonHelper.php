<?php
use App\Models\Unit;


function getUnitBawahan($parentUnitIds, $storedIds) {
  $childUnits = Unit::where(function ($query) use ($parentUnitIds) {
      $query->whereIn('parent_unit', $parentUnitIds)
            ->orWhereIn('parent_koordinasi', $parentUnitIds);
    })
    ->get();

  
  if (count($childUnits) > 0) {
    foreach($childUnits as $childUnit) {
      $storedIds[] = $childUnit->id;
      $storedIds = getUnitBawahan([$childUnit->unit_id], $storedIds);
    }
  }

  return $storedIds;
}


function getChildUnitId($pegawaiId) {
  $parentUnitIds = Unit::where('pegawai_id', $pegawaiId)
    ->pluck('unit_id');
    
  $childUnitIds = getUnitBawahan($parentUnitIds, array());

  $selfUnits = Unit::where('pegawai_id', $pegawaiId)
    ->get();
  
  if (count($selfUnits) > 0) {
    foreach($selfUnits as $unit) {
      $childUnitIds[] = $unit->id; 
    }
  }

  return $childUnitIds;
}

function getUnitDipimpin() {
  $pegawaiId = session('user_data.id'); 

  $units = Unit::where('pegawai_id', $pegawaiId)
    ->pluck('id')->toArray();
  
  return $units;
}

function getAccessibleUnitIds()
{
    $pegawaiId = session('user_data.id');

    // ambil unit bawahan dari unit yang dipegang pegawai
    $parentUnitIds = Unit::where('pegawai_id', $pegawaiId)->pluck('unit_id'); 
    $childUnitIds  = getUnitBawahan($parentUnitIds, []);

    // tambahkan unit milik pegawai itu sendiri
    $selfUnitIds = Unit::where('pegawai_id', $pegawaiId)->pluck('id')->toArray(); 

    return array_merge($childUnitIds, $selfUnitIds);
}
