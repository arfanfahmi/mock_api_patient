<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'komplain_id',
        'unit_id',
        'pegawai_id',
        'user_id',
        'notification_type',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    const NOTIFICATION_TYPES = [
        'new_complaint' => 'Komplain Baru',
        'need_clarification' => 'Perlu Klarifikasi',
        'clarification_completed' => 'Klarifikasi Selesai',
        'rtl_unit_completed' => 'RTL Unit Selesai',
        'need_unit_approval' => 'Perlu Approval Unit',
        'need_manager_approval' => 'Perlu Approval Manajer',
        'unit_approved' => 'Disetujui Unit',
        'unit_rejected' => 'Ditolak Unit',
        'manager_approved' => 'Disetujui Manajer',
        'manager_rejected' => 'Ditolak Manajer',
        'returned_to_humas' => 'Kembali ke Humas',
        'complaint_completed' => 'Komplain Selesai',
    ];

    // Relationships
    public function komplain(): BelongsTo
    {
        return $this->belongsTo(Komplain::class, 'komplain_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForPegawai($query, $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    public function scopeByKomplain($query, $komplainId)
    {
        return $query->where('komplain_id', $komplainId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getNotificationTypeLabelAttribute()
    {
        return self::NOTIFICATION_TYPES[$this->notification_type] ?? $this->notification_type;
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public static function createForPegawai($komplainId, $pegawaiId, $type, $message, $unitId = null)
    {
        return self::create([
            'komplain_id' => $komplainId,
            'unit_id' => $unitId,
            'pegawai_id' => $pegawaiId,
            'notification_type' => $type,
            'message' => $message,
        ]);
    }

    public static function createForUser($komplainId, $userId, $type, $message, $unitId = null)
    {
        return self::create([
            'komplain_id' => $komplainId,
            'unit_id' => $unitId,
            'user_id' => $userId,
            'notification_type' => $type,
            'message' => $message,
        ]);
    }

    public static function markAllAsReadForPegawai($pegawaiId)
    {
        return self::where('pegawai_id', $pegawaiId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public static function markAllAsReadForUser($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}