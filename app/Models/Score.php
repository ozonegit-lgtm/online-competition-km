<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'scores';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'submission_id',
        'rubric_id',
        'judge_assignment_id',
        'score',
        'comment',
        'submitted_at',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ผลงาน
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * เกณฑ์การให้คะแนน
     */
    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    /**
     * กรรมการผู้ให้คะแนน
     */
    public function judgeAssignment(): BelongsTo
    {
        return $this->belongsTo(JudgeAssignment::class);
    }

    /**
     * คะแนนถ่วงน้ำหนัก
     */
    public function getWeightedScoreAttribute(): float
    {
        if (! $this->rubric) {
            return (float) $this->score;
        }

        $maxScore = (float) $this->rubric->max_score;
        $weight = (float) $this->rubric->weight;
        $score = (float) $this->score;

        if ($maxScore <= 0) {
            return 0.0;
        }

        return round(
            ($score / $maxScore) * $weight,
            2
        );
    }
}