@extends('layouts.app')

@section('title', 'จัดการองค์ความรู้ทั้งหมด')

@section('header')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-3">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/><path stroke-linecap="round" d="M8 7h8M8 11h6"/></svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">จัดการองค์ความรู้ทั้งหมด</h1>
            <p class="mt-1 text-sm text-slate-500">จัดการองค์ความรู้และผลงานจากการแข่งขันในหน้าเดียว</p>
        </div>
    </div>

    @can('create', App\Models\KnowledgeItem::class)
        <a href="{{ route('superadmin.km.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            เพิ่มองค์ความรู้
        </a>
    @endcan
</div>
@endsection

@section('content')
@php
    $showManual = request('source') !== 'competition';
    $showCompetition = request('source') !== 'manual';

    $manualItems = $showManual
        ? $knowledgeItems->getCollection()->filter(fn ($item) => is_null($item->submission_id))
        : collect();

    $competitionItems = $showCompetition
        ? $submissions->getCollection()
        : collect();

    if ($showCompetition && request()->filled('status')) {
        $requestedStatus = request('status');

        $competitionItems = $competitionItems->filter(function ($submission) use ($requestedStatus) {
            $kmStatus = $submission->knowledgeItem?->status;

            return match ($requestedStatus) {
                'published' => $kmStatus === 'published',
                'hidden' => $kmStatus === 'hidden',
                'draft' => $kmStatus === null || $kmStatus === 'draft',
                default => true,
            };
        });
    }

    $visibleCount = $manualItems->count() + $competitionItems->count();
@endphp

<div id="km-list" class="space-y-6">

    {{-- FILTER --}}
    <form method="GET" action="{{ route('superadmin.km.index') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/70 px-5 py-3.5">
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M3 5h18M6 12h12M10 19h4"/></svg>
            <div>
                <h2 class="text-sm font-semibold text-slate-700">ค้นหาและกรองข้อมูล</h2>
                <p class="mt-0.5 text-xs text-slate-400">ค้นหาองค์ความรู้และผลงานจากการแข่งขัน</p>
            </div>
        </div>

        <div class="p-5">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <div class="relative md:col-span-2">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อผลงาน รหัส เจ้าของ หรือการแข่งขัน" class="h-11 w-full rounded-xl border-slate-300 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <select name="category_id" class="h-11 rounded-xl border-slate-300 bg-white text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกหมวดหมู่</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>{{ $category->category_name }}</option>
                    @endforeach
                </select>

                <select name="status" class="h-11 rounded-xl border-slate-300 bg-white text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกสถานะ</option>
                    <option value="draft" @selected(request('status') === 'draft')>ยังไม่เผยแพร่</option>
                    <option value="published" @selected(request('status') === 'published')>เผยแพร่แล้ว</option>
                    <option value="hidden" @selected(request('status') === 'hidden')>ซ่อน</option>
                </select>

                <select name="source" class="h-11 rounded-xl border-slate-300 bg-white text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกประเภท</option>
                    <option value="manual" @selected(request('source') === 'manual')>องค์ความรู้</option>
                    <option value="competition" @selected(request('source') === 'competition')>ผลงานการแข่งขัน</option>
                </select>

                <select name="owner" class="h-11 rounded-xl border-slate-300 bg-white text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">ทุกเจ้าของ</option>
                    <option value="unassigned" @selected(request('owner') === 'unassigned')>ไม่มีเจ้าของ</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string)request('owner') === (string)$owner->id)>{{ $owner->username }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4 flex flex-col-reverse gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                <a href="{{ route('superadmin.km.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">ล้างตัวกรอง</a>
                <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    ค้นหา
                </button>
            </div>
        </div>
    </form>

    {{-- RESULT HEADER --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">รายการทั้งหมด</h2>
            <p class="mt-1 text-sm text-slate-500">แสดง {{ number_format($visibleCount) }} รายการในหน้านี้</p>
        </div>

        <div class="flex flex-wrap gap-2 text-xs font-semibold">
            <span class="rounded-full bg-blue-50 px-3 py-1.5 text-blue-700">องค์ความรู้</span>
            <span class="rounded-full bg-violet-50 px-3 py-1.5 text-violet-700">ผลงานการแข่งขัน</span>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">

        {{-- MANUAL KNOWLEDGE --}}
        @foreach($manualItems as $item)
            @php
                $statusConfig = match($item->status) {
                    'published' => ['label' => 'เผยแพร่แล้ว', 'class' => 'bg-emerald-50 text-emerald-700', 'bar' => 'bg-emerald-500'],
                    'hidden' => ['label' => 'ซ่อน', 'class' => 'bg-amber-50 text-amber-700', 'bar' => 'bg-amber-400'],
                    default => ['label' => 'ยังไม่เผยแพร่', 'class' => 'bg-slate-100 text-slate-600', 'bar' => 'bg-slate-200'],
                };
            @endphp

            <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-slate-300 hover:shadow-md">
                <div class="h-1 {{ $statusConfig['bar'] }}"></div>

                <div class="flex flex-1 flex-col p-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z"/></svg>
                            องค์ความรู้
                        </span>

                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>

                        @if($item->is_featured)
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                <svg class="h-3.5 w-3.5 fill-amber-400" viewBox="0 0 24 24"><path d="m12 2.7 2.85 5.78 6.38.93-4.62 4.5 1.09 6.35L12 17.26l-5.7 3 1.09-6.35-4.62-4.5 6.38-.93L12 2.7Z"/></svg>
                                Featured
                            </span>
                        @endif
                    </div>

                    <h3 class="mt-4 text-lg font-bold leading-7 text-slate-900 group-hover:text-blue-700">{{ $item->title }}</h3>

                    <div class="mt-4 grid gap-3 rounded-xl bg-slate-50/80 p-4 text-sm sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium text-slate-400">เจ้าของ</p>
                            <p class="mt-1 truncate font-medium text-slate-700">{{ $item->creator?->username ?? 'ไม่มีเจ้าของ' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-slate-400">หมวดหมู่</p>
                            <p class="mt-1 truncate font-medium text-slate-700">{{ $item->category?->category_name ?? '-' }}</p>
                        </div>

                        @if($item->published_at)
                            <div class="sm:col-span-2">
                                <p class="text-xs font-medium text-slate-400">เผยแพร่เมื่อ</p>
                                <p class="mt-1 font-medium text-slate-700">{{ $item->published_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                    </div>

                    @if($item->summary)
                        <p class="mt-4 line-clamp-2 text-sm leading-6 text-slate-600">{{ $item->summary }}</p>
                    @endif

                    <div class="mt-auto flex flex-wrap items-center gap-2 border-t border-slate-100 pt-5">
                        @can('view', $item)
                            <a href="{{ route('superadmin.km.show', $item) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">ดู</a>
                        @endcan

                        @can('update', $item)
                            <a href="{{ route('superadmin.km.edit', $item) }}" class="inline-flex h-9 items-center rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">แก้ไข</a>
                        @endcan

                        @if($item->status === 'published')
                            @can('unpublish', $item)
                                <form method="POST" action="{{ route('superadmin.km.unpublish', $item) }}">
                                    @csrf @method('DELETE')
                                    <button class="h-9 rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">ถอนเผยแพร่</button>
                                </form>
                            @endcan
                        @else
                            @can('publish', $item)
                                <form method="POST" action="{{ route('superadmin.km.publish', $item) }}">
                                    @csrf
                                    <button class="h-9 rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">เผยแพร่</button>
                                </form>
                            @endcan
                        @endif

                        @can('feature', $item)
                            <form method="POST" action="{{ route($item->is_featured ? 'superadmin.km.unfeature' : 'superadmin.km.feature', $item) }}">
                                @csrf
                                @if($item->is_featured) @method('DELETE') @endif
                                <button class="h-9 rounded-lg border border-amber-200 bg-white px-3 text-xs font-semibold text-amber-700 transition hover:bg-amber-50">{{ $item->is_featured ? 'ถอน Featured' : 'ตั้ง Featured' }}</button>
                            </form>
                        @endcan

                        @can('delete', $item)
                            <form method="POST" action="{{ route('superadmin.km.destroy', $item) }}" class="sm:ml-auto" onsubmit="return confirm('ยืนยันการลบองค์ความรู้นี้?')">
                                @csrf @method('DELETE')
                                <button class="h-9 rounded-lg border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 transition hover:bg-red-50">ลบ</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </article>
        @endforeach

        {{-- COMPETITION SUBMISSIONS --}}
        @foreach($competitionItems as $submission)
            @php
                $knowledgeItem = $submission->knowledgeItem;
                $isPublished = $knowledgeItem?->status === 'published';

                $statusConfig = match($knowledgeItem?->status) {
                    'published' => ['label' => 'เผยแพร่แล้ว', 'class' => 'bg-emerald-50 text-emerald-700'],
                    'hidden' => ['label' => 'ซ่อน', 'class' => 'bg-amber-50 text-amber-700'],
                    'draft' => ['label' => 'ยังไม่เผยแพร่', 'class' => 'bg-slate-100 text-slate-600'],
                    default => ['label' => 'ยังไม่เผยแพร่', 'class' => 'bg-slate-100 text-slate-600'],
                };

                $primaryFile = $submission->files->firstWhere('is_primary', true) ?? $submission->files->first();
                $hasImage = $primaryFile && filled($primaryFile->mime_type) && str_starts_with((string)$primaryFile->mime_type, 'image/');
            @endphp

            <article id="km-submission-{{ $submission->id }}" class="group overflow-hidden rounded-2xl border border-violet-200 bg-white shadow-sm transition hover:border-violet-300 hover:shadow-md">
                <div class="flex h-full flex-col sm:flex-row">

                    <div class="relative h-48 shrink-0 overflow-hidden bg-slate-100 sm:h-auto sm:w-48">
                        @if($hasImage)
                            <img src="{{ $primaryFile->file_url }}" alt="{{ $submission->project_title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]">
                        @else
                            <div class="flex h-full min-h-48 items-center justify-center bg-slate-50 text-slate-300">
                                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5v-16ZM20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5v-16Z"/></svg>
                            </div>
                        @endif

                        <span class="absolute left-3 top-3 rounded-full bg-violet-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm">ผลงานการแข่งขัน</span>
                    </div>

                    <div class="flex min-w-0 flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-violet-600">{{ $submission->competition?->title ?? 'ไม่พบการแข่งขัน' }}</p>
                                <h3 class="mt-2 line-clamp-2 text-lg font-bold leading-snug text-slate-900">{{ $submission->project_title }}</h3>
                                <p class="mt-1 text-xs text-slate-400">รหัสผลงาน: {{ $submission->submission_code }}</p>
                            </div>

                            <div class="shrink-0 rounded-xl bg-blue-50 px-3 py-2 text-center">
                                <p class="text-[10px] font-semibold text-blue-500">คะแนน</p>
                                <p class="mt-0.5 text-lg font-bold text-blue-700">{{ number_format((float)$submission->final_score, 2) }}</p>
                            </div>
                        </div>

                        @if($submission->project_description)
                            <p class="mt-4 line-clamp-2 text-sm leading-6 text-slate-600">{{ $submission->project_description }}</p>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>

                            @if($knowledgeItem?->is_featured)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                    <svg class="h-3.5 w-3.5 fill-amber-400" viewBox="0 0 24 24"><path d="m12 2.7 2.85 5.78 6.38.93-4.62 4.5 1.09 6.35L12 17.26l-5.7 3 1.09-6.35-4.62-4.5 6.38-.93L12 2.7Z"/></svg>
                                    Featured
                                </span>
                            @endif
                        </div>

                        <div class="mt-auto flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                            @if($knowledgeItem)
                                @can('view', $knowledgeItem)
                                    <a href="{{ route('superadmin.km.show', $knowledgeItem) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">ดู KM</a>
                                @endcan
                            @endif

                            @if($isPublished)
                                @if(Route::has('superadmin.submissions.km.unpublish'))
                                    <x-ajax-form :action="route('superadmin.submissions.km.unpublish', $submission)" method="DELETE" confirm="ยืนยันถอนผลงานนี้ออกจาก KM หรือไม่?" success="ถอนผลงานออกจาก KM เรียบร้อยแล้ว" :target="'#km-submission-' . $submission->id">
                                        <button type="submit" class="h-9 rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">ถอนเผยแพร่</button>
                                    </x-ajax-form>
                                @else
                                    <button type="button" disabled class="h-9 cursor-not-allowed rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-semibold text-amber-400">ถอนเผยแพร่</button>
                                @endif
                            @else
                                @if(Route::has('superadmin.submissions.km.publish'))
                                    <x-ajax-form :action="route('superadmin.submissions.km.publish', $submission)" method="POST" confirm="ยืนยันเผยแพร่ผลงานนี้เข้าสู่ KM หรือไม่?" success="เผยแพร่ผลงานสู่ KM เรียบร้อยแล้ว" :target="'#km-submission-' . $submission->id">
                                        <button type="submit" class="h-9 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white transition hover:bg-emerald-700">เผยแพร่สู่ KM</button>
                                    </x-ajax-form>
                                @else
                                    <button type="button" disabled class="h-9 cursor-not-allowed rounded-lg bg-emerald-300 px-3 text-xs font-semibold text-white">เผยแพร่สู่ KM</button>
                                @endif
                            @endif

                            @if($knowledgeItem)
                                @can('feature', $knowledgeItem)
                                    <form method="POST" action="{{ route($knowledgeItem->is_featured ? 'superadmin.km.unfeature' : 'superadmin.km.feature', $knowledgeItem) }}">
                                        @csrf
                                        @if($knowledgeItem->is_featured) @method('DELETE') @endif
                                        <button class="h-9 rounded-lg border border-amber-200 bg-white px-3 text-xs font-semibold text-amber-700 transition hover:bg-amber-50">{{ $knowledgeItem->is_featured ? 'ถอน Featured' : 'ตั้ง Featured' }}</button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @endforeach

        @if($visibleCount === 0)
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center xl:col-span-2">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-700">ไม่พบรายการ</h3>
                <p class="mt-1 text-sm text-slate-400">ไม่พบองค์ความรู้หรือผลงานการแข่งขันที่ตรงกับเงื่อนไข</p>

                @if(request('search') || request('category_id') || request('status') || request('source') || request('owner'))
                    <a href="{{ route('superadmin.km.index') }}" class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">ล้างตัวกรอง</a>
                @endif
            </div>
        @endif
    </div>

    {{-- PAGINATION --}}
    <div class="space-y-3">
        @if($showManual && $knowledgeItems->hasPages())
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">{{ $knowledgeItems->links() }}</div>
        @endif

        @if($showCompetition && $submissions->hasPages())
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">{{ $submissions->links() }}</div>
        @endif
    </div>
</div>
@endsection