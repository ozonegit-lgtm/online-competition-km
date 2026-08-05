<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JudgingSession extends Model
{
    use HasFactory;

    public const STATUS_WAITING = 'waiting';
    public const STATUS_LIVE = 'live';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ENDED = 'ended';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'competition_id',
        'controller_user_id',
        'current_submission_id',
        'current_file_id',
        'status',
        'current_page',
        'scroll_progress',
        'zoom',
        'state_version',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'current_page' => 'integer',
        'scroll_progress' => 'decimal:5',
        'zoom' => 'decimal:2',
        'state_version' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function controller(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'controller_user_id'
        );
    }

    public function currentSubmission(): BelongsTo
    {
        return $this->belongsTo(
            Submission::class,
            'current_submission_id'
        );
    }

    public function currentFile(): BelongsTo
    {
        return $this->belongsTo(
            SubmissionFile::class,
            'current_file_id'
        );
    }

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isLive(): bool
    {
        return $this->status === self::STATUS_LIVE;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isEnded(): bool
    {
        return $this->status === self::STATUS_ENDED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}