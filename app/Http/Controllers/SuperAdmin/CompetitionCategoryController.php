<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompetitionCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return redirect()->route('superadmin.categories.create');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CompetitionCategory::latest()->paginate(6);
        return view('superadmin.categories.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'category_name' => ['required','string','max:255','unique:competition_categories,category_name'],
            'category_slug' => ['nullable', 'string', 'max:255', 'unique:competition_categories,category_slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
            $validate['category_slug'] = Str::slug(
            $validate['category_slug'] ?: $validate['category_name']
        );
        CompetitionCategory::create($validate);
        $categories = CompetitionCategory::latest()->paginate(6);
        return view('superadmin.categories.create',compact('categories'));

    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionCategory $competitionCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionCategory $competitionCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompetitionCategory $competitionCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionCategory $competitionCategory)
    {
        //
    }
}
