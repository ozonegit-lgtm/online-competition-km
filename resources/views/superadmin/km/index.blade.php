@extends('layouts.app')

@section('title', 'จัดการองค์ความรู้ทั้งหมด')

@section('header')
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-16ZM8 7h8M8 11h6"/></svg>
        </div>

        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900">จัดการองค์ความรู้ทั้งหมด</h1>
            <p class="mt-1 text-xs text-slate-500">จัดการองค์ความรู้และผลงานจากการแข่งขันในหน้าเดียว</p>
        </div>
    </div>

    @can('create', App\Models\KnowledgeItem::class)
        <a href="{{ route('superadmin.km.create') }}" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
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
    @php
        $activeFilterCount = collect([
            request('search'),
            request('category_id'),
            request('status'),
            request('source'),
            request('owner'),
        ])->filter(fn ($value) => filled($value))->count();
    @endphp

    <form method="GET" action="{{ route('superadmin.km.index') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" d="M4 6h16M7 12h10M10 18h4"/>
                    </svg>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-bold text-slate-900">ค้นหาและกรองข้อมูล</h2>

                        @if($activeFilterCount > 0)
                            <span class="inline-flex h-5 items-center rounded-full bg-blue-50 px-2 text-[10px] font-semibold text-blue-700">
                                ใช้งาน {{ $activeFilterCount }} ตัวกรอง
                            </span>
                        @endif
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        ค้นหาจากชื่อ รหัส เจ้าของ หรือการแข่งขัน แล้วจำกัดผลลัพธ์ด้วยตัวกรอง
                    </p>
                </div>
            </div>

            @if($activeFilterCount > 0)
                <a
                    href="{{ route('superadmin.km.index') }}"
                    class="inline-flex h-8 shrink-0 items-center justify-center gap-1.5 self-start rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 sm:self-auto"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M3 6h18"/>
                        <path d="M8 6V4h8v2"/>
                        <path d="M19 6l-1 14H6L5 6"/>
                    </svg>
                    ล้างตัวกรอง
                </a>
            @endif
        </div>

        <div class="p-4 sm:p-5">
            <div class="grid gap-4 xl:grid-cols-12">
                {{-- Search --}}
                <div class="xl:col-span-4">
                    <label for="km-search" class="mb-1.5 block text-xs font-semibold text-slate-600">
                        คำค้นหา
                    </label>

                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                        </svg>

                        <input
                            id="km-search"
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="ชื่อผลงาน รหัส เจ้าของ หรือการแข่งขัน"
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                    </div>
                </div>

                {{-- Category --}}
                <div class="sm:col-span-1 xl:col-span-2">
                    <label for="km-category" class="mb-1.5 block text-xs font-semibold text-slate-600">
                        หมวดหมู่
                    </label>

                    <div class="relative">
<select
                        id="km-category"
                        name="category_id"
                        class="h-10 w-full appearance-none rounded-lg border border-slate-300 bg-white pl-3 pr-10 text-sm text-slate-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">ทั้งหมด</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>

                    <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                    </div>
                </div>

                {{-- Status --}}
                <div class="sm:col-span-1 xl:col-span-2">
                    <label for="km-status" class="mb-1.5 block text-xs font-semibold text-slate-600">
                        สถานะ
                    </label>

                    <div class="relative">
<select
                        id="km-status"
                        name="status"
                        class="h-10 w-full appearance-none rounded-lg border border-slate-300 bg-white pl-3 pr-10 text-sm text-slate-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">ทั้งหมด</option>
                        <option value="draft" @selected(request('status') === 'draft')>ยังไม่เผยแพร่</option>
                        <option value="published" @selected(request('status') === 'published')>เผยแพร่แล้ว</option>
                        <option value="hidden" @selected(request('status') === 'hidden')>ซ่อน</option>
                    </select>

                    <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                    </div>
                </div>

                {{-- Source --}}
                <div class="sm:col-span-1 xl:col-span-2">
                    <label for="km-source" class="mb-1.5 block text-xs font-semibold text-slate-600">
                        ประเภทข้อมูล
                    </label>

                    <div class="relative">
<select
                        id="km-source"
                        name="source"
                        class="h-10 w-full appearance-none rounded-lg border border-slate-300 bg-white pl-3 pr-10 text-sm text-slate-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">ทั้งหมด</option>
                        <option value="manual" @selected(request('source') === 'manual')>Manual KM</option>
                        <option value="competition" @selected(request('source') === 'competition')>ผลงานการแข่งขัน</option>
                    </select>

                    <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                    </div>
                </div>

                {{-- Owner --}}
                <div class="sm:col-span-1 xl:col-span-2">
                    <label for="km-owner" class="mb-1.5 block text-xs font-semibold text-slate-600">
                        เจ้าของ
                    </label>

                    <div class="relative">
<select
                        id="km-owner"
                        name="owner"
                        class="h-10 w-full appearance-none rounded-lg border border-slate-300 bg-white pl-3 pr-10 text-sm text-slate-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">ทั้งหมด</option>
                        <option value="unassigned" @selected(request('owner') === 'unassigned')>ไม่มีเจ้าของ</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" @selected((string)request('owner') === (string)$owner->id)>
                                {{ $owner->username }}
                            </option>
                        @endforeach
                    </select>

                    <svg class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 text-xs text-slate-500">
                    @if($activeFilterCount > 0)
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="font-medium text-slate-600">กำลังกรอง:</span>

                            @if(request('search'))
                                <span class="inline-flex max-w-full items-center rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">
                                    คำค้นหา: <span class="ml-1 max-w-[220px] truncate font-semibold text-slate-800">{{ request('search') }}</span>
                                </span>
                            @endif

                            @if(request('category_id'))
                                @php
                                    $selectedCategory = $categories->firstWhere('id', (int) request('category_id'));
                                @endphp
                                @if($selectedCategory)
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">
                                        หมวดหมู่: <span class="ml-1 font-semibold text-slate-800">{{ $selectedCategory->category_name }}</span>
                                    </span>
                                @endif
                            @endif

                            @if(request('status'))
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">
                                    สถานะ:
                                    <span class="ml-1 font-semibold text-slate-800">
                                        {{ match(request('status')) {
                                            'draft' => 'ยังไม่เผยแพร่',
                                            'published' => 'เผยแพร่แล้ว',
                                            'hidden' => 'ซ่อน',
                                            default => request('status'),
                                        } }}
                                    </span>
                                </span>
                            @endif

                            @if(request('source'))
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">
                                    ประเภท:
                                    <span class="ml-1 font-semibold text-slate-800">
                                        {{ request('source') === 'competition' ? 'ผลงานการแข่งขัน' : 'Manual KM' }}
                                    </span>
                                </span>
                            @endif

                            @if(request('owner'))
                                @php
                                    $selectedOwner = request('owner') === 'unassigned'
                                        ? null
                                        : $owners->firstWhere('id', (int) request('owner'));
                                @endphp

                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">
                                    เจ้าของ:
                                    <span class="ml-1 font-semibold text-slate-800">
                                        {{ request('owner') === 'unassigned' ? 'ไม่มีเจ้าของ' : ($selectedOwner?->username ?? '-') }}
                                    </span>
                                </span>
                            @endif
                        </div>
                    @else
                        เลือกตัวกรองเท่าที่จำเป็น แล้วกดค้นหาเพื่อแสดงผลลัพธ์
                    @endif
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    @if($activeFilterCount > 0)
                        <a
                            href="{{ route('superadmin.km.index') }}"
                            class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300"
                        >
                            ล้างตัวกรอง
                        </a>
                    @endif

                    <button
                        type="submit"
                        class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg bg-slate-900 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                        </svg>
                        ค้นหา
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- HEADER --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-900">รายการทั้งหมด</h2>
            <p class="mt-1 text-xs text-slate-500">แสดง {{ number_format($visibleCount) }} รายการในหน้านี้</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z"/></svg>
                Manual KM
                <span class="rounded-full bg-blue-100 px-1.5 py-0.5 text-[10px] tabular-nums text-blue-700">
                    {{ number_format($knowledgeItems->total()) }}
                </span>
            </span>

            <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4ZM5 6H3v2a4 4 0 0 0 4 4M19 6h2v2a4 4 0 0 1-4 4"/></svg>
                การแข่งขัน
                <span class="rounded-full bg-violet-100 px-1.5 py-0.5 text-[10px] tabular-nums text-violet-700">
                    {{ number_format($submissions->total()) }}
                </span>
            </span>
        </div>
    </div>

    {{-- CARDS --}}
    <div class="grid gap-3 xl:grid-cols-2">

        {{-- MANUAL KM --}}
        @foreach($knowledgeItems as $item)
            @php
                $statusConfig = match($item->status) {
                    'published' => ['label' => 'เผยแพร่แล้ว', 'class' => 'bg-emerald-50 text-emerald-700', 'bar' => 'bg-emerald-500'],
                    'hidden' => ['label' => 'ซ่อน', 'class' => 'bg-amber-50 text-amber-700', 'bar' => 'bg-amber-400'],
                    default => ['label' => 'ยังไม่เผยแพร่', 'class' => 'bg-slate-100 text-slate-600', 'bar' => 'bg-slate-300'],
                };
            @endphp

            <article class="group flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-slate-300 hover:shadow-md">
                <!-- <div class="h-1 {{ $statusConfig['bar'] }}"></div> -->

                <div class="flex flex-1 flex-col p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z"/></svg>
                            Manual KM
                        </span>

                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>

                        @if($item->is_featured)
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                                <svg class="h-3.5 w-3.5 fill-amber-400" viewBox="0 0 24 24"><path d="m12 2.7 2.85 5.78 6.38.93-4.62 4.5 1.09 6.35L12 17.26l-5.7 3 1.09-6.35-4.62-4.5 6.38-.93L12 2.7Z"/></svg>
                                Featured
                            </span>
                        @endif
                    </div>

                    <h3 class="mt-3 line-clamp-2 text-base font-semibold leading-6 text-slate-900 transition group-hover:text-blue-700">{{ $item->title }}</h3>

                    @if($item->summary)
                        <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-500">{{ $item->summary }}</p>
                    @endif

                    <div class="mt-3 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 px-3 py-3">
                        <div class="min-w-0">
                            <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 21a8 8 0 0 1 16 0"/>
                                </svg>
                                เจ้าของ
                            </p>
                            <p class="mt-1 truncate text-xs font-medium text-slate-700">{{ $item->creator?->username ?? 'ไม่มีเจ้าของ' }}</p>
                        </div>

                        <div class="min-w-0">
                            <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                                </svg>
                                หมวดหมู่
                            </p>
                            <p class="mt-1 truncate text-xs font-medium text-slate-700">{{ $item->category?->category_name ?? '-' }}</p>
                        </div>

                        <div class="min-w-0">
                            <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M16 3v4M8 3v4M3 10h18"/>
                                </svg>
                                เผยแพร่เมื่อ
                            </p>
                            <p class="mt-1 text-xs font-medium text-slate-700">
                                {{ $item->published_at?->format('d/m/Y H:i') ?? '-' }}
                            </p>
                        </div>

                        <div class="min-w-0">
                            <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                </svg>
                                ประเภท
                            </p>
                            <p class="mt-1 truncate text-xs font-medium text-slate-700">องค์ความรู้</p>
                        </div>
                    </div>

                    <div class="mt-auto grid grid-cols-2 gap-1.5 border-t border-slate-100 pt-3 sm:grid-cols-3">
                        @can('view', $item)
                            <a href="{{ route('superadmin.km.show', $item) }}" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    ดู
                                </a>
                        @endcan

                        @can('update', $item)
                            <a href="{{ route('superadmin.km.edit', $item) }}" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                                    แก้ไข
                                </a>
                        @endcan

                        @if($item->status === 'published')
                            @can('unpublish', $item)
                                <x-ajax-form target="#km-list" :action="route('superadmin.km.unpublish', $item)" method="DELETE" confirm="ยืนยันถอนเผยแพร่องค์ความรู้นี้?" success="ถอนเผยแพร่องค์ความรู้เรียบร้อยแล้ว" class="w-full">
                                    <button type="submit" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-semibold text-amber-700 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3l18 18"/><path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c5 0 9 4 10 8a11.6 11.6 0 0 1-2.2 4.1"/><path d="M6.6 6.6A11.6 11.6 0 0 0 2 12c1 4 5 8 10 8a10.8 10.8 0 0 0 3.1-.5"/></svg>
                                        ถอนเผยแพร่
                                    </button>
                                </x-ajax-form>
                            @endcan
                        @else
                            @can('publish', $item)
                                <x-ajax-form target="#km-list" :action="route('superadmin.km.publish', $item)" method="POST" confirm="ยืนยันเผยแพร่องค์ความรู้นี้?" success="เผยแพร่องค์ความรู้เรียบร้อยแล้ว" class="w-full">
                                    <button type="submit" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                        เผยแพร่
                                    </button>
                                </x-ajax-form>
                            @endcan
                        @endif

                        @can('feature', $item)
                            <x-ajax-form target="#km-list" :action="route($item->is_featured ? 'superadmin.km.unfeature' : 'superadmin.km.feature', $item)" :method="$item->is_featured ? 'DELETE' : 'POST'" :confirm="$item->is_featured ? 'ยืนยันถอน Featured องค์ความรู้นี้?' : 'ยืนยันตั้ง Featured องค์ความรู้นี้?'" :success="$item->is_featured ? 'ถอน Featured เรียบร้อยแล้ว' : 'ตั้ง Featured เรียบร้อยแล้ว'" class="w-full">
                                <button type="submit" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-amber-200 bg-white px-3 text-xs font-semibold text-amber-700 transition hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="{{ $item->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>
                                    {{ $item->is_featured ? 'ถอน Featured' : 'ตั้ง Featured' }}
                                </button>
                            </x-ajax-form>
                        @endcan

                        @can('delete', $item)
                            <x-ajax-form target="#km-list" :action="route('superadmin.km.destroy', $item)" method="DELETE" confirm="ยืนยันการลบองค์ความรู้นี้?" success="ลบองค์ความรู้เรียบร้อยแล้ว" class="w-full">
                                <button type="submit" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                    ลบ
                                </button>
                            </x-ajax-form>
                        @endcan
                    </div>
                </div>
            </article>
        @endforeach

        {{-- COMPETITION KM --}}
        @foreach($submissions as $submission)
            @php
                $knowledgeItem = $submission->knowledgeItem;
                $isPublished = $knowledgeItem?->status === 'published';
                $competitionCategory = $categories
                    ->firstWhere('id', $submission->competition?->category_id)
                    ?->category_name ?? '-';

                $statusConfig = match($knowledgeItem?->status) {
                    'published' => ['label' => 'เผยแพร่แล้ว', 'class' => 'bg-emerald-50 text-emerald-700', 'bar' => 'bg-emerald-500'],
                    'hidden' => ['label' => 'ซ่อน', 'class' => 'bg-amber-50 text-amber-700', 'bar' => 'bg-amber-400'],
                    default => ['label' => 'ยังไม่เผยแพร่', 'class' => 'bg-slate-100 text-slate-600', 'bar' => 'bg-slate-300'],
                };
            @endphp

            <article id="km-submission-{{ $submission->id }}" class="group flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-slate-300 hover:shadow-md">
                <!-- <div class="h-1 {{ $statusConfig['bar'] }}"></div> -->

                <div class="flex flex-1 flex-col p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4ZM5 6H3v2a4 4 0 0 0 4 4M19 6h2v2a4 4 0 0 1-4 4"/></svg>
                            การแข่งขัน
                        </span>

                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>

                        @if($knowledgeItem?->is_featured)
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                                <svg class="h-3.5 w-3.5 fill-amber-400" viewBox="0 0 24 24"><path d="m12 2.7 2.85 5.78 6.38.93-4.62 4.5 1.09 6.35L12 17.26l-5.7 3 1.09-6.35-4.62-4.5 6.38-.93L12 2.7Z"/></svg>
                                Featured
                            </span>
                        @endif
                    </div>

                    <h3 class="mt-3 line-clamp-2 text-base font-semibold leading-6 text-slate-900 transition group-hover:text-violet-700">{{ $submission->project_title }}</h3>

                    <p class="mt-1 inline-flex items-center gap-1 text-[10px] font-medium text-slate-400">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 7V4h3M17 4h3v3M20 17v3h-3M7 20H4v-3"/>
                            <path d="M8 8h8v8H8z"/>
                        </svg>
                        รหัสผลงาน {{ $submission->submission_code }}
                    </p>

                    @if($submission->project_description)
                        <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-500">{{ $submission->project_description }}</p>
                    @endif

                    <div class="mt-3 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 px-3 py-3">
                        <div class="min-w-0">
                            <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M8 21h8M12 17v4"/>
                                    <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                </svg>
                                การแข่งขัน
                            </p>
                            <p class="mt-1 truncate text-xs font-medium text-slate-700">{{ $submission->competition?->title ?? 'ไม่พบการแข่งขัน' }}</p>
                        </div>

                        <div class="min-w-0">
                            <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>
                                </svg>
                                คะแนน
                            </p>
                            <p class="mt-1 text-xs font-semibold text-blue-700">{{ number_format((float)$submission->final_score, 2) }}</p>
                        </div>

                        <div class="min-w-0">
                            <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                                </svg>
                                หมวดหมู่
                            </p>
                            <p class="mt-1 truncate text-xs font-medium text-slate-700">{{ $competitionCategory }}</p>
                        </div>

                        <div class="min-w-0">
                            <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M16 3v4M8 3v4M3 10h18"/>
                                </svg>
                                เผยแพร่เมื่อ
                            </p>
                            <p class="mt-1 text-xs font-medium text-slate-700">
                                {{ $knowledgeItem?->published_at?->format('d/m/Y H:i') ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-auto grid grid-cols-2 gap-1.5 border-t border-slate-100 pt-3 sm:grid-cols-3">
                        @if($knowledgeItem)
                            @can('view', $knowledgeItem)
                                <a href="{{ route('superadmin.km.show', $knowledgeItem) }}" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    ดู
                                </a>
                            @endcan
                        @endif

                        @if($isPublished)
                            <x-ajax-form target="#km-list" :action="route('superadmin.submissions.km.unpublish', $submission)" method="DELETE" confirm="ยืนยันถอนผลงานนี้ออกจาก KM หรือไม่?" success="ถอนผลงานออกจาก KM เรียบร้อยแล้ว" class="w-full">
                                <button type="submit" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-semibold text-amber-700 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3l18 18"/><path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c5 0 9 4 10 8a11.6 11.6 0 0 1-2.2 4.1"/><path d="M6.6 6.6A11.6 11.6 0 0 0 2 12c1 4 5 8 10 8a10.8 10.8 0 0 0 3.1-.5"/></svg>
                                    ถอนเผยแพร่
                                </button>
                            </x-ajax-form>
                        @else
                            <x-ajax-form target="#km-list" :action="route('superadmin.submissions.km.publish', $submission)" method="POST" confirm="ยืนยันเผยแพร่ผลงานนี้เข้าสู่ KM หรือไม่?" success="เผยแพร่ผลงานสู่ KM เรียบร้อยแล้ว" class="w-full">
                                <button type="submit" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    เผยแพร่
                                </button>
                            </x-ajax-form>
                        @endif

                        @if($knowledgeItem)
                            @can('feature', $knowledgeItem)
                                <form method="POST" action="{{ route($knowledgeItem->is_featured ? 'superadmin.km.unfeature' : 'superadmin.km.feature', $knowledgeItem) }}" class="w-full">
                                    @csrf
                                    @if($knowledgeItem->is_featured)
                                        @method('DELETE')
                                    @endif

                                    <button type="submit" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-amber-200 bg-white px-3 text-xs font-semibold text-amber-700 transition hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="{{ $knowledgeItem->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>
                                        {{ $knowledgeItem->is_featured ? 'ถอน Featured' : 'ตั้ง Featured' }}
                                    </button>
                                </form>
                            @endcan

                            @can('delete', $knowledgeItem)
                                <x-ajax-form target="#km-list" :action="route('superadmin.km.destroy', $knowledgeItem)" method="DELETE" confirm="ยืนยันการลบรายการนี้?" success="ลบองค์ความรู้เรียบร้อยแล้ว" class="w-full">
                                    <button type="submit" class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                        ลบ
                                    </button>
                                </x-ajax-form>
                            @endcan
                        @endif
                    </div>
                </div>
            </article>
        @endforeach

        {{-- EMPTY --}}
        @if(!$hasKnowledgeItems && !$hasSubmissions)
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-4 py-8 text-center shadow-sm xl:col-span-2">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-16Z"/></svg>
                </div>

                <h3 class="mt-3 text-base font-semibold text-slate-700">ไม่พบรายการ</h3>
                <p class="mt-1 text-xs text-slate-400">ไม่พบองค์ความรู้หรือผลงานการแข่งขันที่ตรงกับเงื่อนไข</p>

                @if(request('search') || request('category_id') || request('status') || request('source') || request('owner'))
                    <a href="{{ route('superadmin.km.index') }}" class="mt-4 inline-flex h-8 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">ล้างตัวกรอง</a>
                @endif
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
