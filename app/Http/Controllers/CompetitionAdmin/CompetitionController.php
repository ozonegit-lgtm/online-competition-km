<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\CompetitionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompetitionController extends Controller
{
    public function index()
    {
        $competitions = Competition::with([
            'category',
            'template',
        ])->where('created_by', Auth::id())->latest()->paginate(10);
        return view('competition-admin.competitions.create',compact('competitions'));
    }

    public function create()
    {
        $categories = CompetitionCategory::where('is_active', true)->orderBy('category_name')->get();
        $templates = CompetitionTemplate::where('is_active', true)->orderBy('template_name')->get();
        return view('competition-admin.competitions.create',compact('categories', 'templates'));
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Competition $competition)
    {
        //
    }

    public function edit(Competition $competition)
    {
        //
    }

    public function update(
        Request $request,
        Competition $competition
    ) {
        //
    }

    public function destroy(Competition $competition)
    {
        //
    }
}