<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApprovalManajer extends Model
{
    use HasFactory;

    protected $table = 'approval_manajers';

    protected $guarded = [];

    protected $casts = [
        'tanggal_approval' => 'datetime',
    ];

    const STATUS_APPROVAL = [
        'menunggu_approval' => 'Menunggu Approval',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak'
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($approval) {
            if (!$approval->uuid) {
                $approval->uuid = strtoupper(Str::random(8));
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

    public function manajerPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'manajer_pegawai_id');
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

    public function scopeByManajer($query, $manajerId)
    {
        return $query->where('manajer_pegawai_id', $manajerId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status_approval', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status_approval', 'menunggu_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status_approval', 'disetujui');
    }

    public function scopeRejected($query)
    {
        return $query->where('status_approval', 'ditolak');
    }

    // Accessors
    public function getStatusApprovalLabelAttribute()
    {
        return self::STATUS_APPROVAL[$this->status_approval] ?? $this->status_approval;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status_approval) {
            'disetujui' => 'badge-success',
            'ditolak' => 'badge-danger',
            'menunggu_approval' => 'badge-warning',
            default => 'badge-secondary'
        };
    }

    public function getFormattedTanggalAttribute()
    {
        return $this->tanggal_approval?->format('d/m/Y H:i');
    }

    public function getManajerInfoAttribute()
    {
        return $this->manajer_nama . ' (' . $this->manajer_kode_pegawai . ')';
    }

    // Methods
    public function approve($manajerId, $catatan = null)
    {
        $manajer = Pegawai::find($manajerId);
        
        $this->update([
            'status_approval' => 'disetujui',
            'manajer_pegawai_id' => $manajerId,
            'manajer_nama' => $manajer->nama,
            'manajer_kode_pegawai' => $manajer->kode_pegawai,
            'catatan_manajer' => $catatan,
            'tanggal_approval' => now(),
        ]);

        // Update status unit di komplain_units
        $this->komplain->units()->updateExistingPivot($this->unit_id, [
            'status_unit' => 'disetujui_manajer',
            'manager_response_at' => now(),
        ]);

        $this->logAudit('disetujui');
        
        // Check if all approvals completed
        $this->komplain->checkAllApprovalsCompleted();
        
        return $this;
    }

    public function reject($manajerId, $catatan = null)
    {
        $manajer = Pegawai::find($manajerId);
        
        $this->update([
            'status_approval' => 'ditolak',
            'manajer_pegawai_id' => $manajerId,
            'manajer_nama' => $manajer->nama,
            'manajer_kode_pegawai' => $manajer->kode_pegawai,
            'catatan_manajer' => $catatan,
            'tanggal_approval' => now(),
        ]);

        // Update status unit di komplain_units
        $this->komplain->units()->updateExistingPivot($this->unit_id, [
            'status_unit' => 'ditolak_manajer',
            'manager_response_at' => now(),
        ]);

        $this->logAudit('ditolak');
        
        return $this;
    }

    private function logAudit($action)
    {
        KomplainAuditLog::create([
            'komplain_id' => $this->komplain_id,
            'target_unit_id' => $this->unit_id,
            'actor_pegawai_id' => $this->manajer_pegawai_id,
            'actor_unit_id' => $this->unit_id,
            'action_type' => 'approval_manajer',
            'authorization_type' => 'own_unit',
            'action_description' => "Approval manajer {$action} untuk unit {$this->unit->nama_unit}",
            'metadata' => [
                'approval_manajer_id' => $this->id,
                'status_approval' => $this->status_approval,
                'tanggal_approval' => $this->tanggal_approval,
            ]
        ]);
    }
}