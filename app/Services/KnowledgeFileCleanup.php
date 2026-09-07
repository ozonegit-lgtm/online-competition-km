<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class KnowledgeFileCleanup
{
    /** Never mask an original DB/upload error or undo an already committed DB change. */
    public function delete(?string $path): bool
    {
        if (! $path) {
            return true;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (in_array('..', explode('/', $path), true)
            || (! str_starts_with($path, 'knowledge-items/covers/')
                && ! str_starts_with($path, 'knowledge-items/attachments/'))) {
            return true; // Submission files belong to a different lifecycle.
        }

        try {
            if (! Storage::disk('local')->delete($path)) {
                throw new RuntimeException('Storage::delete returned false');
            }

            return true;
        } catch (Throwable $exception) {
            // Keep the exact relative path for an operator to retry after fixing
            // storage. Logging works even when the database itself is unavailable.
            Log::error('KM file cleanup failed; retry required', [
                'disk' => 'local',
                'path' => $path,
                'exception' => $exception,
            ]);

            return false;
        }
    }
}
