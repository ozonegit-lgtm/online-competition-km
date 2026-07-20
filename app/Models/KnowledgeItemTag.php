<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeItemTag extends Pivot
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'knowledge_item_tags';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Incrementing ID
     */
    public $incrementing = true;

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'knowledge_item_id',
        'knowledge_tag_id',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * บทความ KM
     */
    public function knowledgeItem(): BelongsTo
    {
        return $this->belongsTo(KnowledgeItem::class);
    }

    /**
     * Tag
     */
    public function knowledgeTag(): BelongsTo
    {
        return $this->belongsTo(KnowledgeTag::class);
    }
}