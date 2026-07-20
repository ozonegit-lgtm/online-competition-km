<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Award extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'awards';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'competition_id',
        'award_name',
        'description',
        'rank',
        'certificate_title',
        'is_special',
        'is_active',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'rank' => 'integer',
        'is_special' => 'boolean',
        'is_active' => 'boolean',
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
     * ผลงานที่ได้รับรางวัล
     */
    public function submissionAwards(): HasMany
    {
        return $this->hasMany(SubmissionAward::class);
    }
}