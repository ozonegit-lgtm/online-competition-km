<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;



class CompetitionTemplate extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'competition_templates';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'template_name',
        'template_slug',
        'default_description',
        'cover_image',
        'is_active',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Template หนึ่งสามารถถูกใช้สร้างการแข่งขันได้หลายรายการ
     */
    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'template_id');
    }
    public function formFields(): HasMany
    {
        return $this->hasMany(CompetitionTemplateFormField::class,'template_id')->orderBy('sort_order');
    }
}