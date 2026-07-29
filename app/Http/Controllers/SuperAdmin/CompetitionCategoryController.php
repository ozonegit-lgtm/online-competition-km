<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        return redirect()->route('superadmin.categories.create')->with('success', 'สร้างประเภทการแข่งขันสำเร็จ');

    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionCategory $competitionCategory)
    {
        // return view('superadmin.categories.show', compact('competitionCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionCategory $competitionCategory)
    {
        return view('superadmin.categories.edit', compact('competitionCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompetitionCategory $competitionCategory)
    {
        $validate = $request->validate([
            'category_name' =>['nullable','string','max:255'],
            'category_slug' =>['nullable','string','max:255',Rule::unique('competition_categories', 'category_slug')->ignore($competitionCategory->id),],
            'description' =>['nullable','string',],
            'is_active' => ['nullable'],
        ]);
            $validate['is_active'] = $request->has('is_active');

            $competitionCategory->update($validate);
            return redirect()->route('superadmin.categories.create')->with('success', 'แก้ไขประเภทการแข่งขันสำเร็จ');
            // return redirect()->route('superadmin.categories.edit')->with('success', 'แก้ไขข้อมูลสำเร็จ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionCategory $competitionCategory)
    {
         $competitionCategory->delete();
            return redirect()->route('superadmin.categories.create')->with('success', 'ลบ ประเภทการแข่งขัน สำเร็จ');
    }
}
