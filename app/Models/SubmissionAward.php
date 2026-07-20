<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionAward extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'submission_awards';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'submission_id',
        'award_id',
        'awarded_at',
        'remark',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'awarded_at' => 'datetime',
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
     * รางวัล
     */
    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class);
    }

    /**
     * ชื่อรางวัล (Accessor)
     */
    public function getAwardNameAttribute(): ?string
    {
        return $this->award?->award_name;
    }

    /**
     * ลำดับรางวัล (Accessor)
     */
    public function getAwardRankAttribute(): ?int
    {
        return $this->award?->rank;
    }
}