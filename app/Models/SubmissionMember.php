<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionMember extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'submission_members';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'submission_id',
        'fullname',
        'email',
        'phone',
        'organization',
        'position',
        'is_team_leader',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'is_team_leader' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ผลงานที่สังกัด
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}