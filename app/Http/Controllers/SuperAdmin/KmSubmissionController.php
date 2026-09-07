<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeItem;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class KmSubmissionController extends Controller
{
    /**
     * เผยแพร่ผลงานจากการแข่งขันเข้า KM
     */
    public function publish(Submission $submission): RedirectResponse
    {
        $this->ensureCompetitionFinished($submission);
        $this->ensureSubmissionCanBePublished($submission);

        $submission->loadMissing('competition');

        $primaryFile = $submission->files()
            ->where('is_primary', true)
            ->first();

        $knowledgeItem = KnowledgeItem::firstOrNew([
            'submission_id' => $submission->id,
        ]);

        if ($knowledgeItem->created_by === null) {
            $knowledgeItem->created_by = $submission->competition->created_by;
        }

        if ($knowledgeItem->category_id === null) {
            $knowledgeItem->category_id = $submission->competition->category_id;
        }

        $knowledgeItem->title = $submission->project_title;

        if (! $knowledgeItem->summary) {
            $knowledgeItem->summary = $submission->project_description;
        }

        $newCoverPath = null;

        if (
            (
                ! $knowledgeItem->cover_image
                || str_starts_with((string) $knowledgeItem->cover_image, 'submissions/')
            )
            && $primaryFile
            && is_string($primaryFile->file_path)
            && $primaryFile->file_path !== ''
            && str_starts_with((string) $primaryFile->mime_type, 'image/')
        ) {
            $newCoverPath = $this->copySubmissionCover($primaryFile->file_path);
            $knowledgeItem->cover_image = $newCoverPath;
        }

        $knowledgeItem->status = 'published';
        $knowledgeItem->published_at = now();

        try {
            $knowledgeItem->save();
        } catch (Throwable $exception) {
            if ($newCoverPath) {
                Storage::disk('local')->delete($newCoverPath);
            }

            throw $exception;
        }

        return back()->with('success', 'เผยแพร่ผลงานสู่ KM เรียบร้อยแล้ว');
    }

    /**
     * ถอนผลงานการแข่งขันออกจาก KM
     */
    public function unpublish(Submission $submission): RedirectResponse
    {
        $knowledgeItem = $submission->knowledgeItem;

        if ($knowledgeItem) {
            $knowledgeItem->update([
                'status' => 'draft',
                'published_at' => null,
            ]);
        }

        return back()->with('success', 'ถอนผลงานออกจาก KM เรียบร้อยแล้ว');
    }

    /**
     * ต้องจบการตัดสินแล้วเท่านั้น
     */
    private function ensureCompetitionFinished(Submission $submission): void
    {
        $submission->loadMissing('competition.judgingSession');

        $status = $submission->competition?->judgingSession?->status;

        abort_unless(
            in_array($status, ['ended', 'closed'], true),
            422,
            'การแข่งขันนี้ยังตัดสินไม่เสร็จ'
        );
    }

    /**
     * ตรวจว่าผลงานพร้อมเผยแพร่เข้า KM
     */
    private function ensureSubmissionCanBePublished(Submission $submission): void
    {
        abort_if(
            $submission->status === 'disqualified',
            422,
            'ผลงานที่ถูกตัดสิทธิ์ไม่สามารถเผยแพร่เข้าสู่ KM ได้'
        );

        $submission->loadMissing('competition');

        $competition = $submission->competition;

        abort_unless(
            $competition !== null,
            404,
            'ไม่พบการแข่งขันของผลงานนี้'
        );

        $activeRubricIds = $competition->rubrics()
            ->where('is_active', true)
            ->pluck('id');

        $acceptedAssignmentIds = $competition->judgeAssignments()
            ->where('assignment_status', 'accepted')
            ->pluck('id');

        $expectedScoreCount = $activeRubricIds->count() * $acceptedAssignmentIds->count();

        abort_if(
            $expectedScoreCount <= 0,
            422,
            'ยังไม่มีเกณฑ์การให้คะแนนหรือกรรมการที่ตอบรับ'
        );

        $submittedScoreCount = $submission->scores()
            ->whereNotNull('submitted_at')
            ->whereIn('rubric_id', $activeRubricIds)
            ->whereIn('judge_assignment_id', $acceptedAssignmentIds)
            ->count();

        abort_unless(
            $submittedScoreCount >= $expectedScoreCount,
            422,
            'ผลงานนี้ยังได้รับคะแนนจากกรรมการไม่ครบ'
        );
    }

    private function copySubmissionCover(string $sourcePath): string
    {
        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($sourcePath),
            422,
            'ไม่พบไฟล์รูปภาพต้นฉบับของผลงาน'
        );

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        abort_unless(
            in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true),
            422,
            'ไฟล์หลักของผลงานไม่ใช่รูปภาพที่รองรับ'
        );

        $targetPath = 'knowledge-items/covers/' . Str::random(40) . '.' . $extension;

        $copied = $disk->copy($sourcePath, $targetPath);

        abort_unless(
            $copied && $disk->exists($targetPath),
            500,
            'ไม่สามารถคัดลอกรูปภาพเข้าสู่ KM ได้'
        );

        return $targetPath;
    }
}