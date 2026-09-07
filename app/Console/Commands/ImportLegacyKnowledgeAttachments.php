<?php

namespace App\Console\Commands;

use App\Services\KnowledgeFileCleanup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ImportLegacyKnowledgeAttachments extends Command
{
    protected $signature = 'knowledge-items:import-legacy-attachments {--execute : Copy, verify, reference privately, then remove legacy sources}';

    protected $description = 'Safely reconcile legacy attachments (dry run by default; run with KM writes paused)';

    public function handle(): int
    {
        try {
            $plans = [];
            $rows = DB::table('knowledge_item_files')->orderBy('id')->get();
            foreach ($rows->groupBy('knowledge_item_id') as $group) {
                if ($group->count() !== 1) {
                    throw new RuntimeException("KM {$group->first()->knowledge_item_id} has multiple legacy attachments; no files were changed. Resolve the conflict without discarding attachments first.");
                }
                $plans[] = $this->plan($group->first());
            }

            foreach ($plans as $plan) {
                $this->line("KM {$plan['row']->knowledge_item_id}: {$plan['row']->file_path} -> {$plan['target']}");
            }
            if (! $this->option('execute')) {
                $this->info(count($plans).' attachment(s) checked. Dry run; no changes.');
                return self::SUCCESS;
            }
            foreach ($plans as $plan) {
                $this->migrate($plan);
            }
            $this->info(count($plans).' attachment(s) migrated and legacy sources removed.');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            report($exception);
            return self::FAILURE;
        }
    }

    private function plan(object $row): array
    {
        $item = DB::table('knowledge_items')->find($row->knowledge_item_id);
        if (! $item || ! preg_match('#\Aknowledge-items/'.(int) $row->knowledge_item_id.'/files/[A-Za-z0-9_.-]+\z#', $row->file_path)
            || str_contains($row->file_path, '..')) {
            throw new RuntimeException("Invalid legacy reference {$row->id}; left unchanged.");
        }
        $extension = strtolower($row->file_extension);
        if (! preg_match('/\A[a-z0-9]{1,20}\z/', $extension)) {
            throw new RuntimeException("Invalid legacy extension {$row->id}.");
        }
        $target = 'knowledge-items/attachments/legacy-'.$row->id.'-'.hash('sha256', $row->file_path).'.'.$extension;
        $this->assertSafeDestination($target);
        if ($item->attachment_path !== null && $item->attachment_path !== $target) {
            throw new RuntimeException("KM {$item->id} already has a different canonical attachment; left unchanged.");
        }

        $sources = [];
        $hash = null;
        foreach (['public', 'local'] as $disk) {
            if (Storage::disk($disk)->exists($row->file_path)) {
                $file = $this->safeFile($disk, $row->file_path);
                $currentHash = hash_file('sha256', $file);
                if (filesize($file) !== (int) $row->file_size || ($hash !== null && $hash !== $currentHash)) {
                    throw new RuntimeException("Legacy file {$row->id} size/content mismatch; left unchanged.");
                }
                $hash = $currentHash;
                $sources[] = $disk;
            }
        }
        // A prior run may have committed the reference and removed the source,
        // then stopped before retiring the legacy row. Verify and finish it.
        if (! $sources && $item->attachment_path === $target) {
            $file = $this->safeFile('local', $target);
            if (filesize($file) === (int) $row->file_size) {
                $hash = hash_file('sha256', $file);
            }
        }
        if (! $hash) {
            throw new RuntimeException("Legacy file {$row->id} is missing; reference preserved.");
        }
        if (Storage::disk('local')->exists($target)
            && hash_file('sha256', $this->safeFile('local', $target)) !== $hash) {
            throw new RuntimeException("Canonical destination for {$row->id} conflicts; left unchanged.");
        }
        return compact('row', 'target', 'sources', 'hash');
    }

    private function migrate(array $plan): void
    {
        ['row' => $row, 'target' => $target, 'sources' => $sources, 'hash' => $hash] = $plan;
        $copied = false;
        try {
            $this->assertSafeDestination($target);
            if (! Storage::disk('local')->exists($target)) {
                $copied = true;
                $stream = fopen($this->safeFile($sources[0], $row->file_path), 'rb');
                try {
                    if (! Storage::disk('local')->put($target, $stream, ['visibility' => 'private'])) {
                        throw new RuntimeException("Private copy failed for legacy file {$row->id}.");
                    }
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }
            if (hash_file('sha256', $this->safeFile('local', $target)) !== $hash) {
                throw new RuntimeException("Private copy verification failed for {$row->id}.");
            }
            DB::transaction(function () use ($row, $target): void {
                $item = DB::table('knowledge_items')->where('id', $row->knowledge_item_id)->lockForUpdate()->first();
                $legacy = DB::table('knowledge_item_files')->where('id', $row->id)->lockForUpdate()->first();
                if (! $item || ! $legacy || $legacy->file_path !== $row->file_path
                    || ($item->attachment_path !== null && $item->attachment_path !== $target)) {
                    throw new RuntimeException('Legacy reference changed during migration; retry with KM writes paused.');
                }
                DB::table('knowledge_items')->where('id', $item->id)->update([
                    'attachment_path' => $target,
                    'attachment_original_name' => mb_substr(basename(str_replace('\\', '/', preg_replace('/[\x00-\x1F\x7F]/', '', $row->original_name))), 0, 255),
                ]);
            });
        } catch (Throwable $exception) {
            if ($copied) {
                app(KnowledgeFileCleanup::class)->delete($target);
            }
            throw $exception;
        }

        // DB now references the verified private file. On cleanup failure keep
        // the legacy row as a durable retry record; never roll back this commit.
        foreach ($sources as $disk) {
            $file = $this->safeFile($disk, $row->file_path);
            if (hash_file('sha256', $file) !== $hash
                || ! Storage::disk($disk)->delete($row->file_path)
                || Storage::disk($disk)->exists($row->file_path)) {
                throw new RuntimeException("Legacy source cleanup failed ({$disk}:{$row->file_path}); private reference saved, legacy row retained. Retry this command.");
            }
        }
        DB::table('knowledge_item_files')->where('id', $row->id)->delete();
    }

    private function assertSafeDestination(string $path): void
    {
        $root = realpath(Storage::disk('local')->path(''));
        if (! $root) {
            throw new RuntimeException('Private storage root is missing.');
        }
        $part = $root;
        foreach (explode('/', $path) as $segment) {
            $part .= DIRECTORY_SEPARATOR.$segment;
            if (is_link($part) || (file_exists($part)
                && ! str_starts_with((string) realpath($part), $root.DIRECTORY_SEPARATOR))) {
                throw new RuntimeException("Unsafe private destination: {$path}.");
            }
        }
    }

    private function safeFile(string $disk, string $path): string
    {
        $root = realpath(Storage::disk($disk)->path(''));
        $file = Storage::disk($disk)->path($path);
        $resolved = realpath($file);
        if (! $root || ! $resolved || ! is_file($resolved)
            || ! str_starts_with($resolved, $root.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException("Unsafe or missing file {$disk}:{$path}.");
        }
        for ($part = $file; $part !== $root && strlen($part) > strlen($root); $part = dirname($part)) {
            if (is_link($part)) {
                throw new RuntimeException("Symlink rejected: {$disk}:{$path}.");
            }
        }
        return $resolved;
    }
}
