<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Implementasi extends Model
{
    use HasFactory;

    protected $table = 'implementasis';

    protected $guarded = [];

    protected $casts = [
        'tanggal_implementasi' => 'datetime',
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($implementasi) {
            if (!$implementasi->uuid) {
                $implementasi->uuid = strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function komplain(): BelongsTo
    {
        return $this->belongsTo(Komplain::class, 'komplain_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function inputBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    // Scopes
    public function scopeByKomplain($query, $komplainId)
    {
        return $query->where('komplain_id', $komplainId);
    }

    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_implementasi', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedTanggalAttribute()
    {
        return $this->tanggal_implementasi->format('d/m/Y H:i');
    }

    public function getCreatorInfoAttribute()
    {
        return $this->nama_humas . ' (' . $this->kode_pegawai_humas . ')';
    }
}