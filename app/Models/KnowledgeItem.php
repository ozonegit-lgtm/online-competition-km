<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Throwable;

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
        'created_by',
        'category_id',
        'title',
        'summary',
        'content',
        'cover_image',
        'attachment_path',
        'attachment_original_name',
        'is_featured',
        'status',
        'published_at',
        'knowledge_type',
        'knowledge_category_id',
        'external_url',
        'archived_at',
        'sort_order',
        'publication_year',
        'volume',
        'issue',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
        'sort_order' => 'integer',
        'publication_year' => 'integer',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            CompetitionCategory::class,
            'category_id'
        );
    }

    public function knowledgeCategory(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'knowledge_category_id');
    }

    public function scopeEbooks(Builder $query): Builder
    {
        return $query->where('knowledge_type', 'ebook');
    }

    public function scopeLegacy(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('knowledge_type')
                ->orWhere('knowledge_type', '!=', 'ebook');
        });
    }

    public function getIsEbookAttribute(): bool
    {
        return $this->knowledge_type === 'ebook';
    }

    /**
     * URL รูปปก
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        $path = $this->cover_image;
        if ($path && (str_starts_with($path, 'knowledge-items/covers/')
            || str_starts_with($path, 'submissions/'))) {
            return route('knowledge-items.cover', $this);
        }

        return ! $path && $this->submission?->files
            ->contains(fn ($file) => str_starts_with((string) $file->mime_type, 'image/'))
            ? route('knowledge-items.cover', $this)
            : null;
    }

    public function attachmentMedia(): array
    {
        $name = $this->attachment_original_name ?: $this->attachment_path;
        $extension = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: '';
        $type = match ($extension) {
            'pdf' => 'PDF',
            'doc' => 'DOC',
            'docx' => 'DOCX',
            'ppt' => 'PPT',
            'pptx' => 'PPTX',
            'zip' => 'ZIP',
            default => $extension !== '' ? strtoupper(substr($extension, 0, 12)) : 'FILE',
        };

        $media = [
            'exists' => false,
            'is_image' => false,
            'mime_type' => null,
            'type' => $type,
        ];
        $path = $this->managedAttachmentPath();

        if (! $path) {
            return $media;
        }

        try {
            $disk = Storage::disk('local');
            if (! $disk->exists($path)) {
                return $media;
            }

            $mimeType = $disk->mimeType($path);
        } catch (Throwable $exception) {
            report($exception);

            return $media;
        }

        $hasMimeType = is_string($mimeType) && $mimeType !== '';
        $media['exists'] = true;
        $media['mime_type'] = $hasMimeType ? $mimeType : null;
        $media['is_image'] = $hasMimeType
            ? str_starts_with($mimeType, 'image/')
            : in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);

        return $media;
    }

    private function managedAttachmentPath(): ?string
    {
        $path = $this->attachment_path;
        if (! is_string($path) || $path === '' || str_contains($path, "\0")) {
            return null;
        }

        $normalized = str_replace('\\', '/', $path);
        if (str_starts_with($normalized, '/')
            || preg_match('/^[A-Za-z]:\//', $normalized)
            || in_array('..', explode('/', $normalized), true)) {
            return null;
        }

        return str_starts_with($normalized, 'knowledge-items/attachments/')
            ? $normalized
            : null;
    }

    /**
     * เผยแพร่แล้วหรือไม่
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }
}
