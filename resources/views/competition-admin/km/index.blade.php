@extends('layouts.app')

@section('title', 'จัดการองค์ความรู้')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-slate-900 text-xl font-bold">จัดการองค์ความรู้</h1>
        <p class="mt-1 text-slate-500 text-xs">จัดการองค์ความรู้และผลงานจากการแข่งขันในหน้าเดียว</p>
    </div>

    @can('create', App\Models\KnowledgeItem::class)
        <a href="{{ route('competition-admin.km.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 h-9 px-3">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            เพิ่มองค์ความรู้
        </a>
    @endcan
</div>
@endsection

@section('content')
@php
    $hasKnowledgeItems = $knowledgeItems->count() > 0;
    $hasSubmissions = $submissions->count() > 0;
    $visibleCount = $knowledgeItems->count() + $submissions->count();
@endphp

<div id="km-list" class="space-y-4">

    {{-- FILTER --}}
    <form method="GET" action="{{ route('competition-admin.km.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <div class="relative xl:col-span-2">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"/>
                    <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                </svg>
                <input name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อผลงาน รหัสผลงาน หรือการแข่งขัน" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 border py-2 h-10 px-3 pl-10 pr-4">
            </div>

            <select name="category_id" class="rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 border py-2 h-10 px-3">
                <option value="">ทุกหมวดหมู่</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>{{ $category->category_name }}</option>
                @endforeach
            </select>

            <select name="status" class="rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 border py-2 h-10 px-3">
                <option value="">ทุกสถานะ</option>
                <option value="draft" @selected(request('status') === 'draft')>ยังไม่เผยแพร่</option>
                <option value="published" @selected(request('status') === 'published')>เผยแพร่แล้ว</option>
                <option value="hidden" @selected(request('status') === 'hidden')>ซ่อน</option>
            </select>

            <select name="source" class="rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 border py-2 h-10 px-3">
                <option value="">ทุกประเภท</option>
                <option value="manual" @selected(request('source') === 'manual')>องค์ความรู้</option>
                <option value="competition" @selected(request('source') === 'competition')>ผลงานการแข่งขัน</option>
            </select>
        </div>

        <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-4">
            <a href="{{ route('competition-admin.km.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-600 transition hover:bg-slate-50 h-9 px-3">ล้างตัวกรอง</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 text-sm font-semibold text-white transition hover:bg-slate-800 h-9 px-3">ค้นหา</button>
        </div>
    </form>

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-slate-900 text-base font-semibold">รายการทั้งหมด</h2>
            <p class="mt-1 text-slate-500 text-xs">
                องค์ความรู้และผลงานจากการแข่งขันที่ตัดสินเสร็จแล้ว ·
                <span id="km-total">{{ number_format($visibleCount) }} รายการ</span>
            </p>
        </div>

        <div class="flex flex-wrap gap-2 text-xs font-semibold">
            <span class="rounded-full bg-blue-50 text-blue-700 text-xs px-2.5 py-1">องค์ความรู้</span>
            <span class="rounded-full bg-violet-50 text-violet-700 text-xs px-2.5 py-1">ผลงานการแข่งขัน</span>
        </div>
    </div>

    {{-- CARDS --}}
    <div class="grid gap-4 xl:grid-cols-2">

        {{-- MANUAL KNOWLEDGE --}}
        @foreach($knowledgeItems as $item)
            @php
                $statusConfig = match($item->status) {
                    'published' => ['label' => 'เผยแพร่แล้ว', 'class' => 'bg-emerald-50 text-emerald-700'],
                    'hidden' => ['label' => 'ซ่อน', 'class' => 'bg-slate-100 text-slate-600'],
                    default => ['label' => 'ยังไม่เผยแพร่', 'class' => 'bg-amber-50 text-amber-700'],
                };
            @endphp

            <article class="flex h-full flex-col rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 font-semibold text-blue-700 text-xs px-2.5 py-1">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z"/>
                                </svg>
                                องค์ความรู้
                            </span>

                            <span class="rounded-full font-semibold {{ $statusConfig['class'] }} text-xs px-2.5 py-1">{{ $statusConfig['label'] }}</span>
                        </div>

                        <h3 class="mt-3 line-clamp-2 text-slate-900 text-base font-semibold">{{ $item->title }}</h3>
                        <p class="mt-1 text-slate-500 text-xs">{{ $item->category?->category_name ?? 'ไม่ระบุหมวดหมู่' }}</p>
                    </div>

                    @if($item->published_at)
                        <time class="shrink-0 text-xs text-slate-400">{{ $item->published_at->format('d/m/Y H:i') }}</time>
                    @endif
                </div>

                @if($item->summary)
                    <p class="mt-4 line-clamp-2 text-sm leading-6 text-slate-600">{{ $item->summary }}</p>
                @endif

                <div class="mt-auto flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                    @can('view', $item)
                        <a href="{{ route('competition-admin.km.show', $item) }}" class="rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-50 inline-flex items-center justify-center h-9 px-3">ดูรายละเอียด</a>
                    @endcan

                    @can('update', $item)
                        <a href="{{ route('competition-admin.km.edit', $item) }}" class="rounded-lg border border-blue-200 bg-blue-50 text-sm font-semibold text-blue-700 transition hover:bg-blue-100 inline-flex items-center justify-center h-9 px-3">แก้ไข</a>
                    @endcan

                    @if($item->status === 'published')
                        @can('unpublish', $item)
                            <x-ajax-form target="#km-list, #km-total" :action="route('competition-admin.km.unpublish', $item)" method="DELETE" confirm="ยืนยันถอนเผยแพร่รายการนี้?" success="ถอนเผยแพร่เรียบร้อย">
                                <button type="submit" class="rounded-lg bg-amber-500 text-sm font-semibold text-white transition hover:bg-amber-600 inline-flex items-center justify-center h-9 px-3">ถอนเผยแพร่</button>
                            </x-ajax-form>
                        @endcan
                    @else
                        @can('publish', $item)
                            <x-ajax-form target="#km-list, #km-total" :action="route('competition-admin.km.publish', $item)" method="POST" confirm="ยืนยันเผยแพร่รายการนี้?" success="เผยแพร่เรียบร้อย">
                                <button type="submit" class="rounded-lg bg-emerald-600 text-sm font-semibold text-white transition hover:bg-emerald-700 inline-flex items-center justify-center h-9 px-3">เผยแพร่</button>
                            </x-ajax-form>
                        @endcan
                    @endif

                    @can('delete', $item)
                        <x-ajax-form target="#km-list, #km-total" :action="route('competition-admin.km.destroy', $item)" method="DELETE" confirm="ยืนยันลบองค์ความรู้นี้?" success="ลบเรียบร้อย">
                            <button type="submit" class="rounded-lg border border-red-200 bg-white text-sm font-semibold text-red-600 transition hover:bg-red-50 inline-flex items-center justify-center h-9 px-3">ลบ</button>
                        </x-ajax-form>
                    @endcan
                </div>
            </article>
        @endforeach

        {{-- COMPETITION SUBMISSIONS --}}
        @foreach($submissions as $submission)
            @php
                $knowledgeItem = $submission->knowledgeItem;
                $isPublished = $knowledgeItem?->status === 'published';

                $statusConfig = match($knowledgeItem?->status) {
                    'published' => ['label' => 'เผยแพร่แล้ว', 'class' => 'bg-emerald-50 text-emerald-700'],
                    'hidden' => ['label' => 'ซ่อน', 'class' => 'bg-slate-100 text-slate-600'],
                    default => ['label' => 'ยังไม่เผยแพร่', 'class' => 'bg-amber-50 text-amber-700'],
                };

                $primaryFile = $submission->files->firstWhere('is_primary', true) ?? $submission->files->first();
                $hasImage = $primaryFile && filled($primaryFile->mime_type) && str_starts_with((string)$primaryFile->mime_type, 'image/');
            @endphp

            <article id="km-submission-{{ $submission->id }}" class="overflow-hidden rounded-xl border border-violet-200 bg-white shadow-sm transition hover:border-violet-300 hover:shadow-sm">
                <div class="flex h-full flex-col sm:flex-row">
                    <div class="relative h-44 shrink-0 overflow-hidden bg-slate-100 sm:h-auto sm:w-44">
                        @if($hasImage)
                            <img src="{{ $primaryFile->file_url }}" alt="{{ $submission->project_title }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full min-h-44 items-center justify-center bg-slate-50 text-slate-300">
                                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5v-16ZM20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5v-16Z"/>
                                </svg>
                            </div>
                        @endif

                        <span class="absolute left-3 top-3 rounded-full bg-violet-600 font-semibold text-white shadow-sm text-xs px-2.5 py-1">ผลงานการแข่งขัน</span>
                    </div>

                    <div class="flex min-w-0 flex-1 flex-col p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-violet-600">{{ $submission->competition?->title ?? 'ไม่พบการแข่งขัน' }}</p>
                                <h3 class="mt-2 line-clamp-2 text-slate-900 text-base font-semibold">{{ $submission->project_title }}</h3>
                                <p class="mt-1 text-slate-400 text-xs">รหัสผลงาน: {{ $submission->submission_code }}</p>
                            </div>

                            <div class="shrink-0 rounded-xl bg-blue-50 px-3 py-2 text-center">
                                <p class="text-[10px] font-semibold text-blue-500">คะแนน</p>
                                <p class="mt-0.5 text-lg font-bold text-blue-700">{{ number_format((float)$submission->final_score, 2) }}</p>
                            </div>
                        </div>

                        @if($submission->project_description)
                            <p class="mt-4 line-clamp-2 text-sm leading-6 text-slate-600">{{ $submission->project_description }}</p>
                        @endif

                        <div class="mt-4">
                            <span class="inline-flex items-center gap-1.5 rounded-full font-semibold {{ $statusConfig['class'] }} text-xs px-2.5 py-1">
                                <span class="h-1.5 w-1.5 rounded-full {{ $isPublished ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>

                        <div class="mt-auto flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                            @if($knowledgeItem)
                                @can('view', $knowledgeItem)
                                    <a href="{{ route('competition-admin.km.show', $knowledgeItem) }}" class="rounded-lg border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-50 inline-flex items-center justify-center h-9 px-3">ดู KM</a>
                                @endcan
                            @endif

                            @if($isPublished)
                                <x-ajax-form target="#km-list, #km-total" :action="route('competition-admin.submissions.km.unpublish', $submission)" method="DELETE" confirm="ยืนยันถอนผลงานนี้ออกจาก KM หรือไม่?" success="ถอนผลงานออกจาก KM เรียบร้อยแล้ว">
                                    <button type="submit" class="rounded-lg bg-amber-500 text-sm font-semibold text-white transition hover:bg-amber-600 inline-flex items-center justify-center h-9 px-3">ถอนเผยแพร่</button>
                                </x-ajax-form>
                            @else
                                <x-ajax-form target="#km-list, #km-total" :action="route('competition-admin.submissions.km.publish', $submission)" method="POST" confirm="ยืนยันเผยแพร่ผลงานนี้เข้าสู่ KM หรือไม่?" success="เผยแพร่ผลงานสู่ KM เรียบร้อยแล้ว">
                                    <button type="submit" class="rounded-lg bg-emerald-600 text-sm font-semibold text-white transition hover:bg-emerald-700 inline-flex items-center justify-center h-9 px-3">เผยแพร่สู่ KM</button>
                                </x-ajax-form>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @endforeach

        @if(!$hasKnowledgeItems && !$hasSubmissions)
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-14 text-center xl:col-span-2 shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                    </svg>
                </div>

                <h3 class="mt-4 text-slate-700 text-base font-semibold">ไม่พบรายการ</h3>
                <p class="mt-1 text-slate-400 text-xs">ไม่พบองค์ความรู้หรือผลงานการแข่งขันที่ตรงกับเงื่อนไข</p>
            </div>
        @endif
    </div>

    {{-- PAGINATION --}}
    <div class="space-y-3">
        @if($knowledgeItems->hasPages())
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                {{ $knowledgeItems->links() }}
            </div>
        @endif

        @if($submissions->hasPages())
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection