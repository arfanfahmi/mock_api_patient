<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RtlHumas extends Model
{
    use HasFactory;

    protected $table = 'rtl_humas';

    protected $guarded = [];

    protected $casts = [
        'tanggal_rencana' => 'datetime',
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rtlHumas) {
            if (!$rtlHumas->uuid) {
                $rtlHumas->uuid = strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function komplain(): BelongsTo
    {
        return $this->belongsTo(Komplain::class, 'komplain_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    // Scopes
    public function scopeByKomplain($query, $komplainId)
    {
        return $query->where('komplain_id', $komplainId);
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

    public function getHumasInfoAttribute()
    {
        return $this->nama_humas . ' (' . $this->kode_pegawai_humas . ')';
    }
}