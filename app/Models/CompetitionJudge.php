<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionJudge extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'competition_judges';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'fullname',
        'email',
        'phone',
        'organization',
        'position',
        'password',
        'status',
        'last_login_at',
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
            'status' => 'boolean',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * รายการแข่งขันที่ได้รับมอบหมาย
     */
    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class, 'judge_id');
    }
}