<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KnowledgeItemRequest;
use App\Models\CompetitionCategory;
use App\Models\KnowledgeItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class KnowledgeItemController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', KnowledgeItem::class);

        $query = KnowledgeItem::query()->with([
            'creator:id,username',
            'category:id,category_name,is_active',
            'submission.competition:id,title',
        ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhereHas('creator', fn ($query) =>
                        $query->where('username', 'like', "%{$search}%"))
                    ->orWhereHas('submission.competition', fn ($query) =>
                        $query->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('category_id')) {
            $categoryId = filter_var($request->input('category_id'), FILTER_VALIDATE_INT);
            if ($categoryId !== false && CompetitionCategory::whereKey($categoryId)->exists()) {
                $query->where('category_id', $categoryId);
            }
        }

        if (in_array($request->status, ['draft', 'published', 'hidden'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->source === 'manual') {
            $query->whereNull('submission_id');
        } elseif ($request->source === 'competition') {
            $query->whereNotNull('submission_id');
        }

        if ($request->owner === 'unassigned') {
            $query->whereNull('created_by');
        } elseif ($request->filled('owner')) {
            $ownerId = filter_var($request->input('owner'), FILTER_VALIDATE_INT);
            if ($ownerId !== false && User::whereKey($ownerId)->exists()) {
                $query->where('created_by', $ownerId);
            }
        }

        $knowledgeItems = $query->latest()->paginate(15)->withQueryString();
        $owners = User::query()
            ->whereHas('knowledgeItems')
            ->orderBy('username')
            ->get(['id', 'username']);

        return view('superadmin.km.index', [
            'knowledgeItems' => $knowledgeItems,
            'categories' => $this->categories(),
            'owners' => $owners,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', KnowledgeItem::class);
        return view('superadmin.km.create', ['categories' => $this->categories()]);
    }

    public function store(KnowledgeItemRequest $request): RedirectResponse
    {
        Gate::authorize('create', KnowledgeItem::class);
        $validated = $request->validated();
        $storedPaths = [];

        try {
            if ($request->hasFile('cover_image')) {
                $storedPaths['cover_image'] = $this->storeManagedUpload(
                    $request->file('cover_image'),
                    'knowledge-items/covers',
                    'cover_image'
                );
            }
            if ($request->hasFile('attachment')) {
                $storedPaths['attachment_path'] = $this->storeManagedUpload(
                    $request->file('attachment'),
                    'knowledge-items/attachments',
                    'attachment'
                );
            }

            $knowledgeItem = DB::transaction(fn () => KnowledgeItem::create([
                'submission_id' => null,
                'created_by' => Auth::id(),
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'summary' => $validated['summary'] ?? null,
                'content' => $validated['content'] ?? null,
                'cover_image' => $storedPaths['cover_image'] ?? null,
                'attachment_path' => $storedPaths['attachment_path'] ?? null,
                'attachment_original_name' => isset($storedPaths['attachment_path'])
                    ? $this->safeOriginalName($request->file('attachment')->getClientOriginalName())
                    : null,
                'status' => 'draft',
                'published_at' => null,
                'is_featured' => false,
            ]));
        } catch (Throwable $exception) {
            $this->deleteManagedFiles(array_values($storedPaths));
            throw $exception;
        }

        return redirect()->route('superadmin.km.show', $knowledgeItem)
            ->with('success', 'เพิ่มองค์ความรู้เรียบร้อยแล้ว');
    }

    public function show(KnowledgeItem $knowledgeItem): View
    {
        Gate::authorize('view', $knowledgeItem);
        $knowledgeItem->load(['creator:id,username', 'category:id,category_name,is_active', 'submission.competition:id,title']);
        return view('superadmin.km.show', compact('knowledgeItem'));
    }

    public function edit(KnowledgeItem $knowledgeItem): View
    {
        Gate::authorize('update', $knowledgeItem);
        $knowledgeItem->load(['creator:id,username', 'category:id,category_name,is_active', 'submission.competition:id,title']);
        return view('superadmin.km.edit', ['knowledgeItem' => $knowledgeItem, 'categories' => $this->categories()]);
    }

    public function update(KnowledgeItemRequest $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('update', $knowledgeItem);
        $validated = $request->validated();
        $oldCover = $knowledgeItem->cover_image;
        $oldAttachment = $knowledgeItem->attachment_path;
        $newPaths = [];

        try {
            if ($request->hasFile('cover_image')) {
                $newPaths['cover_image'] = $this->storeManagedUpload(
                    $request->file('cover_image'),
                    'knowledge-items/covers',
                    'cover_image'
                );
            }
            if ($request->hasFile('attachment')) {
                $newPaths['attachment_path'] = $this->storeManagedUpload(
                    $request->file('attachment'),
                    'knowledge-items/attachments',
                    'attachment'
                );
            }

            DB::transaction(function () use ($request, $validated, $knowledgeItem, $newPaths): void {
                $changes = [
                    'category_id' => $validated['category_id'],
                    'title' => $validated['title'],
                    'summary' => $validated['summary'] ?? null,
                    'content' => $validated['content'] ?? null,
                ];
                if (isset($newPaths['cover_image'])) {
                    $changes['cover_image'] = $newPaths['cover_image'];
                } elseif ($request->boolean('remove_cover_image')) {
                    $changes['cover_image'] = null;
                }
                if (isset($newPaths['attachment_path'])) {
                    $changes['attachment_path'] = $newPaths['attachment_path'];
                    $changes['attachment_original_name'] = $this->safeOriginalName($request->file('attachment')->getClientOriginalName());
                } elseif ($request->boolean('remove_attachment')) {
                    $changes['attachment_path'] = null;
                    $changes['attachment_original_name'] = null;
                }
                $knowledgeItem->update($changes);
            });
        } catch (Throwable $exception) {
            $this->deleteManagedFiles(array_values($newPaths));
            throw $exception;
        }

        if (isset($newPaths['cover_image']) || ($request->boolean('remove_cover_image') && ! isset($newPaths['cover_image']))) {
            $this->deleteManagedFile($oldCover);
        }
        if (isset($newPaths['attachment_path']) || ($request->boolean('remove_attachment') && ! isset($newPaths['attachment_path']))) {
            $this->deleteManagedFile($oldAttachment);
        }

        return redirect()->route('superadmin.km.show', $knowledgeItem)->with('success', 'แก้ไของค์ความรู้เรียบร้อยแล้ว');
    }

    public function destroy(KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('delete', $knowledgeItem);
        $paths = [$knowledgeItem->cover_image, $knowledgeItem->attachment_path];
        DB::transaction(fn () => $knowledgeItem->delete());
        $this->deleteManagedFiles($paths);
        return redirect()->route('superadmin.km.index')->with('success', 'ลบองค์ความรู้เรียบร้อยแล้ว');
    }

    public function publish(KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('publish', $knowledgeItem);
        $knowledgeItem->update(['status' => 'published', 'published_at' => now()]);
        return back()->with('success', 'เผยแพร่องค์ความรู้เรียบร้อยแล้ว');
    }

    public function unpublish(KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('unpublish', $knowledgeItem);
        $knowledgeItem->update(['status' => 'draft', 'published_at' => null]);
        return back()->with('success', 'ถอนเผยแพร่องค์ความรู้เรียบร้อยแล้ว');
    }

    public function feature(KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('feature', $knowledgeItem);
        $knowledgeItem->update(['is_featured' => true]);
        return back()->with('success', 'ตั้งเป็นรายการแนะนำแล้ว');
    }

    public function unfeature(KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('feature', $knowledgeItem);
        $knowledgeItem->update(['is_featured' => false]);
        return back()->with('success', 'ยกเลิกรายการแนะนำแล้ว');
    }

    private function categories()
    {
        return CompetitionCategory::query()->orderBy('category_name')->get(['id', 'category_name', 'is_active']);
    }

    private function safeOriginalName(string $name): string
    {
        $name = str_replace(["\0", '\\'], ['', '/'], $name);
        $name = basename($name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        $name = mb_substr(trim($name), 0, 255);
        return $name !== '' ? $name : 'attachment';
    }

    private function storeManagedUpload(
        UploadedFile $file,
        string $directory,
        string $attribute
    ): string {
        try {
            $path = $file->store($directory, 'local');
        } catch (Throwable) {
            throw ValidationException::withMessages([
                $attribute => [$this->storageFailureMessage($attribute)],
            ]);
        }

        if (! is_string($path) || trim($path) === '') {
            throw ValidationException::withMessages([
                $attribute => [$this->storageFailureMessage($attribute)],
            ]);
        }

        return $path;
    }

    private function storageFailureMessage(string $attribute): string
    {
        return $attribute === 'cover_image'
            ? 'ไม่สามารถบันทึกรูปปกได้ กรุณาลองใหม่อีกครั้ง'
            : 'ไม่สามารถบันทึกไฟล์แนบได้ กรุณาลองใหม่อีกครั้ง';
    }

    private function deleteManagedFiles(array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteManagedFile($path);
        }
    }

    private function deleteManagedFile(?string $path): void
    {
        if (! $path) {
            return;
        }
        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        if (in_array('..', explode('/', $normalized), true)) {
            return;
        }
        if (! str_starts_with($normalized, 'knowledge-items/covers/')
            && ! str_starts_with($normalized, 'knowledge-items/attachments/')) {
            return;
        }
        Storage::disk('local')->delete($normalized);
    }
}
