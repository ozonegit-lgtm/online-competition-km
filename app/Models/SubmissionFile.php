<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFile extends Model
{
    use HasFactory;

    /**
     * ตารางที่ใช้งาน
     */
    protected $table = 'submission_files';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'submission_id',
        'original_name',
        'stored_name',
        'file_path',
        'file_extension',
        'mime_type',
        'file_size',
        'is_primary',
    ];

    /**
     * Type Casting
     */
    protected $casts = [
        'file_size' => 'integer',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ผลงานที่ไฟล์นี้สังกัด
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * URL สำหรับเรียกใช้งานไฟล์
     */
    public function getFileUrlAttribute(): string
    {
        return route('submission-files.show', $this);
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('submission-files.download', $this);
    }

    public function managedPath(): ?string
    {
        $path = $this->file_path;
        if (! is_string($path) || str_contains($path, "\0")
            || str_contains($path, '\\') || in_array('..', explode('/', $path), true)) {
            return null;
        }

        $submission = $this->submission;
        if (! $submission) {
            return null;
        }

        $directory = "submissions/{$submission->competition_id}/{$submission->submission_code}/";

        return str_starts_with($path, $directory) ? $path : null;
    }
}
