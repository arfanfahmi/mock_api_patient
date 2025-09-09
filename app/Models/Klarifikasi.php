<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Klarifikasi extends Model
{
    use HasFactory;

    protected $table = 'klarifikasis';

    protected $guarded = [];

    protected $casts = [
        'tanggal_klarifikasi' => 'datetime',
    ];

    const FILL_AUTHORIZATION = [
        'own_unit' => 'Unit Sendiri',
        'parent_unit' => 'Unit Parent',
        'delegated' => 'Didelegasikan'
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($klarifikasi) {
            if (!$klarifikasi->uuid) {
                $klarifikasi->uuid = strtoupper(Str::random(8));
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

    public function filledByPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'filled_by_pegawai_id');
    }

    public function filledByUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'filled_by_unit_id');
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

    public function scopeByAuthorization($query, $authorization)
    {
        return $query->where('fill_authorization', $authorization);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_klarifikasi', [$startDate, $endDate]);
    }

    // Accessors
    public function getFillAuthorizationLabelAttribute()
    {
        return self::FILL_AUTHORIZATION[$this->fill_authorization] ?? $this->fill_authorization;
    }

    public function getFormattedTanggalAttribute()
    {
        return $this->tanggal_klarifikasi->format('d/m/Y H:i');
    }

    public function getFillerInfoAttribute()
    {
        return $this->filledByPegawai->nama;
    }

    // Methods
    public function logAudit()
    {
        KomplainAuditLog::create([
            'komplain_id' => $this->komplain_id,
            'target_unit_id' => $this->unit_id,
            'actor_pegawai_id' => $this->filled_by_pegawai_id,
            'actor_unit_id' => $this->filled_by_unit_id,
            'action_type' => 'klarifikasi',
            'authorization_type' => $this->fill_authorization,
            'action_description' => "Klarifikasi dibuat untuk unit {$this->unit->nama_unit}",
            'metadata' => [
                'klarifikasi_id' => $this->id,
                'tanggal_klarifikasi' => $this->tanggal_klarifikasi,
            ]
        ]);
    }
}