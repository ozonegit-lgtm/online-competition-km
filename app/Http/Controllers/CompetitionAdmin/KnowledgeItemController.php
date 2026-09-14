<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KnowledgeItemRequest;
use App\Models\CompetitionCategory;
use App\Models\KnowledgeItem;
use App\Models\Submission;
use App\Services\KnowledgeFileCleanup;
use App\Services\LegacyKnowledgeAttachments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class KnowledgeItemController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', KnowledgeItem::class);

        // องค์ความรู้ที่เพิ่มเองเท่านั้น
        $knowledgeQuery = KnowledgeItem::query()
            ->legacy()
            ->where('created_by', Auth::id())
            ->whereNull('submission_id')
            ->with([
                'creator:id,username',
                'category:id,category_name',
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $knowledgeQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $knowledgeQuery->where('category_id', $request->integer('category_id'));
        }

        if (in_array($request->status, ['draft', 'published', 'hidden'], true)) {
            $knowledgeQuery->where('status', $request->status);
        }

        if ($request->source === 'competition') {
            $knowledgeQuery->whereRaw('1 = 0');
        }

        $knowledgeItems = $knowledgeQuery
            ->latest()
            ->paginate(15, ['*'], 'page')
            ->withQueryString();

        // ผลงานจากการแข่งขันที่ Admin คนนี้เป็นเจ้าของ
        $submissionQuery = Submission::query()
            ->where('status', '!=', 'disqualified')
            ->whereHas('competition', fn ($query) => $query->where('created_by', Auth::id()))
            ->whereHas('competition.judgingSession', fn ($query) => $query->whereIn('status', ['ended', 'closed']))
            ->with([
                'competition',
                'knowledgeItem',
                'files',
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $submissionQuery->where(function ($query) use ($search) {
                $query->where('project_title', 'like', "%{$search}%")
                    ->orWhere('submission_code', 'like', "%{$search}%")
                    ->orWhereHas('competition', fn ($query) => $query->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->integer('category_id');

            $submissionQuery->whereHas('competition', fn ($query) => $query->where('category_id', $categoryId));
        }

        if ($request->status === 'published') {
            $submissionQuery->whereHas('knowledgeItem', fn ($query) => $query->where('status', 'published'));
        } elseif ($request->status === 'hidden') {
            $submissionQuery->whereHas('knowledgeItem', fn ($query) => $query->where('status', 'hidden'));
        } elseif ($request->status === 'draft') {
            $submissionQuery->where(function ($query) {
                $query->whereDoesntHave('knowledgeItem')
                    ->orWhereHas('knowledgeItem', fn ($query) => $query->where('status', 'draft'));
            });
        }

        if ($request->source === 'manual') {
            $submissionQuery->whereRaw('1 = 0');
        }

        $submissions = $submissionQuery
            ->orderByDesc('final_score')
            ->paginate(15, ['*'], 'submission_page')
            ->withQueryString();

        return view('competition-admin.km.index', [
            'knowledgeItems' => $knowledgeItems,
            'submissions' => $submissions,
            'categories' => $this->activeCategories(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', KnowledgeItem::class);

        return view('competition-admin.km.create', [
            'categories' => $this->activeCategories(),
        ]);
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

            $knowledgeItem = DB::transaction(function () use ($request, $validated, $storedPaths) {
                return KnowledgeItem::create([
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
                ]);
            });
        } catch (Throwable $exception) {
            $this->deleteManagedFiles(array_values($storedPaths));
            throw $exception;
        }

        return redirect()
            ->route('competition-admin.km.show', $knowledgeItem)
            ->with('success', 'เพิ่มองค์ความรู้เรียบร้อยแล้ว');
    }

    public function show(KnowledgeItem $knowledgeItem): View
    {
        Gate::authorize('view', $knowledgeItem);

        $knowledgeItem->load([
            'creator:id,username',
            'category:id,category_name',
            'submission.competition:id,title',
        ]);

        return view('competition-admin.km.show', compact('knowledgeItem'));
    }

    public function edit(KnowledgeItem $knowledgeItem): View
    {
        Gate::authorize('update', $knowledgeItem);

        $knowledgeItem->load([
            'category:id,category_name',
            'submission.competition:id,title',
        ]);

        return view('competition-admin.km.edit', [
            'knowledgeItem' => $knowledgeItem,
            'categories' => $this->activeCategories(),
        ]);
    }

    public function update(KnowledgeItemRequest $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('update', $knowledgeItem);
        app(LegacyKnowledgeAttachments::class)->ensureMigrated($knowledgeItem->id);

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
                    $changes['attachment_original_name'] = $this->safeOriginalName(
                        $request->file('attachment')->getClientOriginalName()
                    );
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

        if (
            isset($newPaths['cover_image'])
            || ($request->boolean('remove_cover_image') && ! isset($newPaths['cover_image']))
        ) {
            $this->deleteManagedFile($oldCover);
        }

        if (
            isset($newPaths['attachment_path'])
            || ($request->boolean('remove_attachment') && ! isset($newPaths['attachment_path']))
        ) {
            $this->deleteManagedFile($oldAttachment);
        }

        return redirect()
            ->route('competition-admin.km.show', $knowledgeItem)
            ->with('success', 'แก้ไของค์ความรู้เรียบร้อยแล้ว');
    }

    public function destroy(Request $request, KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('delete', $knowledgeItem);
        app(LegacyKnowledgeAttachments::class)->ensureMigrated($knowledgeItem->id);

        $cover = $knowledgeItem->cover_image;
        $attachment = $knowledgeItem->attachment_path;

        DB::transaction(fn () => $knowledgeItem->delete());

        $this->deleteManagedFiles([$cover, $attachment]);

        $index = route('competition-admin.km.index');
        $previous = url()->previous();
        $destination = $request->ajax() && strtok($previous, '?') === $index
            ? $previous
            : $index;

        return redirect()
            ->to($destination)
            ->with('success', 'ลบองค์ความรู้เรียบร้อยแล้ว');
    }

    public function publish(KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('publish', $knowledgeItem);

        $knowledgeItem->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'เผยแพร่องค์ความรู้เรียบร้อยแล้ว');
    }

    public function unpublish(KnowledgeItem $knowledgeItem): RedirectResponse
    {
        Gate::authorize('unpublish', $knowledgeItem);

        $knowledgeItem->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return back()->with('success', 'ถอนเผยแพร่องค์ความรู้เรียบร้อยแล้ว');
    }

    private function activeCategories()
    {
        return CompetitionCategory::query()
            ->where('is_active', true)
            ->orderBy('category_name')
            ->get(['id', 'category_name']);
    }

    private function safeOriginalName(string $name): string
    {
        $name = str_replace(["\0", '\\'], ['', '/'], $name);
        $name = basename($name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        $name = trim($name);
        $name = mb_substr($name, 0, 255);

        return $name !== '' ? $name : 'attachment';
    }

    private function storeManagedUpload(UploadedFile $file, string $directory, string $attribute): string
    {
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
        app(KnowledgeFileCleanup::class)->delete($path);
    }
}
