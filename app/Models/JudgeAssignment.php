<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JudgeAssignment extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'judge_assignments';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'competition_id',
        'judge_id',
        'assigned_at',
        'assignment_status',
        'accepted_at',
        'declined_at',
        'submitted_at',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * การแข่งขัน
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * กรรมการ
     */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    /**
     * คะแนนที่กรรมการให้
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }
}