<?php

namespace App\Console\Commands;

use App\Models\KnowledgeItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SecureKnowledgeItemFiles extends Command
{
    protected $signature = 'knowledge-items:secure-files {--execute : Move referenced files to private storage}';

    protected $description = 'Move managed Knowledge Item files from public to private storage';

    public function handle(): int
    {
        $execute = (bool) $this->option('execute');
        $counts = ['found' => 0, 'moved' => 0, 'skipped' => 0, 'failed' => 0];

        KnowledgeItem::query()
            ->select(['id', 'cover_image', 'attachment_path'])
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($execute, &$counts): void {
                foreach ($items as $item) {
                    foreach ([$item->cover_image, $item->attachment_path] as $path) {
                        if (! $this->isManagedPath($path)) {
                            $counts['skipped']++;
                            continue;
                        }

                        $counts['found']++;
                        if (! $execute) {
                            continue;
                        }

                        $this->move($path, $counts);
                    }
                }
            });

        $mode = $execute ? 'EXECUTE' : 'DRY RUN';
        $this->info("{$mode}: found={$counts['found']} moved={$counts['moved']} skipped={$counts['skipped']} failed={$counts['failed']}");

        return $counts['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function move(string $path, array &$counts): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        if ($private->exists($path)) {
            $counts['skipped']++;
            return;
        }

        if (! $public->exists($path)) {
            $counts['failed']++;
            $this->error("Missing public file: {$path}");
            return;
        }

        try {
            $stream = $public->readStream($path);
            $copied = is_resource($stream) && $private->writeStream($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            if (! $copied || ! $private->exists($path)) {
                $counts['failed']++;
                $this->error("Copy failed: {$path}");
                return;
            }

            if (! $public->delete($path)) {
                $counts['failed']++;
                $this->error("Public cleanup failed: {$path}");
                return;
            }

            $counts['moved']++;
        } catch (Throwable $exception) {
            $counts['failed']++;
            $this->error("Move failed: {$path}");
        }
    }

    private function isManagedPath(mixed $path): bool
    {
        if (! is_string($path) || $path === '' || str_contains($path, "\0")) {
            return false;
        }

        $normalized = str_replace('\\', '/', $path);
        if (str_starts_with($normalized, '/')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $normalized)
            || in_array('..', explode('/', $normalized), true)) {
            return false;
        }

        return str_starts_with($normalized, 'knowledge-items/covers/')
            || str_starts_with($normalized, 'knowledge-items/attachments/');
    }
}
