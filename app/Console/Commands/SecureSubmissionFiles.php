<?php

namespace App\Console\Commands;

use App\Models\SubmissionFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SecureSubmissionFiles extends Command
{
    protected $signature = 'submissions:secure-files {--execute : Move referenced files to private storage}';

    protected $description = 'Move referenced Submission files from public to private storage';

    public function handle(): int
    {
        $execute = (bool) $this->option('execute');
        $counts = ['found' => 0, 'moved' => 0, 'skipped' => 0, 'failed' => 0];

        SubmissionFile::query()
            ->select(['id', 'file_path'])
            ->whereNotNull('file_path')
            ->orderBy('id')
            ->chunkById(100, function ($files) use ($execute, &$counts): void {
                foreach ($files as $file) {
                    $path = $this->managedPath($file->file_path);
                    if (! $path) {
                        $counts['skipped']++;
                        continue;
                    }

                    $counts['found']++;
                    if ($execute) {
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

        $stream = null;
        try {
            $stream = $public->readStream($path);
            $copied = is_resource($stream) && $private->writeStream($path, $stream);

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
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function managedPath(mixed $path): ?string
    {
        if (! is_string($path) || $path === '' || str_contains($path, "\0")) {
            return null;
        }

        $normalized = str_replace('\\', '/', $path);
        if (str_starts_with($normalized, '/')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $normalized)
            || in_array('..', explode('/', $normalized), true)) {
            return null;
        }

        return str_starts_with($normalized, 'submissions/')
            ? $normalized
            : null;
    }
}
