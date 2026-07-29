<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionCategory extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'competition_categories';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'category_name',
        'category_slug',
        'description',
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
     * หมวดหมู่หนึ่ง มีการแข่งขันได้หลายรายการ
     */
    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class, 'category_id');
    }
    
    
}