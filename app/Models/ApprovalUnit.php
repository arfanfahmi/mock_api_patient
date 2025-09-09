<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApprovalUnit extends Model
{
    use HasFactory;

    protected $table = 'approval_units';

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

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'parent_unit_id');
    }

    public function canApprove($user)
    {
        return $this->unit && $this->unit->pegawai_id === $user->pegawai_id;
    }

    // Relationships
    public function komplain(): BelongsTo
    {
        return $this->belongsTo(Komplain::class, 'komplain_id');
    }

    public function subunit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'subunit_id');
    }

    public function parentUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_unit_id');
    }

    public function approverPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'approver_pegawai_id');
    }

    // Scopes
    public function scopeByKomplain($query, $komplainId)
    {
        return $query->where('komplain_id', $komplainId);
    }

    public function scopeBySubunit($query, $subunitId)
    {
        return $query->where('subunit_id', $subunitId);
    }

    public function scopeByParentUnit($query, $parentUnitId)
    {
        return $query->where('parent_unit_id', $parentUnitId);
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

    public function getApproverInfoAttribute()
    {
        return $this->approver_nama . ' (' . $this->approver_kode_pegawai . ')';
    }

    // Methods
    public function approve($approverId, $catatan = null)
    {
        $approver = Pegawai::find($approverId);
        
        $this->update([
            'status_approval' => 'disetujui',
            'approver_pegawai_id' => $approverId,
            'approver_nama' => $approver->nama,
            'approver_kode_pegawai' => $approver->kode_pegawai,
            'catatan_approval' => $catatan,
            'tanggal_approval' => now(),
        ]);

        // Update status unit di komplain_units
        $this->komplain->units()->updateExistingPivot($this->subunit_id, [
            'status_unit' => 'disetujui_unit',
            'unit_response_at' => now(),
        ]);

        $this->logAudit('disetujui');
        
        return $this;
    }

    public function reject($approverId, $catatan = null)
    {
        $approver = Pegawai::find($approverId);
        
        $this->update([
            'status_approval' => 'ditolak',
            'approver_pegawai_id' => $approverId,
            'approver_nama' => $approver->nama,
            'approver_kode_pegawai' => $approver->kode_pegawai,
            'catatan_approval' => $catatan,
            'tanggal_approval' => now(),
        ]);

        // Update status unit di komplain_units
        $this->komplain->units()->updateExistingPivot($this->subunit_id, [
            'status_unit' => 'ditolak_unit',
            'unit_response_at' => now(),
        ]);

        $this->logAudit('ditolak');
        
        return $this;
    }

    private function logAudit($action)
    {
        KomplainAuditLog::create([
            'komplain_id' => $this->komplain_id,
            'target_unit_id' => $this->subunit_id,
            'actor_pegawai_id' => $this->approver_pegawai_id,
            'actor_unit_id' => $this->parent_unit_id,
            'action_type' => 'approval_unit',
            'authorization_type' => 'parent_unit',
            'action_description' => "Approval unit {$action} untuk subunit {$this->subunit->nama_unit}",
            'metadata' => [
                'approval_unit_id' => $this->id,
                'status_approval' => $this->status_approval,
                'tanggal_approval' => $this->tanggal_approval,
            ]
        ]);
    }
}