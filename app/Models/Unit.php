<?php

namespace App\Models;

use App\Models\Klarifikasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $guarded = [];

    protected $casts = [
        'shift' => 'boolean',
        'aktif' => 'boolean',
    ];

    // Relationships
    public function parentUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_unit', 'unit_id');
    }

    public function childUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_unit', 'unit_id');
    }

    public function koordinasiUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_koordinasi', 'unit_id');
    }

    public function pimpinan(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    public function komplains(): BelongsToMany
    {
        return $this->belongsToMany(Komplain::class, 'komplain_units')
                    ->withPivot([
                        'is_primary_target',
                        'parent_workflow_unit_id',
                        'workflow_level',
                        'workflow_order',
                        'authorized_units',
                        'authorized_pegawai_ids',
                        'status_unit',
                        'notified_at',
                        'klarifikasi_at',
                        'tindak_lanjut_at',
                        'submitted_to_unit_at',
                        'unit_response_at',
                        'submitted_to_manager_at',
                        'manager_response_at',
                        'completed_at',
                        'progress_percentage'
                    ])
                    ->withTimestamps();
    }

    public function klarifikasis(): HasMany
    {
        return $this->hasMany(Klarifikasi::class, 'unit_id');
    }

    public function rtlUnits(): HasMany
    {
        return $this->hasMany(RtlUnit::class, 'unit_id');
    }

    public function implementasis(): HasMany
    {
        return $this->hasMany(Implementasi::class, 'unit_id');
    }

    public function approvalUnits(): HasMany
    {
        return $this->hasMany(ApprovalUnit::class, 'subunit_id');
    }

    public function approvalUnitAsParent(): HasMany
    {
        return $this->hasMany(ApprovalUnit::class, 'parent_unit_id');
    }

    public function approvalManajers(): HasMany
    {
        return $this->hasMany(ApprovalManajer::class, 'unit_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'unit_id');
    }

    public function workflowPermissionsAsTarget(): HasMany
    {
        return $this->hasMany(WorkflowPermission::class, 'target_unit_id');
    }

    public function workflowPermissionsAsAuthorized(): HasMany
    {
        return $this->hasMany(WorkflowPermission::class, 'authorized_unit_id');
    }

    public function auditLogsAsTarget(): HasMany
    {
        return $this->hasMany(KomplainAuditLog::class, 'target_unit_id');
    }

    public function auditLogsAsActor(): HasMany
    {
        return $this->hasMany(KomplainAuditLog::class, 'actor_unit_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level_unit', $level);
    }

    public function scopeHasParent($query)
    {
        return $query->whereNotNull('parent_unit');
    }

    public function scopeRootUnits($query)
    {
        return $query->whereNull('parent_unit');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->nama_unit . ' (' . $this->kode_unit . ')';
    }

    public function getHierarchyPathAttribute()
    {
        $path = collect([$this]);
        $current = $this;
        
        while ($current->parentUnit) {
            $current = $current->parentUnit;
            $path->prepend($current);
        }
        
        return $path->pluck('nama_unit')->join(' > ');
    }

    public function getIsSubunitAttribute()
    {
        return $this->parent_unit !== null;
    }

    public function getManagerAttribute()
    {
        // Cari manajer dari unit ini atau unit parent
        if ($this->pimpinan) {
            return $this->pimpinan;
        }
        
        if ($this->parentUnit && $this->parentUnit->pimpinan) {
            return $this->parentUnit->pimpinan;
        }
        
        return null;
    }

    // Methods
    public function getAllSubunits()
    {
        $subunits = collect();
        
        foreach ($this->childUnits as $child) {
            $subunits->push($child);
            $subunits = $subunits->merge($child->getAllSubunits());
        }
        
        return $subunits;
    }

    public function getAuthorizedUnitsForComplaint($komplainId)
    {
        $pivot = $this->komplains()->where('komplain_id', $komplainId)->first()?->pivot;
        
        if ($pivot && $pivot->authorized_units) {
            $authorizedUnitIds = json_decode($pivot->authorized_units, true);
            return Unit::whereIn('unit_id', $authorizedUnitIds)->get();
        }
        
        return collect();
    }

    public function canUserFillComplaint($komplainId, $pegawaiId)
    {
        $pivot = $this->komplains()->where('komplain_id', $komplainId)->first()?->pivot;
        
        if ($pivot && $pivot->authorized_pegawai_ids) {
            $authorizedIds = json_decode($pivot->authorized_pegawai_ids, true);
            return in_array($pegawaiId, $authorizedIds);
        }
        
        return false;
    }

    /**
     * Check if unit needs approval from kanit
     */
    public function needsKanitApproval()
    {
        return $this->level_unit == 5 && 
               $this->parentUnit && 
               $this->parentUnit->level_unit == 4;
    }

    /**
     * Check if unit needs approval from manager
     */
    public function needsManagerApproval()
    {
        if ($this->level_unit == 5 && $this->parentUnit && $this->parentUnit->level_unit == 4) {
            return true;
        }
        
        if ($this->level_unit == 4 && $this->parentUnit && $this->parentUnit->level_unit == 3) {
            return true;
        }
        
        return false;
    }

    /**
     * Get approval workflow requirements
     */
    public function getApprovalRequirements()
    {
        return [
            'needs_unit_approval' => $this->needsUnitApproval(),
            'needs_manager_approval' => $this->needsManagerApproval(),
            'approval_sequence' => $this->getApprovalSequence()
        ];
    }

    /**
     * Get approval sequence
     */
    public function getApprovalSequence()
    {
        $sequence = [];
        
        if ($this->needsUnitApproval()) {
            $sequence[] = 'unit_approval';
        }
        
        if ($this->needsManagerApproval()) {
            $sequence[] = 'manager_approval';
        }
        
        return $sequence;
    }
}