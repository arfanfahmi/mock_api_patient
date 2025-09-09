<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomplainAuditLog extends Model
{
    use HasFactory;

    protected $table = 'komplain_audit_logs';

    protected $fillable = [
        'komplain_id',
        'target_unit_id',
        'actor_pegawai_id',
        'actor_unit_id',
        'action_type',
        'authorization_type',
        'action_description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    const ACTION_TYPES = [
        'klarifikasi' => 'Klarifikasi',
        'rtl_unit' => 'Rencana Tindak Lanjut Unit',
        'approval_unit' => 'Approval Unit',
        'approval_manajer' => 'Approval Manajer',
        'delegasi' => 'Delegasi'
    ];

    const AUTHORIZATION_TYPES = [
        'own_unit' => 'Unit Sendiri',
        'parent_unit' => 'Unit Parent',
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

    public function actorPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'actor_pegawai_id');
    }

    public function actorUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'actor_unit_id');
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

    public function scopeByActorPegawai($query, $pegawaiId)
    {
        return $query->where('actor_pegawai_id', $pegawaiId);
    }

    public function scopeByActorUnit($query, $unitId)
    {
        return $query->where('actor_unit_id', $unitId);
    }

    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeByAuthorizationType($query, $authorizationType)
    {
        return $query->where('authorization_type', $authorizationType);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Accessors
    public function getActionTypeLabelAttribute()
    {
        return self::ACTION_TYPES[$this->action_type] ?? $this->action_type;
    }

    public function getAuthorizationTypeLabelAttribute()
    {
        return self::AUTHORIZATION_TYPES[$this->authorization_type] ?? $this->authorization_type;
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y H:i:s');
    }

    public function getActorInfoAttribute()
    {
        return $this->actorPegawai->nama . ' (' . $this->actorUnit->nama_unit . ')';
    }

    // Methods
    public static function logAction($komplainId, $targetUnitId, $actorPegawaiId, $actorUnitId, $actionType, $authorizationType, $description, $metadata = null)
    {
        return self::create([
            'komplain_id' => $komplainId,
            'target_unit_id' => $targetUnitId,
            'actor_pegawai_id' => $actorPegawaiId,
            'actor_unit_id' => $actorUnitId,
            'action_type' => $actionType,
            'authorization_type' => $authorizationType,
            'action_description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public function getMetadataValue($key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }
}