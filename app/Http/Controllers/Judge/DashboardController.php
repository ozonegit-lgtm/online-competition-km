<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\JudgeAssignment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $assignments = JudgeAssignment::query()
            ->where('judge_id', auth()->id())
            ->with([
                'competition:id,title,created_by,judging_start,judging_end',
                'competition.judgingSession',
            ])
            ->latest('assigned_at')
            ->paginate(12);

        return view('judge.dashboard', compact('assignments'));
    }
}
