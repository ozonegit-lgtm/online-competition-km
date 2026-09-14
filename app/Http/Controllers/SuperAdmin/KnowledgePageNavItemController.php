<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Concerns\EnsuresSuperAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\KnowledgePageNavItemRequest;
use App\Models\KnowledgePageNavItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KnowledgePageNavItemController extends Controller
{
    use EnsuresSuperAdmin;

    public function index(): View
    {
        $this->ensureSuperAdmin();

        return view('superadmin.knowledge-page.nav-items.index', [
            'items' => KnowledgePageNavItem::query()->orderBy('placement')->orderBy('sort_order')->orderBy('id')->paginate(30),
        ]);
    }

    public function store(KnowledgePageNavItemRequest $request): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $data = $request->validated();
        $data['sort_order'] ??= 0;
        $data['is_visible'] = $request->boolean('is_visible', true);
        KnowledgePageNavItem::create($data);

        return back()->with('success', 'เพิ่มลิงก์แล้ว');
    }

    public function update(KnowledgePageNavItemRequest $request, KnowledgePageNavItem $navItem): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $data = $request->validated();
        $data['is_visible'] = $request->boolean('is_visible');
        $navItem->update($data);

        return back()->with('success', 'แก้ไขลิงก์แล้ว');
    }

    public function destroy(KnowledgePageNavItem $navItem): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $navItem->delete();

        return back()->with('success', 'ลบลิงก์แล้ว');
    }

    public function visibility(Request $request, KnowledgePageNavItem $navItem): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate(['is_visible' => ['required', 'boolean']]);
        $navItem->update($validated);

        return back()->with('success', 'ปรับการแสดงผลลิงก์แล้ว');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:knowledge_page_nav_items,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);
        DB::transaction(function () use ($validated): void {
            foreach ($validated['items'] as $row) {
                KnowledgePageNavItem::query()->whereKey($row['id'])->update(['sort_order' => $row['sort_order']]);
            }
        });

        return back()->with('success', 'เรียงลำดับลิงก์แล้ว');
    }
}
