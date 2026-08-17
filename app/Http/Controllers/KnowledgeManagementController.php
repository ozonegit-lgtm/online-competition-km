<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\CompetitionCategory;
use Illuminate\Http\Request;

class KnowledgeManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::query()
        ->with([
            'competition.category',
            'files',
            'awards',
        ])
        ->whereHas('scores', function ($q) {
            $q->whereNotNull('submitted_at');
        });

        // ค้นหา
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('project_title', 'like', "%{$search}%")
                    ->orWhere('project_description', 'like', "%{$search}%")
                    ->orWhere('team_name', 'like', "%{$search}%")
                    ->orWhereHas('competition', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    });
            });
        }

        // กรองหมวดหมู่
        if ($request->filled('category')) {
            $query->whereHas('competition', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // เรียงข้อมูล
        switch ($request->get('sort', 'latest')) {
            case 'score':
                $query->orderByDesc('final_score');
                break;

            case 'title':
                $query->orderBy('project_title');
                break;

            default:
                $query->latest('submitted_at');
                break;
        }

        $works = $query->paginate(12);

        // ผลงานที่ได้รับรางวัล
        $featuredWorks = Submission::query()
        ->with([
            'competition.category',
            'files',
            'awards',
        ])
        ->whereHas('scores', function ($q) {
            $q->whereNotNull('submitted_at');
        })
        ->whereHas('awards')
        ->orderByDesc('final_score')
        ->take(6)
        ->get();

        // หมวดหมู่
        $categories = CompetitionCategory::query()
        ->where('is_active', true)
        ->orderBy('category_name')
        ->get();

        return view('index', [
            'works' => $works,
            'featuredWorks' => $featuredWorks,
            'categories' => $categories,
        ]);
    }
}