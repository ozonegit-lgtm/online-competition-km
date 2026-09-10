<?php

namespace App\Http\Controllers;

use App\Models\CompetitionCategory;
use App\Models\Competition;
use App\Models\KnowledgeItem;
use App\Models\Submission;
use Illuminate\Http\Request;

class KnowledgeManagementController extends Controller
{
    public function show(KnowledgeItem $knowledgeItem)
    {
        abort_unless($knowledgeItem->status === 'published', 404);

        if ($knowledgeItem->submission_id !== null) {
            $knowledgeItem->load([
                'submission.competition.category',
                'submission.members',
                'submission.files',
                'submission.fieldValues.field',
            ]);

            abort_unless(
                $knowledgeItem->submission && $knowledgeItem->submission->status !== 'disqualified',
                404
            );

            $submission = $knowledgeItem->submission;
            $competition = $submission->competition;

            if ($competition
                && $competition->publish_scores
                && $competition->result_announcement !== null
                && $competition->resultReadiness()['ready'] === true) {
                $rankedSubmissions = $competition->submissions()
                    ->where('status', '!=', 'disqualified')
                    ->orderByDesc('final_score')
                    ->orderBy('id')
                    ->get(['id', 'final_score']);

                $lastScore = null;
                $lastRank = 0;
                $submissionRank = null;
                $rankCounts = [];

                // Match index(): competition ranks (1, 1, 3), comparing integer hundredths.
                foreach ($rankedSubmissions as $index => $candidate) {
                    $currentScore = (int) round((float) $candidate->final_score * 100);

                    if ($lastScore === null || $currentScore !== $lastScore) {
                        $lastRank = $index + 1;
                        $lastScore = $currentScore;
                    }

                    $rankCounts[$lastRank] = ($rankCounts[$lastRank] ?? 0) + 1;

                    if ((int) $candidate->id === (int) $submission->id) {
                        $submissionRank = $lastRank;
                    }
                }

                if ($submissionRank !== null && $submissionRank <= 3) {
                    $submission->setAttribute('rank', $submissionRank);
                    $submission->setAttribute('is_shared_rank', $rankCounts[$submissionRank] > 1);
                }
            }
        } else {
            $knowledgeItem->load('creator');
        }

        return view('show', compact('knowledgeItem'));
    }

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | รายการผลงาน KM
        |--------------------------------------------------------------------------
        |
        | แสดงเฉพาะรายการที่ผู้จัดเลือกเผยแพร่
        | และผลงานต้องไม่ถูกตัดสิทธิ์
        |
        */
        $query = KnowledgeItem::query()
            ->with([
                'category',
                'submission.competition.category',
                'submission.files',
            ])
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('submission_id')
                    ->orWhereHas('submission', fn ($query) => $query->where('status', '!=', 'disqualified'));
            });

        /*
        |--------------------------------------------------------------------------
        | ค้นหา
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = $request
                ->string('search')
                ->toString();

            $query->where(function ($query) use ($search) {
                $query
                    ->where(
                        'title',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'summary',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'content',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'submission.competition',
                        function ($query) use ($search) {
                            $query->where(
                                'title',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | กรองหมวดหมู่
        |--------------------------------------------------------------------------
        */
        if ($request->filled('category')) {
            $query->where(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereNull('submission_id')->where('category_id', $request->category);
                })->orWhereHas('submission.competition', fn ($query) => $query->where('category_id', $request->category));
            });
        }

        /*
        |--------------------------------------------------------------------------
        | เรียงลำดับ
        |--------------------------------------------------------------------------
        */
        switch ($request->get('sort', 'latest')) {
            case 'score':
                $query
                    ->orderByDesc(
                        Submission::query()
                            ->select('final_score')
                            ->whereColumn(
                                'submissions.id',
                                'knowledge_items.submission_id'
                            )
                            ->limit(1)
                    )
                    ->orderByDesc('published_at');
                break;

            case 'title':
                $query
                    ->orderBy('title')
                    ->orderByDesc('published_at');
                break;

            default:
                $query->orderByDesc('published_at');
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | แบ่งหน้า
        |--------------------------------------------------------------------------
        */
        $knowledgeItems = $query
            ->paginate(12)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | ผลงานแนะนำ
        |--------------------------------------------------------------------------
        |
        | ต้องเผยแพร่แล้วและต้องไม่ถูกตัดสิทธิ์
        |
        */
        $featuredItems = KnowledgeItem::query()
            ->with([
                'category',
                'submission.competition.category',
                'submission.files',
            ])
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('submission_id')
                    ->orWhereHas('submission', fn ($query) => $query->where('status', '!=', 'disqualified'));
            })
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->take(6)
            ->get();


            /*
|--------------------------------------------------------------------------
| ผลการแข่งขันที่ Admin ประกาศ
|--------------------------------------------------------------------------
|
| แยกออกจาก KnowledgeItem โดยสมบูรณ์
| ใช้ publish_scores เป็นสถานะว่าต้องแสดงหน้า Public หรือไม่
|
*/
        $publishedResults = Competition::query()
            ->with([
                'category:id,category_name',

                'submissions' => function ($query) {
                    $query
                        ->where('status', '!=', 'disqualified')
                        ->with([
                            'files' => fn ($query) => $query
                                ->orderByDesc('is_primary')
                                ->orderBy('id'),
                        ])
                        ->orderByDesc('final_score')
                        ->orderBy('id');
                },
            ])
            ->where('publish_scores', true)
            ->whereNotNull('result_announcement')
            ->orderByDesc('result_announcement')
            ->get()
            ->filter(
                fn ($competition) =>
                    $competition->resultReadiness()['ready']
            )
            ->map(function ($competition) {
                $lastScore = null;
                $lastRank = 0;

                /*
                * จัดอันดับร่วมแบบ 1, 1, 3
                * ใช้คะแนนหน่วยสตางค์เพื่อไม่ให้มีปัญหา Float
                */
                $rankedSubmissions = $competition
                    ->submissions
                    ->values()
                    ->map(function (
                        $submission,
                        $index
                    ) use (
                        &$lastScore,
                        &$lastRank
                    ) {
                        $currentScore = (int) round(
                            (float) $submission->final_score * 100
                        );

                        if (
                            $lastScore === null ||
                            $currentScore !== $lastScore
                        ) {
                            $lastRank = $index + 1;
                            $lastScore = $currentScore;
                        }

                        $submission->setAttribute(
                            'rank',
                            $lastRank
                        );

                        return $submission;
                    });

                $rankCounts = $rankedSubmissions->countBy(
                    fn ($submission) =>
                        (int) $submission->rank
                );

                $topSubmissions = $rankedSubmissions
                    ->filter(
                        fn ($submission) =>
                            (int) $submission->rank <= 3
                    )
                    ->each(function ($submission) use ($rankCounts) {
                        $submission->setAttribute(
                            'is_shared_rank',
                            $rankCounts->get(
                                (int) $submission->rank,
                                0
                            ) > 1
                        );
                    })
                    ->values();

                $competition->setRelation(
                    'submissions',
                    $topSubmissions
                );

                return $competition;
            })
            ->filter(
                fn ($competition) =>
                    $competition->submissions->isNotEmpty()
            )
            ->values();
        /*
        |--------------------------------------------------------------------------
        | หมวดหมู่
        |--------------------------------------------------------------------------
        */
        $categories = CompetitionCategory::query()
            ->where('is_active', true)
            ->orderBy('category_name')
            ->get();

        return view('index', [
            'publishedResults' => $publishedResults,
            'knowledgeItems' => $knowledgeItems,
            'featuredItems' => $featuredItems,
            'categories' => $categories,
        ]);
    }
}
