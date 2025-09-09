<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RtlUnit extends Model
{
    use HasFactory;

    protected $table = 'rtl_units';

    protected $fillable = [
        'uuid',
        'komplain_id',
        'unit_id',
        'tanggal_rencana',
        'rtl_unit',
        'pic_pegawai_id',
        'pic_nama',
        'pic_kode_pegawai',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_rencana' => 'datetime',
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rtlUnit) {
            if (!$rtlUnit->uuid) {
                $rtlUnit->uuid = strtoupper(Str::random(8));
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

    public function picPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pic_pegawai_id');
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
        return $query->whereBetween('tanggal_rencana', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedTanggalAttribute()
    {
        return $this->tanggal_rencana->format('d/m/Y H:i');
    }

    public function getPicInfoAttribute()
    {
        // return $this->pic_nama . ' (' . $this->pic_kode_pegawai . ')';
        return $this->pic_nama;
    }

    // Methods
    public function logAudit()
    {
        KomplainAuditLog::create([
            'komplain_id' => $this->komplain_id,
            'target_unit_id' => $this->unit_id,
            'actor_pegawai_id' => $this->pic_pegawai_id,
            'actor_unit_id' => $this->unit_id,
            'action_type' => 'rtl_unit',
            'authorization_type' => 'own_unit',
            'action_description' => "Rencana tindak lanjut dibuat untuk unit {$this->unit->nama_unit}",
            'metadata' => [
                'rtl_unit_id' => $this->id,
                'tanggal_rencana' => $this->tanggal_rencana,
            ]
        ]);
    }
}