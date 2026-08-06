<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionCategory;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'category_name' => ['required','string','max:255','unique:competition_categories,category_name'],
            'category_slug' => ['nullable', 'string', 'max:255', 'unique:competition_categories,category_slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
            $slugSource = filled($validated['category_slug'] ?? null)
                ? $validated['category_slug']
                : $validated['category_name'];

            $validated['category_slug'] = $this->generateUniqueSlug($slugSource);
            $validated['is_active'] = $request->boolean('is_active');

            CompetitionCategory::create($validated);
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
    public function update(
        Request $request,
        CompetitionCategory $competitionCategory
    ) {
        $validated = $request->validate([
            'category_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('competition_categories', 'category_name')
                    ->ignore($competitionCategory->id),
            ],
            'category_slug' => [
                'nullable',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $slugSource = filled($validated['category_slug'] ?? null)
            ? $validated['category_slug']
            : $validated['category_name'];

        $validated['category_slug'] = $this->generateUniqueSlug(
            $slugSource,
            $competitionCategory->id
        );

        $validated['is_active'] = $request->boolean('is_active');

        $competitionCategory->update($validated);

        return redirect()
            ->route('superadmin.categories.create')
            ->with('success', 'แก้ไขประเภทการแข่งขันสำเร็จ');
    }
    /**
     * สร้าง slug ที่รองรับภาษาไทยและไม่ซ้ำในฐานข้อมูล
     */
    private function generateUniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $baseSlug = mb_strtolower(trim($value));

        // เก็บตัวอักษร Unicode เช่น ภาษาไทย อังกฤษ และตัวเลข
        $baseSlug = preg_replace(
            '/[^\p{L}\p{N}]+/u',
            '-',
            $baseSlug
        ) ?? '';

        $baseSlug = trim($baseSlug, '-');

        if ($baseSlug === '') {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $number = 2;

        while (
            CompetitionCategory::query()
                ->where('category_slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$number}";
            $number++;
        }

        return $slug;
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
