<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Concerns\EnsuresSuperAdmin;
use App\Http\Controllers\Controller;
use App\Models\KnowledgeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KnowledgeCategoryController extends Controller
{
    use EnsuresSuperAdmin;

    public function index(): View
    {
        $this->ensureSuperAdmin();

        return view('superadmin.knowledge-page.categories.index', [
            'categories' => KnowledgeCategory::query()->withCount('knowledgeItems')->orderBy('name')->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:knowledge_categories,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:knowledge_categories,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = Str::slug(($data['slug'] ?? null) ?: $data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        KnowledgeCategory::create($data);

        return back()->with('success', 'เพิ่มหมวด E-Book แล้ว');
    }

    public function update(Request $request, KnowledgeCategory $knowledgeCategory): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('knowledge_categories', 'name')->ignore($knowledgeCategory)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('knowledge_categories', 'slug')->ignore($knowledgeCategory)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = Str::slug(($data['slug'] ?? null) ?: $data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $knowledgeCategory->update($data);

        return back()->with('success', 'แก้ไขหมวด E-Book แล้ว');
    }

    public function destroy(KnowledgeCategory $knowledgeCategory): RedirectResponse
    {
        $this->ensureSuperAdmin();
        if ($knowledgeCategory->knowledgeItems()->exists()) {
            throw ValidationException::withMessages(['category' => 'ลบหมวดที่มี E-Book ใช้งานอยู่ไม่ได้']);
        }
        $knowledgeCategory->delete();

        return back()->with('success', 'ลบหมวด E-Book แล้ว');
    }
}
