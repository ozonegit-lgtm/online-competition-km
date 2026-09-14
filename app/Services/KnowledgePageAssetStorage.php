<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class KnowledgePageAssetStorage
{
    public function store(UploadedFile $file, string $slot): string
    {
        if (! in_array($slot, ['logo', 'hero', 'about', 'footer-logo'], true)) {
            throw new RuntimeException('Unsupported knowledge page asset slot.');
        }

        $path = $file->store("knowledge-page/assets/{$slot}", 'local');
        if (! is_string($path) || $path === '' || ! Storage::disk('local')->exists($path)) {
            throw new RuntimeException('Unable to store knowledge page asset.');
        }

        return $path;
    }

    public function delete(?string $path): bool
    {
        $normalized = $this->managedPath($path);
        if (! $normalized) {
            return true;
        }

        try {
            return Storage::disk('local')->delete($normalized);
        } catch (Throwable $exception) {
            Log::error('Knowledge page asset cleanup failed', [
                'disk' => 'local', 'path' => $normalized, 'exception' => $exception,
            ]);

            return false;
        }
    }

    public function managedPath(?string $path, ?string $slot = null): ?string
    {
        if (! is_string($path) || $path === '' || str_contains($path, "\0")) {
            return null;
        }

        $normalized = str_replace('\\', '/', $path);
        if (str_starts_with($normalized, '/') || preg_match('/^[A-Za-z]:\//', $normalized)
            || in_array('..', explode('/', $normalized), true)) {
            return null;
        }

        $prefix = $slot ? "knowledge-page/assets/{$slot}/" : 'knowledge-page/assets/';

        return str_starts_with($normalized, $prefix) ? $normalized : null;
    }
}
