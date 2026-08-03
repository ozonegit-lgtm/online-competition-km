<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Models\Submission;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $submission = Submission::with(['competition.category','files',])->latest('submitted_at')->paginate(12);
        return view('competition-admin.dashboard', compact('submission'));
    }
        public function submissions(): View
    {
        $submissions = Submission::with([
                'competition.category',
                'files',
            ])
            ->whereHas('competition', function ($query) {
                $query->where('created_by', auth()->id());
            })
            ->latest('submitted_at')
            ->paginate(12);

        return view(
            'competition-admin.submissions.index',
            compact('submissions')
        );
    }
}