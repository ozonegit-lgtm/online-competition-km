<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionTemplateFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'label',
        'field_name',
        'system_field',
        'field_type',
        'placeholder',
        'help_text',
        'options',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            CompetitionTemplate::class,
            'template_id'
        );
    }
}