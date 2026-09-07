<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionFileController extends Controller
{
    public function show(SubmissionFile $submissionFile): StreamedResponse
    {
        return $this->serve($submissionFile, 'inline');
    }

    public function download(SubmissionFile $submissionFile): StreamedResponse
    {
        return $this->serve($submissionFile, 'attachment');
    }

    private function serve(SubmissionFile $file, string $disposition): StreamedResponse
    {
        $this->ensureCanAccess($file);
        $path = $file->managedPath();
        $disk = Storage::disk('local');
        abort_unless($path && $disk->exists($path), 404);

        $name = basename(str_replace('\\', '/', $file->original_name));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?: basename($path);

        return $disk->response($path, $name, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ], $disposition);
    }

    private function ensureCanAccess(SubmissionFile $file): void
    {
        $submission = $file->submission;
        $competition = $submission?->competition;
        abort_unless($submission && $competition, 404);

        $user = Auth::user();
        if ($user && $user->is_active) {
            $role = $user->role?->role_name;
            if ($role === 'Super Admin'
                || ($role === 'Competition Admin' && (int) $competition->created_by === (int) $user->id)
                || ($role === 'Judge' && $competition->judgeAssignments()
                    ->where('judge_id', $user->id)->where('assignment_status', 'accepted')->exists())) {
                return;
            }
        }

        abort_unless($submission->status !== 'disqualified', 404);
        if ($submission->knowledgeItem()->where('status', 'published')->exists()) {
            return;
        }

        // Public results expose only the first image of a top-three submission.
        if ($competition->publish_scores && $competition->result_announcement
            && str_starts_with((string) $file->mime_type, 'image/')
            && $competition->resultReadiness()['ready']) {
            $higherScores = $competition->submissions()->where('status', '!=', 'disqualified')
                ->where('final_score', '>', $submission->final_score)->count();
            $image = $submission->files()->where('mime_type', 'like', 'image/%')
                ->orderByDesc('is_primary')->orderBy('id')->first();
            if ($higherScores < 3 && $image?->is($file)) {
                return;
            }
        }

        abort(404);
    }
}
