<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeItem;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KmSubmissionController extends Controller
{
    /**
     * ผลงานจากการแข่งขันที่จบแล้ว
     */
    public function index(Request $request)
    {
        $query = Submission::query()
        ->where('status', '!=', 'disqualified')
            ->with([
                'competition',
                'knowledgeItem',
                'files',
            ])
            ->whereHas('competition', function ($query) {
                $query->where('created_by', Auth::id());
            })
            ->whereHas('competition.judgingSession', function ($query) {
                $query->whereIn('status', [
                    'ended',
                    'closed',
                ]);
            });

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($query) use ($search) {
                $query
                    ->where('project_title', 'like', "%{$search}%")
                    ->orWhere('submission_code', 'like', "%{$search}%")
                    ->orWhereHas('competition', function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('competition_id')) {
            $query->where(
                'competition_id',
                $request->integer('competition_id')
            );
        }

        if ($request->filled('km_status')) {
            if ($request->km_status === 'published') {
                $query->whereHas('knowledgeItem', function ($query) {
                    $query->where('status', 'published');
                });
            }

            if ($request->km_status === 'unpublished') {
                $query->where(function ($query) {
                    $query
                        ->whereDoesntHave('knowledgeItem')
                        ->orWhereHas('knowledgeItem', function ($query) {
                            $query->where('status', '!=', 'published');
                        });
                });
            }
        }

        $submissions = $query
            ->orderByDesc('final_score')
            ->paginate(15)
            ->withQueryString();

        $competitions = \App\Models\Competition::query()
            ->where('created_by', Auth::id())
            ->whereHas('judgingSession', function ($query) {
                $query->whereIn('status', [
                    'ended',
                    'closed',
                ]);
            })
            ->orderByDesc('id')
            ->get([
                'id',
                'title',
            ]);

        return view(
            'competition-admin.km.submissions',
            [
                'submissions' => $submissions,
                'competitions' => $competitions,
            ]
        );
    }

    /**
     * เผยแพร่ผลงานเข้า KM
     */
    public function publish(
        Submission $submission
    ): RedirectResponse {
        $this->ensureSubmissionOwner($submission);
        $this->ensureCompetitionFinished($submission);
        $this->ensureSubmissionCanBePublished($submission);

        $primaryFile = $submission->files()
            ->where('is_primary', true)
            ->first();

        $knowledgeItem = KnowledgeItem::firstOrNew([
            'submission_id' => $submission->id,
        ]);

        if ($knowledgeItem->created_by === null) {
            $knowledgeItem->created_by =
                $submission->competition->created_by;
        }

        if ($knowledgeItem->category_id === null) {
            $knowledgeItem->category_id =
                $submission->competition->category_id;
        }

        $knowledgeItem->title = $submission->project_title;

        if (! $knowledgeItem->summary) {
            $knowledgeItem->summary =
                $submission->project_description;
        }

        if (
            ! $knowledgeItem->cover_image
            && $primaryFile
            && str_starts_with(
                (string) $primaryFile->mime_type,
                'image/'
            )
        ) {
            $knowledgeItem->cover_image =
                $primaryFile->file_path;
        }

        $knowledgeItem->status = 'published';
        $knowledgeItem->published_at = now();

        $knowledgeItem->save();

        return back()->with(
            'success',
            'เผยแพร่ผลงานสู่ KM เรียบร้อยแล้ว'
        );
    }

    /**
     * ถอนผลงานออกจาก KM
     */
    public function unpublish(
        Submission $submission
    ): RedirectResponse {
        $this->ensureSubmissionOwner($submission);

        $knowledgeItem = $submission->knowledgeItem;

        if ($knowledgeItem) {
            $knowledgeItem->update([
                'status' => 'draft',
                'published_at' => null,
            ]);
        }

        return back()->with(
            'success',
            'ถอนผลงานออกจาก KM เรียบร้อยแล้ว'
        );
    }

    /**
     * ตรวจว่า Submission อยู่ในการแข่งขันของ Admin คนนี้
     */
    private function ensureSubmissionOwner(
        Submission $submission
    ): void {
        $submission->loadMissing('competition');

        abort_unless(
            (int) $submission->competition?->created_by
                === (int) Auth::id(),
            403,
            'คุณไม่มีสิทธิ์จัดการผลงานนี้'
        );
    }

    /**
     * ต้องจบการตัดสินแล้วเท่านั้น
     */
    private function ensureCompetitionFinished(
        Submission $submission
    ): void {
        $submission->loadMissing(
            'competition.judgingSession'
        );

        $status =
            $submission->competition
                ?->judgingSession
                ?->status;

        abort_unless(
            in_array(
                $status,
                ['ended', 'closed'],
                true
            ),
            422,
            'การแข่งขันนี้ยังตัดสินไม่เสร็จ'
        );
    }
    /**
 * ตรวจว่าผลงานพร้อมเผยแพร่เข้า KM
 */
    private function ensureSubmissionCanBePublished(
        Submission $submission
    ): void {
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

        $acceptedAssignmentIds = $competition
            ->judgeAssignments()
            ->where('assignment_status', 'accepted')
            ->pluck('id');

        $expectedScoreCount =
            $activeRubricIds->count()
            * $acceptedAssignmentIds->count();

        abort_if(
            $expectedScoreCount <= 0,
            422,
            'ยังไม่มีเกณฑ์การให้คะแนนหรือกรรมการที่ตอบรับ'
        );

        $submittedScoreCount = $submission->scores()
            ->whereNotNull('submitted_at')
            ->whereIn(
                'rubric_id',
                $activeRubricIds
            )
            ->whereIn(
                'judge_assignment_id',
                $acceptedAssignmentIds
            )
            ->count();

        abort_unless(
            $submittedScoreCount >= $expectedScoreCount,
            422,
            'ผลงานนี้ยังได้รับคะแนนจากกรรมการไม่ครบ'
        );
    }
}
