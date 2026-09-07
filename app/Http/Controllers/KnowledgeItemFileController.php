<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KnowledgeItemFileController extends Controller
{
    public function cover(KnowledgeItem $knowledgeItem): StreamedResponse
    {
        $this->authorizeAccess($knowledgeItem);
        $path = $knowledgeItem->cover_image;
        if (! $path || str_starts_with($path, 'submissions/')) {
            $files = $knowledgeItem->submission?->files();
            $file = $path
                ? $files?->where('file_path', $path)->first()
                : $files?->where('mime_type', 'like', 'image/%')->orderBy('id')->first();
            abort_unless($file && $file->managedPath()
                && str_starts_with((string) $file->mime_type, 'image/'), 404);

            return $this->serve($knowledgeItem, $file->managedPath(), 'submissions/', null, 'inline');
        }

        return $this->serve(
            $knowledgeItem,
            $knowledgeItem->cover_image,
            'knowledge-items/covers/',
            null,
            'inline'
        );
    }

    public function attachment(KnowledgeItem $knowledgeItem): StreamedResponse
    {
        return $this->serve(
            $knowledgeItem,
            $knowledgeItem->attachment_path,
            'knowledge-items/attachments/',
            $knowledgeItem->attachment_original_name ?: 'attachment',
            'attachment'
        );
    }

    private function serve(
        KnowledgeItem $knowledgeItem,
        ?string $path,
        string $directory,
        ?string $name,
        string $disposition
    ): StreamedResponse {
        $this->authorizeAccess($knowledgeItem);
        $normalized = $this->managedPath($path, $directory);
        $disk = Storage::disk('local');

        abort_unless($normalized && $disk->exists($normalized), 404);

        $headers = ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'];
        $mime = $disk->mimeType($normalized);
        if (is_string($mime) && $mime !== '') {
            $headers['Content-Type'] = $mime;
        }

        return $disk->response(
            $normalized,
            $name,
            $headers,
            $disposition
        );
    }

    private function authorizeAccess(KnowledgeItem $knowledgeItem): void
    {
        if ($knowledgeItem->status === 'published'
            && ($knowledgeItem->submission_id === null
                || ($knowledgeItem->submission && $knowledgeItem->submission->status !== 'disqualified'))) {
            return;
        }

        abort_unless(
            Auth::check() && Gate::allows('view', $knowledgeItem),
            404
        );
    }

    private function managedPath(?string $path, string $directory): ?string
    {
        if (! is_string($path) || $path === '' || str_contains($path, "\0")) {
            return null;
        }

        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) {
            return null;
        }

        $normalized = str_replace('\\', '/', $path);
        if (in_array('..', explode('/', $normalized), true)) {
            return null;
        }

        return str_starts_with($normalized, $directory)
            ? $normalized
            : null;
    }
}
