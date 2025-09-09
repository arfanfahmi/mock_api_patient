<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisKomplain extends Model
{
    use HasFactory;

    protected $table = 'jenis_komplains';

    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
        'deskripsi',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function komplains(): HasMany
    {
        return $this->hasMany(Komplain::class, 'jenis_komplain_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKode($query, $kode)
    {
        return $query->where('kode_jenis', $kode);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->nama_jenis . ' (' . $this->kode_jenis . ')';
    }

    // Methods
    public function getComplainCount()
    {
        return $this->komplains()->count();
    }

    public function getActiveComplainCount()
    {
        return $this->komplains()->where('is_active', 1)->count();
    }
}