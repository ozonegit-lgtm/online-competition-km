<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'activity_logs';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'user_id',
        'module',
        'action',
        'model_type',
        'model_id',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ผู้ใช้งาน
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ชื่อผู้ใช้งาน
     */
    public function getUserNameAttribute(): string
    {
        return $this->user?->username ?? 'System';
    }

    /**
     * ตรวจสอบว่าเป็นการสร้างข้อมูลหรือไม่
     */
    public function getIsCreateAttribute(): bool
    {
        return $this->action === 'create';
    }

    /**
     * ตรวจสอบว่าเป็นการแก้ไขข้อมูลหรือไม่
     */
    public function getIsUpdateAttribute(): bool
    {
        return $this->action === 'update';
    }

    /**
     * ตรวจสอบว่าเป็นการลบข้อมูลหรือไม่
     */
    public function getIsDeleteAttribute(): bool
    {
        return $this->action === 'delete';
    }
}