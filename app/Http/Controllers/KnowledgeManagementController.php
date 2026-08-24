<?php

namespace App\Http\Controllers;

use App\Models\CompetitionCategory;
use App\Models\KnowledgeItem;
use App\Models\Submission;
use Illuminate\Http\Request;

class KnowledgeManagementController extends Controller
{
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
                'submission.competition.category',
                'submission.files',
            ])
            ->where('status', 'published')
            ->whereHas('submission', function ($query) {
                $query->where(
                    'status',
                    '!=',
                    'disqualified'
                );
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
            $query->whereHas(
                'submission.competition',
                function ($query) use ($request) {
                    $query->where(
                        'category_id',
                        $request->category
                    );
                }
            );
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
                'submission.competition.category',
                'submission.files',
            ])
            ->where('status', 'published')
            ->whereHas('submission', function ($query) {
                $query->where(
                    'status',
                    '!=',
                    'disqualified'
                );
            })
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

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
            'knowledgeItems' => $knowledgeItems,
            'featuredItems' => $featuredItems,
            'categories' => $categories,
        ]);
    }
}