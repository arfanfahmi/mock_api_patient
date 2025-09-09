<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Komplain extends Model
{
    use HasFactory;

    protected $table = 'komplains';

    protected $guarded = [];

    protected $casts = [
        'tanggal_komplain' => 'date',
        'waktu_kejadian' => 'datetime',
        'submitted_at' => 'datetime',
        'units_notified_at' => 'datetime',
        'all_clarifications_completed_at' => 'datetime',
        'all_follow_ups_completed_at' => 'datetime',
        'all_approvals_completed_at' => 'datetime',
        'returned_to_humas_at' => 'datetime',
        'final_resolution_at' => 'datetime',
    ];

    // Constants
    const MEDIA_KOMPLAIN = [
        'Langsung' => 'Langsung', 
        'WhatsApp' => 'WhatsApp', 
        'Google review' => 'Google review', 
        'Instagram' => 'Instagram', 
        'Mobile Jkn' => 'Mobile Jkn', 
        'Telepon' => 'Telepon', 
        'SMS' => 'SMS', 
        'Email' => 'Email', 
        'Website' => 'Website', 
        'lainnya' => 'Lainnya'
    ];

    const KATEGORI = [
        'hijau' => 'Hijau',
        'kuning' => 'Kuning',
        'merah' => 'Merah'
    ];

    const DISAMPAIKAN_OLEH = [
        'ybs' => 'Yang Bersangkutan',
        'keluarga' => 'Keluarga',
        'lainnya' => 'Lainnya'
    ];

    const STATUS_KOMPLAIN = [
        'draft' => 'Draft',
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        'batal' => 'Batal'
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($komplain) {
            if (!$komplain->uuid) {
                $komplain->uuid = strtoupper(Str::random(8));
            }
            if (!$komplain->nomor_komplain) {
                $komplain->nomor_komplain = self::generateNomorKomplain();
            }
        });
    }

    // Relationships
    public function jenisKomplain(): BelongsTo
    {
        return $this->belongsTo(JenisKomplain::class, 'jenis_komplain_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'komplain_units')
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

    public function implementasis(): hasOne
    {
        return $this->hasOne(Implementasi::class, 'komplain_id');
    }

    public function klarifikasis(): HasMany
    {
        return $this->hasMany(Klarifikasi::class, 'komplain_id');
    }

    public function rtlUnits(): HasMany
    {
        return $this->hasMany(RtlUnit::class, 'komplain_id');
    }

    public function workflowPermissions(): HasMany
    {
        return $this->hasMany(WorkflowPermission::class, 'komplain_id');
    }

    public function approvalUnits(): HasMany
    {
        return $this->hasMany(ApprovalUnit::class, 'komplain_id');
    }

    public function approvalManajers(): HasMany
    {
        return $this->hasMany(ApprovalManajer::class, 'komplain_id');
    }

    public function rtlHumas(): HasOne
    {
        return $this->hasOne(RtlHumas::class, 'komplain_id');
    }

    public function hasilPenyelesaians(): HasOne
    {
        return $this->hasOne(HasilPenyelesaian::class, 'komplain_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'komplain_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(KomplainAuditLog::class, 'komplain_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status_komplain', 'aktif');
    }

    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status_komplain', $status);
    }

    public function scopeByUnit($query, $unitId)
    {
        return $query->whereHas('units', function($q) use ($unitId) {
            $q->where('units.id', $unitId);
        });
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_komplain', [$startDate, $endDate]);
    }

    // Accessors
    public function getMediaKomplainLabelAttribute()
    {
        $label = self::MEDIA_KOMPLAIN[$this->media_komplain] ?? $this->media_komplain;
        
        if ($this->media_komplain === 'lainnya' && $this->media_lainnya_detail) {
            $label = $this->media_lainnya_detail;
        }
        
        return $label;
    }

    public function getKategoriLabelAttribute()
    {
        return self::KATEGORI[$this->kategori] ?? $this->kategori;
    }

    public function getDisampaikanOlehLabelAttribute()
    {
        $label = self::DISAMPAIKAN_OLEH[$this->disampaikan_oleh] ?? $this->disampaikan_oleh;
        
        if ($this->disampaikan_oleh === 'lainnya' && $this->disampaikan_oleh_detail) {
            $label = $this->disampaikan_oleh_detail;
        }
        
        return $label;
    }

    public function getStatusKomplainLabelAttribute()
    {
        return self::STATUS_KOMPLAIN[$this->status_komplain] ?? $this->status_komplain;
    }

    // public function getKategoriBadgeClassAttribute()
    // {
    //     return match($this->kategori) {
    //         'merah' => 'badge-danger',
    //         'kuning' => 'badge-warning',
    //         'hijau' => 'badge-success',
    //         default => 'badge-secondary'
    //     };
    // }

    public function getOverallProgressAttribute()
    {
        $totalUnits = $this->units->count();
        if ($totalUnits === 0) return 0;

        $completedUnits = $this->units->where('pivot.status_unit', 'selesai')->count();
        return round(($completedUnits / $totalUnits) * 100);
    }

    // Methods
    public static function generateNomorKomplain()
    {
        $year = date('Y');
        $month = date('m');
        $lastNumber = self::whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->count() + 1;
        
        return sprintf('KMP/%s%s/%04d', $year, $month, $lastNumber);
    }

    public function setupWorkflowForUnits($unitIds)
    {
        foreach ($unitIds as $unitId) {
            $unit = Unit::where('unit_id', $unitId)->first();
            if (!$unit) continue;

            $workflowLevel = $this->determineWorkflowLevel($unit);
            $authorizedUnits = $this->getAuthorizedUnits($unit);
            $authorizedPegawais = $this->getAuthorizedPegawais($unit);

            // Attach unit dengan pivot data
            $this->units()->attach($unit->id, [
                'is_primary_target' => true,
                'parent_workflow_unit_id' => $unit->parent_unit ? 
                    Unit::where('unit_id', $unit->parent_unit)->first()?->id : null,
                'workflow_level' => $workflowLevel,
                'workflow_order' => 1,
                'authorized_units' => json_encode($authorizedUnits),
                'authorized_pegawai_ids' => json_encode($authorizedPegawais),
                'status_unit' => 'menunggu_klarifikasi',
            ]);

            // Create workflow permissions
            $this->createWorkflowPermissions($unit, $authorizedUnits, $authorizedPegawais);
        }
    }

    private function determineWorkflowLevel($unit)
    {
        if ($unit->parent_unit) {
            return 'subunit';
        }
        
        // Check if this unit has manager level based on level_unit
        if ($unit->level_unit <= 2) {
            return 'manager';
        }
        
        return 'unit';
    }

    private function getAuthorizedUnits($unit)
    {
        $authorized = [$unit->id];
        
        // Add parent unit if exists
        if ($unit->level_unit == 5 && optional($unit->parentUnit)->level_unit == 4) {
          if ($unit->parent_unit) {
              $authorized[] = $unit->parent_unit;
          }
        }
        
        return $authorized;
    }

    private function getAuthorizedPegawais($unit)
    {
        $authorized = [];
        
        // Add unit leader
        if ($unit->pegawai_id) {
            $authorized[] = $unit->pegawai_id;
        }
        
        // Add parent unit leader
        if ($unit->level_unit == 5 && optional($unit->parentUnit)->level_unit == 4) {
          if ($unit->parentUnit && $unit->parentUnit->pegawai_id) {
              $authorized[] = $unit->parentUnit->pegawai_id;
          }
        }
        
        return $authorized;
    }

    private function createWorkflowPermissions($unit, $authorizedUnits, $authorizedPegawais)
    {
        foreach ($authorizedUnits as $authUnitId) {
            $authUnit = Unit::where('unit_id', $authUnitId)->first();
            if (!$authUnit) continue;

            $reason = $authUnitId === $unit->unit_id ? 'own_unit' : 'parent_unit';

            WorkflowPermission::create([
                'komplain_id' => $this->id,
                'target_unit_id' => $unit->id,
                'authorized_unit_id' => $authUnit->id,
                'can_klarifikasi' => true,
                'can_rtl_unit' => true,
                'can_approval' => $reason === 'parent_unit',
                'authorization_reason' => $reason,
            ]);
        }
    }

    public function notifyUnits()
    {
        foreach ($this->units as $unit) {
            // Update pivot
            $this->units()->updateExistingPivot($unit->id, [
                'notified_at' => now()
            ]);

            // Create notifications
            $authorizedPegawais = json_decode($unit->pivot->authorized_pegawai_ids, true) ?? [];
            foreach ($authorizedPegawais as $pegawaiId) {
                Notification::create([
                    'komplain_id' => $this->id,
                    'unit_id' => $unit->id,
                    'pegawai_id' => $pegawaiId,
                    'notification_type' => 'new_complaint',
                    'message' => "Komplain baru #{$this->nomor_komplain} memerlukan klarifikasi dari unit {$unit->nama_unit}"
                ]);
            }
        }

        $this->update(['units_notified_at' => now()]);
    }

    public function checkAllClarificationsCompleted()
    {
        $allCompleted = $this->units()
            ->wherePivot('status_unit', '!=', 'klarifikasi_selesai')
            ->doesntExist();

        if ($allCompleted) {
            $this->update(['all_clarifications_completed_at' => now()]);
        }

        return $allCompleted;
    }

    public function checkAllFollowUpsCompleted()
    {
        $allCompleted = $this->units()
            ->wherePivot('status_unit', '!=', 'tindak_lanjut_selesai')
            ->doesntExist();

        if ($allCompleted) {
            $this->update(['all_follow_ups_completed_at' => now()]);
        }

        return $allCompleted;
    }

    public function checkAllApprovalsCompleted()
    {
        $allCompleted = $this->units()
            ->whereIn('pivot.status_unit', ['disetujui_manajer', 'selesai'])
            ->count() === $this->units()->count();

        if ($allCompleted) {
            $this->update(['all_approvals_completed_at' => now()]);
        }

        return $allCompleted;
    }

    // Tambahkan method berikut ke Model Komplain

    /**
     * Calculate overall progress of the komplain
     */
    // public function getOverallProgressAttribute()
    // {
    //     $units = $this->units;
    //     if($units->isEmpty()) {
    //         return 0;
    //     }
        
    //     $totalProgress = 0;
    //     foreach($units as $unit) {
    //         $unitProgress = $this->calculateUnitProgress($unit);
    //         $totalProgress += $unitProgress;
    //     }
        
    //     // Tambahkan progress global activities
    //     $globalProgress = $this->calculateGlobalProgress();
        
    //     $overallProgress = (($totalProgress / $units->count()) + $globalProgress) / 2;
        
    //     return round($overallProgress, 2);
    // }

    /**
     * Calculate progress for specific unit
     */
    public function calculateUnitProgress($unit)
    { 
        // Determine total steps dynamically
        $totalSteps = 5; // implementasi, klarifikasi, rtl_unit, hasil_penyelesaian, rtl_humas

        // Add approval steps if required
        if ($unit->needsKanitApproval()) {
            $totalSteps++;
        }
        if ($unit->needsManagerApproval()) {
            $totalSteps++;
        }

        $completedSteps = 0;

        if($this->implementasis) {
          $completedSteps++;
        }
        
        // 1. Klarifikasi Unit
        $unitKlarifikasis = optional($this->klarifikasis)->where('unit_id', $unit->id); Log::info($unitKlarifikasis);
        if($unitKlarifikasis && $unitKlarifikasis->count() > 0) { 
            $completedSteps++;
        }
        
        // 2. RTL Unit
        $unitRtls = optional($this->rtlUnits)->where('unit_id', $unit->id);
        if($unitRtls && $unitRtls->count() > 0) {
            $completedSteps++;
        }
        
        // 3. Approval Unit
        if ($unitRtls && $unitRtls->count() > 0) {
          $unitApprovals = optional($this->approvalUnits)->where('subunit_id', $unit->id);
          $approvedCount = $unitApprovals->where('status_approval', 'disetujui')->count();
          if($approvedCount > 0  || !$unit->needsKanitApproval()) {
              $completedSteps++;
          }
        }
        
        // 4. Approval Manajer
        if ($unitRtls && $unitRtls->count() > 0) {
          $manajerApprovals = optional($this->approvalManajers)->where('unit_id', $unit->id);
          $manajerApprovedCount = $manajerApprovals->where('status_approval', 'disetujui')->count();
          
          if($manajerApprovedCount > 0 || !$unit->needsManagerApproval() ) {
              $completedSteps++;
          }
        }

        // 5. RTL Humas (untuk unit tertentu jika ada)
        if($this->rtlHumas) { 
          $completedSteps++;
        }
        
        // 6. Hasil Penyelesaian
        $unitHasil = optional($this->hasilPenyelesaians)->where('unit_id', $unit->id);
        if($unitHasil) {
            $completedSteps++;
        }
        
        $progress = ($completedSteps / $totalSteps) * 100;
        
        // Update progress in pivot table if exists
        if($this->relationLoaded('units')) {
            $unitFromRelation = $this->units->where('id', $unit->id)->first();

            if($unitFromRelation && $unitFromRelation->pivot) {
                $unitFromRelation->pivot->progress_percentage = round($progress, 2);
            }
        }
        
        return round($progress, 2);
    }

    /**
     * Calculate global progress
     */
    public function calculateGlobalProgress()
    {
        $globalSteps = 3; // rtl_humas, implementasi_global, hasil_penyelesaian_global
        $globalCompleted = 0;
        
        // RTL Humas
        if($this->rtlHumas->isNotEmpty()) {
            $globalCompleted++;
        }
        
        // Implementasi Global
        $globalImplementations = $this->implementasis->where('unit_id', null);
        if($globalImplementations->isNotEmpty()) {
            $globalCompleted++;
        }
        
        // Hasil Penyelesaian Global
        $globalHasil = $this->hasilPenyelesaians->where('unit_id', null);
        if($globalHasil->isNotEmpty()) {
            $globalCompleted++;
        }
        
        return ($globalCompleted / $globalSteps) * 100;
    }

    /**
     * Update unit progress in pivot table
     */
    public function updateUnitProgress($unitId = null)
    {
      if($unitId) {
        $unit = $this->units()->where('units.id', $unitId)->first();
        if($unit) {
          $progress = $this->calculateUnitProgress($unit);
          $status = $this->calculateUnitStatus($unit);
          
          $this->units()->updateExistingPivot($unitId, [
              'progress_percentage' => $progress,
              'status_unit' => $status
          ]);
        }
      } else {
        // Update all units
        foreach($this->units as $unit) {
          $progress = $this->calculateUnitProgress($unit);
          $status = $this->calculateUnitStatus($unit);
          
          $this->units()->updateExistingPivot($unit->id, [
              'progress_percentage' => $progress,
              'status_unit' => $status
          ]);
        }
      }
    }

    /**
     * Get status badge class for kategori
     */
    public function getKategoriBadgeClassAttribute()
    {
        switch($this->kategori) {
            case 'merah':
                return 'bg-light-danger text-danger';
            case 'kuning':
                return 'bg-light-warning text-warning';
            case 'hijau':
                return 'bg-light-success text-success';
            default:
                return 'bg-light-secondary text-secondary';
        }
    }

    public function calculateUnitStatus($unit)
    {
      // Get data for status calculation
      $unitKlarifikasis = optional($this->klarifikasis)->where('unit_id', $unit->id)->count();
      $unitRtls = optional($this->rtlUnits)->where('unit_id', $unit->id);
      
      // Approval logic - ONLY if RTL exists (same condition as progress calculation)
      if ($unitRtls->count() > 0) {
          $unitApprovals = optional($this->approvalUnits)->where('subunit_id', $unit->id);
          $manajerApprovals = optional($this->approvalManajers)->where('unit_id', $unit->id);
          
          // Check for rejection status first (highest priority)
          $rejectedUnitApprovals = $unitApprovals->where('status_approval', 'ditolak')->count();
          $rejectedManajerApprovals = $manajerApprovals->where('status_approval', 'ditolak')->count();
          
          if ($rejectedUnitApprovals > 0) {
              return 'ditolak_unit';
          }
          
          if ($rejectedManajerApprovals > 0) {
              return 'ditolak_manajer';
          }
          
          // Check approval statuses
          $approvedUnitCount = $unitApprovals->where('status_approval', 'disetujui')->count();
          $approvedManajerCount = $manajerApprovals->where('status_approval', 'disetujui')->count();
          
          // Manager approval flow (only if unit needs manager approval)
          if ($unit->needsManagerApproval()) { 
              if ($approvedManajerCount > 0) {
                  return 'selesai';
              }
              
              // Check if unit approval is also needed and completed
              if ($unit->needsKanitApproval()) {
                  if ($approvedUnitCount > 0) {
                      return 'menunggu_approval_manajer';
                  }
                  return 'menunggu_approval_unit';
              } else {
                  // No unit approval needed, go straight to manager approval
                  return 'menunggu_approval_manajer';
              }
          }
          
          // Unit approval flow (only if unit needs unit approval)
          if ($unit->needsKanitApproval()) {
            if ($approvedUnitCount > 0) {
                return 'disetujui_unit';
            }
            return 'menunggu_approval_unit';
          }
          
          // If RTL exists but no approval needed, it's follow-up completed
          return 'selesai';
      }
      
      // RTL and follow-up statuses (when no RTL exists yet)
      if ($unitKlarifikasis > 0) {
          return 'menunggu_tindak_lanjut';
      }
      
      // Default status - waiting for clarification
      return 'menunggu_klarifikasi';
    }


    /**
     * Get human readable status label
     */
    public function getUnitStatusLabel($status)
    {
        $statusLabels = [
            'menunggu_klarifikasi' => 'Menunggu Klarifikasi Unit',
            'klarifikasi_selesai' => 'Klarifikasi Selesai',
            'menunggu_tindak_lanjut' => 'Menunggu RTL Unit',
            'tindak_lanjut_selesai' => 'RTL Unit Selesai',
            'menunggu_approval_unit' => 'Menunggu Approval Kanit',
            'menunggu_approval_manajer' => 'Menunggu Approval Manajer',
            'disetujui_unit' => 'Disetujui Unit',
            'disetujui_manajer' => 'Disetujui Manajer',
            'ditolak_unit' => 'Ditolak Unit',
            'ditolak_manajer' => 'Ditolak Manajer',
            'selesai' => 'Proses di Unit Selesai'
        ];
        
        return $statusLabels[$status] ?? $status;
    }

    
}