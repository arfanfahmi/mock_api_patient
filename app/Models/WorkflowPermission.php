<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowPermission extends Model
{
    use HasFactory;

    protected $table = 'workflow_permissions';

    protected $fillable = [
        'komplain_id',
        'target_unit_id',
        'authorized_unit_id',
        'authorized_pegawai_id',
        'can_klarifikasi',
        'can_rtl_unit',
        'can_approval',
        'authorization_reason',
    ];

    protected $casts = [
        'can_klarifikasi' => 'boolean',
        'can_rtl_unit' => 'boolean',
        'can_approval' => 'boolean',
    ];

    const AUTHORIZATION_REASONS = [
        'own_unit' => 'Unit Sendiri',
        'parent_unit' => 'Unit Parent (Atasan)',
        'koordinasi_unit' => 'Unit Koordinasi',
        'delegated' => 'Didelegasikan',
        'emergency' => 'Kondisi Darurat'
    ];

    // Relationships
    public function komplain(): BelongsTo
    {
        return $this->belongsTo(Komplain::class, 'komplain_id');
    }

    public function targetUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'target_unit_id');
    }

    public function authorizedUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'authorized_unit_id');
    }

    public function authorizedPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'authorized_pegawai_id');
    }

    // Scopes
    public function scopeByKomplain($query, $komplainId)
    {
        return $query->where('komplain_id', $komplainId);
    }

    public function scopeByTargetUnit($query, $unitId)
    {
        return $query->where('target_unit_id', $unitId);
    }

    public function scopeByAuthorizedUnit($query, $unitId)
    {
        return $query->where('authorized_unit_id', $unitId);
    }

    public function scopeByAuthorizedPegawai($query, $pegawaiId)
    {
        return $query->where('authorized_pegawai_id', $pegawaiId);
    }

    public function scopeCanKlarifikasi($query)
    {
        return $query->where('can_klarifikasi', true);
    }

    public function scopeCanRtlUnit($query)
    {
        return $query->where('can_rtl_unit', true);
    }

    public function scopeCanApproval($query)
    {
        return $query->where('can_approval', true);
    }

    // Accessors
    public function getAuthorizationReasonLabelAttribute()
    {
        return self::AUTHORIZATION_REASONS[$this->authorization_reason] ?? $this->authorization_reason;
    }

    public function getPermissionsListAttribute()
    {
        $permissions = [];
        if ($this->can_klarifikasi) $permissions[] = 'Klarifikasi';
        if ($this->can_rtl_unit) $permissions[] = 'RTL Unit';
        if ($this->can_approval) $permissions[] = 'Approval';
        
        return $permissions;
    }

    // Methods
    public function hasPermission($permissionType)
    {
        return match($permissionType) {
            'klarifikasi' => $this->can_klarifikasi,
            'rtl_unit' => $this->can_rtl_unit,
            'approval' => $this->can_approval,
            default => false
        };
    }

    public static function checkPermission($komplainId, $targetUnitId, $authorizedUnitId, $permissionType)
    {
        return self::where('komplain_id', $komplainId)
            ->where('target_unit_id', $targetUnitId)
            ->where('authorized_unit_id', $authorizedUnitId)
            ->where("can_{$permissionType}", true)
            ->exists();
    }

    public static function getUserPermissions($komplainId, $pegawaiId)
    {
        return self::where('komplain_id', $komplainId)
            ->where('authorized_pegawai_id', $pegawaiId)
            ->get();
    }
}