<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Concerns\EnsuresSuperAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\EbookRequest;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeItem;
use App\Services\KnowledgeFileCleanup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class EbookController extends Controller
{
    use EnsuresSuperAdmin;

    public function index(Request $request): View
    {
        $this->ensureSuperAdmin();
        $query = KnowledgeItem::query()->ebooks()->with('knowledgeCategory');
        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%"));
        }

        return view('superadmin.knowledge-page.books.index', [
            'ebooks' => $query->orderBy('sort_order')->orderByDesc('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->ensureSuperAdmin();

        return view('superadmin.knowledge-page.books.create', ['categories' => $this->categories()]);
    }

    public function store(EbookRequest $request, KnowledgeFileCleanup $cleanup): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $validated = $request->validated();
        $newPaths = [];

        try {
            $newPaths = $this->storeUploads($request, $cleanup);
            $ebook = DB::transaction(fn () => KnowledgeItem::create([
                ...Arr::only($validated, [
                    'title', 'summary', 'content', 'knowledge_category_id', 'external_url',
                    'publication_year', 'volume', 'issue', 'sort_order',
                ]),
                'knowledge_type' => 'ebook',
                'submission_id' => null,
                'created_by' => Auth::id(),
                'category_id' => null,
                'cover_image' => $newPaths['cover_image'] ?? null,
                'attachment_path' => $newPaths['attachment_path'] ?? null,
                'attachment_original_name' => isset($newPaths['attachment_path'])
                    ? $this->safeOriginalName($request->file('attachment')->getClientOriginalName()) : null,
                'status' => 'hidden',
                'published_at' => null,
                'is_featured' => false,
                'sort_order' => $validated['sort_order'] ?? 0,
            ]));
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                $cleanup->delete($path);
            }
            throw $exception;
        }

        return redirect()->route('superadmin.knowledge-page.books.show', $ebook)
            ->with('success', 'เพิ่ม E-Book เรียบร้อยแล้ว');
    }

    public function show(int $ebook): View
    {
        $this->ensureSuperAdmin();
        $item = $this->ebook($ebook);
        Gate::authorize('view', $item);

        return view('superadmin.knowledge-page.books.show', ['ebook' => $item->load('knowledgeCategory')]);
    }

    public function edit(int $ebook): View
    {
        $this->ensureSuperAdmin();
        $item = $this->ebook($ebook);
        Gate::authorize('update', $item);

        return view('superadmin.knowledge-page.books.edit', [
            'ebook' => $item, 'categories' => $this->categories(),
        ]);
    }

    public function update(EbookRequest $request, int $ebook, KnowledgeFileCleanup $cleanup): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $item = $this->ebook($ebook);
        Gate::authorize('update', $item);
        $validated = $request->validated();
        $oldPaths = [$item->cover_image, $item->attachment_path];
        $newPaths = [];

        try {
            $newPaths = $this->storeUploads($request, $cleanup);
            DB::transaction(function () use ($request, $validated, $item, $newPaths): void {
                $data = Arr::only($validated, [
                    'title', 'summary', 'content', 'knowledge_category_id', 'external_url',
                    'publication_year', 'volume', 'issue', 'sort_order',
                ]);
                $data['knowledge_type'] = 'ebook';
                $data['submission_id'] = null;
                $data['category_id'] = null;

                if (isset($newPaths['cover_image'])) {
                    $data['cover_image'] = $newPaths['cover_image'];
                } elseif ($request->boolean('remove_cover_image')) {
                    $data['cover_image'] = null;
                }
                if (isset($newPaths['attachment_path'])) {
                    $data['attachment_path'] = $newPaths['attachment_path'];
                    $data['attachment_original_name'] = $this->safeOriginalName(
                        $request->file('attachment')->getClientOriginalName()
                    );
                } elseif ($request->boolean('remove_attachment')) {
                    $data['attachment_path'] = null;
                    $data['attachment_original_name'] = null;
                }

                $item->update($data);
            });
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                $cleanup->delete($path);
            }
            throw $exception;
        }

        foreach ($oldPaths as $key => $path) {
            $replacement = $key === 0 ? ($newPaths['cover_image'] ?? null) : ($newPaths['attachment_path'] ?? null);
            $removed = $key === 0 ? $request->boolean('remove_cover_image') : $request->boolean('remove_attachment');
            if ($path && ($replacement || $removed)) {
                $cleanup->delete($path);
            }
        }

        return redirect()->route('superadmin.knowledge-page.books.show', $item)
            ->with('success', 'แก้ไข E-Book เรียบร้อยแล้ว');
    }

    public function destroy(Request $request, int $ebook, KnowledgeFileCleanup $cleanup): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $request->validate(['confirm_delete' => ['required', 'accepted']]);
        $item = $this->ebook($ebook);
        Gate::authorize('delete', $item);
        $paths = [$item->cover_image, $item->attachment_path];
        DB::transaction(fn () => $item->delete());
        foreach ($paths as $path) {
            if ($path && ! $cleanup->delete($path)) {
                throw ValidationException::withMessages(['file' => 'ลบข้อมูลแล้วแต่ cleanup ไฟล์ไม่สำเร็จ โปรดตรวจสอบ log']);
            }
        }

        return redirect()->route('superadmin.knowledge-page.books.index')
            ->with('success', 'ลบ E-Book เรียบร้อยแล้ว');
    }

    public function publish(int $ebook): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $item = $this->ebook($ebook);
        Gate::authorize('publish', $item);
        $item->update(['status' => 'published', 'published_at' => now()]);

        return back()->with('success', 'แสดง E-Book แล้ว');
    }

    public function hide(int $ebook): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $item = $this->ebook($ebook);
        Gate::authorize('unpublish', $item);
        $item->update(['status' => 'hidden', 'published_at' => null]);

        return back()->with('success', 'ซ่อน E-Book แล้ว');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);
        $ids = collect($validated['items'])->pluck('id');
        abort_unless(KnowledgeItem::query()->ebooks()->whereIn('id', $ids)->count() === $ids->count(), 404);
        DB::transaction(function () use ($validated): void {
            foreach ($validated['items'] as $row) {
                KnowledgeItem::query()->ebooks()->whereKey($row['id'])->update(['sort_order' => $row['sort_order']]);
            }
        });

        return back()->with('success', 'เรียงลำดับ E-Book แล้ว');
    }

    private function ebook(int $id): KnowledgeItem
    {
        return KnowledgeItem::query()->ebooks()->findOrFail($id);
    }

    private function categories()
    {
        return KnowledgeCategory::query()->where('is_active', true)->orderBy('name')->get();
    }

    private function storeUploads(EbookRequest $request, KnowledgeFileCleanup $cleanup): array
    {
        $paths = [];
        try {
            if ($request->hasFile('cover_image')) {
                $paths['cover_image'] = $this->storeManagedUpload($request->file('cover_image'), 'knowledge-items/covers');
            }
            if ($request->hasFile('attachment')) {
                $paths['attachment_path'] = $this->storeManagedUpload($request->file('attachment'), 'knowledge-items/attachments');
            }
        } catch (Throwable $exception) {
            foreach ($paths as $path) {
                $cleanup->delete($path);
            }

            throw $exception;
        }

        return $paths;
    }

    private function storeManagedUpload(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'local');
        if (! is_string($path) || $path === '' || ! Storage::disk('local')->exists($path)) {
            throw ValidationException::withMessages(['file' => 'จัดเก็บไฟล์ไม่สำเร็จ']);
        }

        return $path;
    }

    private function safeOriginalName(string $name): string
    {
        return mb_substr(trim(preg_replace('/[\x00-\x1F\x7F]+/u', '', basename(str_replace('\\', '/', $name))) ?: 'ebook.pdf'), 0, 255);
    }
}
