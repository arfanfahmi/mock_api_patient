<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tahun extends Model
{
    use HasFactory;

    protected $table = 'tahuns';

    protected $fillable = [
        'kode',
        'nama_tahun',
        'keterangan',
    ];

    protected $casts = [
        'nama_tahun' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->whereYear('nama_tahun', '>=', now()->year - 5);
    }

    public function getFormattedTahunAttribute()
    {
        return $this->nama_tahun . ' (' . $this->kode . ')';
    }
}