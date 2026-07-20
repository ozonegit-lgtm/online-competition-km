<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubric extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'rubrics';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'competition_id',
        'criteria_name',
        'description',
        'max_score',
        'weight',
        'sort_order',
        'is_active',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
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
     * คะแนนของเกณฑ์นี้
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }
}