<?php

namespace App\Models;

use App\Models\Klarifikasi;
use App\Models\ApprovalUnit;
use App\Models\ApprovalManajer;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Schema;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class Pegawai extends Model implements Authenticatable
{
    use AuthenticableTrait, HasApiTokens, HasFactory, Notifiable;
    //protected $primaryKey = 'kode_pegawai';
    public $incrementing = false;
    const UPDATED_AT = null;

    protected $guarded=[];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->id = $attributes['id'] ?? null;
        $this->kode_pegawai = $attributes['kode_pegawai'] ?? null;
        $this->nama = $attributes['nama'] ?? null;
    }

    // Relationships
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'pegawai_id');
    }

    public function klarifikasis(): HasMany
    {
        return $this->hasMany(Klarifikasi::class, 'filled_by_pegawai_id');
    }

    public function rtlUnits(): HasMany
    {
        return $this->hasMany(RtlUnit::class, 'pic_pegawai_id');
    }

    public function approvalUnits(): HasMany
    {
        return $this->hasMany(ApprovalUnit::class, 'approver_pegawai_id');
    }

    public function approvalManajers(): HasMany
    {
        return $this->hasMany(ApprovalManajer::class, 'manajer_pegawai_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'pegawai_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(KomplainAuditLog::class, 'actor_pegawai_id');
    }

    public function workflowPermissions(): HasMany
    {
        return $this->hasMany(WorkflowPermission::class, 'authorized_pegawai_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeByUnit($query, $unitId)
    {
        return $query->whereHas('units', function($q) use ($unitId) {
            $q->where('units.id', $unitId);
        });
    }

    // Accessors
    public function getFullInfoAttribute()
    {
        return $this->nama . ' (' . $this->kode_pegawai . ')';
    }

    // Methods
    public function hasPermissionForUnit($unitId, $permissionType)
    {
        return $this->workflowPermissions()
            ->where('target_unit_id', $unitId)
            ->where($permissionType, true)
            ->exists();
    }

    public function getUnreadNotificationsCount()
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    // Implement the methods required by the Authenticatable contract
    public function getAuthIdentifierName()
    {
        return 'kode_pegawai';
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}
