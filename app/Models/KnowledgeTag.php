<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KnowledgeTag extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'knowledge_tags';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'tag_name',
        'slug',
        'color',
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
     * บทความ KM
     */
    public function knowledgeItems(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgeItem::class,
            'knowledge_item_tags',
            'knowledge_tag_id',
            'knowledge_item_id'
        );
    }
}