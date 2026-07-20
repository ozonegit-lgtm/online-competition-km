<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionFormField extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'competition_form_fields';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'competition_id',
        'label',
        'field_name',
        'field_type',
        'placeholder',
        'help_text',
        'options',
        'is_required',
        'sort_order',
        'is_active',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
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
     * ค่าที่ผู้สมัครกรอก
     */
    public function values(): HasMany
    {
        return $this->hasMany(SubmissionFieldValue::class, 'field_id');
    }
}