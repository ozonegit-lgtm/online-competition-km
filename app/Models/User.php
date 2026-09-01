<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'users';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'role_id',
        'username',
        'email',
        'password',
        'is_active',
    ];

    /**
     * ซ่อนข้อมูล
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Type Casting
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * User เป็นของ Role หนึ่ง Role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * User มีข้อมูล Profile เพียงหนึ่งรายการ
     */
    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    /**
     * User สร้างการแข่งขันได้หลายรายการ
     */
    public function competitions(): HasMany
    {
        return $this->hasMany(
            Competition::class,
            'created_by'
        );
    }

    public function knowledgeItems(): HasMany
    {
        return $this->hasMany(
            KnowledgeItem::class,
            'created_by'
        );
    }

    /**
     * User มี Activity Logs ได้หลายรายการ
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * รายการมอบหมายการตัดสินของ User คนนี้
     */
    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(
            JudgeAssignment::class,
            'judge_id'
        );
    }

    /**
     * การแข่งขันที่ User คนนี้ได้รับแต่งตั้งให้ตัดสิน
     */
    public function judgingCompetitions(): BelongsToMany
    {
        return $this->belongsToMany(
            Competition::class,
            'judge_assignments',
            'judge_id',
            'competition_id'
        )
            ->withPivot([
                'id',
                'assigned_at',
                'assignment_status',
                'accepted_at',
                'declined_at',
                'submitted_at',
            ])
            ->withTimestamps();
    }
}
