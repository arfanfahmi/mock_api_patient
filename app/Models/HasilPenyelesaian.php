<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HasilPenyelesaian extends Model
{
    use HasFactory;

    protected $table = 'hasil_penyelesaians';

    protected $guarded = [];

    protected $casts = [
        'tanggal_penyelesaian' => 'datetime',
    ];

    const STATUS_PENYELESAIAN = [
        'selesai' => 'Selesai',
        'belum_selesai' => 'Belum Selesai',
        'lainnya' => 'Lainnya'
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($hasil) {
            if (!$hasil->uuid) {
                $hasil->uuid = strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function komplain(): BelongsTo
    {
        return $this->belongsTo(Komplain::class, 'komplain_id');
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

    public function scopeByStatus($query, $status)
    {
        return $query->where('status_penyelesaian', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_penyelesaian', [$startDate, $endDate]);
    }

    // Accessors
    public function getStatusPenyelesaianLabelAttribute()
    {
        $label = self::STATUS_PENYELESAIAN[$this->status_penyelesaian] ?? $this->status_penyelesaian;
        
        if ($this->status_penyelesaian === 'lainnya' && $this->status_lainnya_detail) {
            $label = $this->status_lainnya_detail;
        }
        
        return $label;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status_penyelesaian) {
            'selesai' => 'badge-success',
            'belum_selesai' => 'badge-warning',
            'lainnya' => 'badge-info',
            default => 'badge-secondary'
        };
    }

    public function getFormattedTanggalAttribute()
    {
        return $this->tanggal_penyelesaian->format('d/m/Y H:i');
    }

    public function getHumasInfoAttribute()
    {
        return $this->nama_humas . ' (' . $this->kode_pegawai_humas . ')';
    }
}