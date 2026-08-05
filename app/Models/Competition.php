<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Competition extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'competitions';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'category_id',
        'template_id',
        'created_by',
        'title',
        'description',
        'cover_image',
        'competition_type',
        'visibility',
        'access_code',
        'registration_start',
        'registration_end',
        'judging_start',
        'judging_end',
        'result_announcement',
        'publish_scores',
        'publish_km',
        'status',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'registration_start' => 'datetime',
        'registration_end' => 'datetime',
        'judging_start' => 'datetime',
        'judging_end' => 'datetime',
        'result_announcement' => 'datetime',
        'publish_scores' => 'boolean',
        'publish_km' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * หมวดหมู่การแข่งขัน
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CompetitionCategory::class, 'category_id');
    }

    /**
     * Template การแข่งขัน
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CompetitionTemplate::class, 'template_id');
    }

    /**
     * ผู้สร้างการแข่งขัน
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ฟอร์มการแข่งขัน
     */
    public function formFields(): HasMany
    {
        return $this->hasMany(CompetitionFormField::class);
    }

    /**
     * เกณฑ์การให้คะแนน
     */
    public function rubrics(): HasMany
    {
        return $this->hasMany(Rubric::class);
    }

    /**
     * กรรมการ
     */
    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(JudgeAssignment::class);
    }
     public function judges(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'judge_assignments',
            'competition_id',
            'judge_id'
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

    /**
     * ผลงานที่ส่งเข้าประกวด
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * รางวัล
     */
    public function awards(): HasMany
    {
        return $this->hasMany(Award::class);
    }
    public function judgingSession(): HasOne
    {
        return $this->hasOne(JudgingSession::class);
    }
}