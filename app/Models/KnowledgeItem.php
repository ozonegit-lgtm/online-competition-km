<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KnowledgeItem extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'knowledge_items';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'submission_id',
        'title',
        'summary',
        'content',
        'cover_image',
        'is_featured',
        'status',
        'published_at',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ผลงานต้นฉบับ
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * แท็ก
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgeTag::class,
            'knowledge_item_tags',
            'knowledge_item_id',
            'knowledge_tag_id'
        );
    }

    /**
     * URL รูปปก
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        return asset('storage/' . $this->cover_image);
    }

    /**
     * เผยแพร่แล้วหรือไม่
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }
}