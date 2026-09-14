@extends('layouts.km')
@section('title', 'คลังผลงานการประกวด')
@section('content')
<div class="min-h-screen bg-slate-50">
    @php
        $resolveKmMedia = static function ($item): array {
            if ($item->cover_image) {
                return [
                    'url' => $item->cover_image_url,
                    'kind' => 'cover',
                    'document_type' => null,
                ];
            }
            if ($item->submission_id === null) {
                $attachmentMedia = $item->attachmentMedia();
                return [
                    'url' => $attachmentMedia['is_image']
                        ? route('knowledge-items.cover', $item)
                        : null,
                    'kind' => $attachmentMedia['is_image']
                        ? 'attachment-image'
                        : ($attachmentMedia['exists'] ? 'document' : 'empty'),
                    'document_type' => $attachmentMedia['exists'] && ! $attachmentMedia['is_image']
                        ? $attachmentMedia['type']
                        : null,
                ];
            }
            $submissionImage = $item->submission?->files
                ?->filter(fn ($file) => str_starts_with((string) $file->mime_type, 'image/'))
                ->sortBy([
                    ['is_primary', 'desc'],
                    ['id', 'asc'],
                ])
                ->first();
            return [
                'url' => $submissionImage?->file_url,
                'kind' => $submissionImage ? 'submission-image' : 'empty',
                'document_type' => null,
            ];
        };
    @endphp
    {{-- =========================================================
        HERO
    ========================================================== --}}
    <section
        class="relative isolate overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-emerald-50"
    >
        {{-- Background glow --}}
        <div
            class="pointer-events-none absolute -left-32 top-20 h-[420px] w-[420px] rounded-full bg-emerald-100/70 blur-2xl"
        ></div>
        <div
            class="pointer-events-none absolute -right-32 -top-20 h-[420px] w-[420px] rounded-full bg-emerald-100/80 blur-2xl"
        ></div>
        {{-- Decorative dots left --}}
        <div
            class="pointer-events-none absolute left-14 top-56 hidden h-24 w-24 opacity-50 lg:block"
        >
            <div class="grid grid-cols-4 gap-4">
                @for ($i = 0; $i < 16; $i++)
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                @endfor
            </div>
        </div>
        {{-- Decorative dots right --}}
        <div
            class="pointer-events-none absolute right-14 top-52 hidden h-24 w-24 opacity-50 lg:block"
        >
            <div class="grid grid-cols-4 gap-4">
                @for ($i = 0; $i < 16; $i++)
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                @endfor
            </div>
        </div>
        {{-- Decorative circles --}}
        <div
            class="pointer-events-none absolute -left-32 top-24 hidden h-80 w-80 rounded-full border-[80px] border-emerald-100/50 lg:block"
        ></div>
        <div
            class="pointer-events-none absolute -right-24 -top-24 hidden h-80 w-80 rounded-full border-[70px] border-emerald-100/60 lg:block"
        ></div>
        {{-- HERO CONTENT --}}
        <div
            class="relative mx-auto flex max-w-7xl items-center justify-center px-4 py-4"
        >
            <div class="w-full max-w-7xl text-center">
                {{-- Badge --}}
                <div
                    class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-400 bg-white/90 px-3 py-1.5 text-xs font-medium text-emerald-700 shadow-sm"
                >
                    <span
                        class="h-2 w-2 rounded-full bg-emerald-500"
                    ></span>
                    คลังผลงานการประกวด
                </div>
                {{-- Heading --}}
                <h1
                    class="mx-auto max-w-7xl leading-tight tracking-tight text-slate-900 text-xl font-bold"
                >
                    ผลงานที่สร้างแรงบันดาลใจ
                    <span
                        class="mt-2 block text-emerald-600"
                    >
                        และองค์ความรู้
                    </span>
                </h1>
                {{-- Description --}}
                <p
                    class="mx-auto mt-4 max-w-2xl leading-7 text-slate-500 text-xs"
                >
                    รวบรวมผลงานจากการแข่งขันที่ผ่านการตัดสินและตรวจสอบแล้ว
                    <br class="hidden sm:block">
                    เพื่อให้คุณค้นหา เรียนรู้ และนำแนวคิดดี ๆ ไปต่อยอดได้ง่ายขึ้น
                </p>
                {{-- Search --}}
                <form
                    id="hero-search-form"
                    data-search-form
                    method="GET"
                    action="{{ route('home') }}"
                    class="mx-auto mt-4 w-full max-w-2xl"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row"
                    >
                        {{-- Input --}}
                        <div class="relative flex-1">
                            <svg
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="ค้นหาผลงาน หรือชื่อการแข่งขัน..."
                                aria-label="ค้นหาผลงาน"
                                class="w-full rounded-xl border border-slate-200 bg-white text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 py-2 h-10 px-3 pl-11 pr-5"
                            >
                        </div>
                        {{-- Button --}}
                        <button
                            type="submit"
                            data-search-button
                            class="shrink-0 rounded-xl bg-emerald-600 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 hover:shadow-sm focus:outline-none focus:ring-4 focus:ring-emerald-500/20 disabled:cursor-wait disabled:opacity-80 inline-flex items-center justify-center gap-2 h-9 px-3"
                        >
                            <span data-search-spinner class="km-search-spinner hidden" aria-hidden="true"></span>
                            <span data-search-label>ค้นหาผลงาน</span>
                        </button>
                    </div>
                </form>
                {{-- Helper --}}
                <p
                    class="mt-3 text-slate-400 text-xs"
                >
                    ค้นหาจากชื่อผลงาน ชื่อทีม หรือชื่อการแข่งขัน
                </p>
                <a
                    href="{{ route('knowledge.index') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-4 inline-flex h-10 items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 text-sm font-bold text-emerald-700 shadow-sm transition hover:border-emerald-400 hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                >
                    คลัง E-Book KM ↗
                </a>
            </div>
        </div>
        {{-- Bottom wave --}}
        <div
            class="pointer-events-none absolute -bottom-1 left-0 h-4 w-full overflow-hidden"
        >
            <svg
                class="absolute bottom-0 h-full w-full"
                viewBox="0 0 1440 100"
                preserveAspectRatio="none"
                fill="none"
            >
                <path
                    d="M0 55C180 5 330 10 520 45C720 82 860 95 1060 55C1230 20 1330 20 1440 45V100H0V55Z"
                    fill="#f8fafc"
                />
            </svg>
        </div>
    </section>
    {{-- =========================================================
        DESKTOP 3-COLUMN LAYOUT
    ========================================================== --}}
    <div
        class="relative mx-auto w-full max-w-7xl px-4 py-4"
    >
        <div
            class="grid grid-cols-1 gap-4 lg:grid-cols-[160px_minmax(0,1fr)_160px] xl:grid-cols-[200px_minmax(0,1fr)_200px]"
        >
            {{-- พื้นที่ว่างด้านซ้าย --}}
            <aside
                class="hidden lg:block"
                aria-label="พื้นที่ด้านซ้าย"
            >
            </aside>
            {{-- เนื้อหาหลัก --}}
            <main class="min-w-0">
        {{-- =====================================================
            CATEGORIES
        ====================================================== --}}
        <section>
            <div class="mb-4">
                <p
                    class="text-xs font-semibold text-emerald-600"
                >
                    สำรวจผลงาน
                </p>
                <h2
                    class="mt-1 text-slate-900 text-base font-semibold"
                >
                    หมวดหมู่
                </h2>
            </div>
            <div
                class="flex flex-wrap gap-2 pb-1"
            >
                {{-- ALL --}}
                <a
                    href="{{ route('home') }}"
                    class="shrink-0 rounded-full border text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 {{ !request('category')
                        ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700' }} inline-flex items-center justify-center h-9 px-3"
                >
                    ทั้งหมด
                </a>
                {{-- CATEGORIES --}}
                @foreach ($categories ?? [] as $category)
                    <a
                        href="{{ route('home', ['category' => $category->id]) }}"
                        class="shrink-0 rounded-full border text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 {{ request('category') == $category->id
                            ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700' }} inline-flex items-center justify-center h-9 px-3"
                    >
                        {{ $category->category_name }}
                    </a>
                @endforeach
            </div>
        </section>
        {{-- =====================================================
            PUBLISHED COMPETITION RESULTS (PODIUM)
        ====================================================== --}}
        @if (($publishedResults ?? collect())->isNotEmpty())
            <section class="mt-4">
                <div class="mb-4 flex items-start gap-2.5">
                    <div
                        class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M8 21h8"/>
                            <path d="M12 17v4"/>
                            <path d="M7 4h10v5a5 5 0 01-10 0V4Z"/>
                            <path d="M7 6H4a1 1 0 00-1 1v1a4 4 0 004 4"/>
                            <path d="M17 6h3a1 1 0 011 1v1a4 4 0 01-4 4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium leading-5 text-amber-600">
                            ผลการแข่งขัน
                        </p>
                        <h2 class="leading-tight text-slate-900 text-base font-semibold">
                            ผลงานที่ได้รับรางวัล
                        </h2>
                        <p class="mt-1 leading-5 text-slate-500 text-xs">
                            ผลงานอันดับ 1–3 ที่ผู้จัดการแข่งขันประกาศแล้ว
                        </p>
                    </div>
                </div>
                <div class="space-y-4">
                    @foreach ($publishedResults as $resultCompetition)
                        <article
                            class="km-reveal overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                        >
                            <div
                                class="flex flex-col gap-1.5 border-b border-slate-200 bg-white px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <h3 class="truncate text-slate-900 text-base font-semibold">
                                        {{ $resultCompetition->title }}
                                    </h3>
                                    <p class="mt-0.5 text-slate-500 text-xs">
                                        {{ $resultCompetition->category?->category_name
                                            ?? 'ไม่ระบุหมวดหมู่' }}
                                    </p>
                                </div>
                                @if ($resultCompetition->result_announcement)
                                    <span
                                        class="inline-flex shrink-0 items-center gap-1 text-[11px] text-slate-400"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        ประกาศเมื่อ
                                        {{ $resultCompetition
                                            ->result_announcement
                                            ->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>
                            {{-- Podium --}}
                            <div
                                data-podium-stage
                                class="relative overflow-hidden bg-gradient-to-b from-amber-50/40 via-white to-emerald-50/30 px-4 py-4 sm:px-4 sm:py-4"
                            >
                                {{-- ambient glow, echoes hero background --}}
                                <div
                                    class="pointer-events-none absolute left-1/2 top-0 h-48 w-[28rem] -translate-x-1/2 -translate-y-1/3 rounded-full bg-amber-100/55 blur-3xl"
                                ></div>
                                {{-- decorative dot grid, echoes hero --}}
                                <div
                                    class="pointer-events-none absolute right-4 top-4 hidden h-16 w-16 opacity-40 sm:block"
                                >
                                    <div class="grid grid-cols-4 gap-2.5">
                                        @for ($i = 0; $i < 16; $i++)
                                            <span class="h-1 w-1 rounded-full bg-emerald-300"></span>
                                        @endfor
                                    </div>
                                </div>
                                <div
                                    class="relative flex flex-wrap items-end justify-center gap-3 sm:gap-4"
                                >
                                    @foreach (
                                        $resultCompetition->submissions
                                        as $submission
                                    )
                                        @php
                                            $rank = (int) $submission->rank;
                                            $rankStyle = match ($rank) {
                                                1 => [
                                                    'order'       => 'order-2',
                                                    'card'        => 'w-[104px] sm:w-40',
                                                    'ring'        => 'ring-amber-200',
                                                    'hoverRing'   => 'hover:shadow-[0_18px_38px_-16px_rgba(245,158,11,0.45)]',
                                                    'medal'       => 'bg-gradient-to-br from-amber-200 via-amber-400 to-amber-500',
                                                    'medalSize'   => 'h-10 w-10 sm:h-11 sm:w-11',
                                                    'ribbon'      => 'bg-amber-400',
                                                    'score'       => 'text-amber-600',
                                                    'label'       => 'ชนะเลิศ',
                                                    'labelShort'  => 'ชนะเลิศ',
                                                    'labelClass'  => 'border-amber-200 bg-amber-50 text-amber-700',
                                                    'riserBg'     => 'bg-gradient-to-b from-amber-50 via-amber-50/80 to-white',
                                                    'riserBorder' => 'border-amber-200',
                                                    'riserTop'    => 'bg-gradient-to-r from-amber-300 via-amber-500 to-amber-300',
                                                    'riserNum'    => 'text-amber-500',
                                                    'riserLabel'  => 'text-amber-700',
                                                    'riserH'      => 'h-[72px]',
                                                    'crown'       => true,
                                                ],
                                                2 => [
                                                    'order'       => 'order-1',
                                                    'card'        => 'w-[88px] sm:w-36',
                                                    'ring'        => 'ring-slate-200',
                                                    'hoverRing'   => 'hover:shadow-[0_16px_34px_-16px_rgba(100,116,139,0.35)]',
                                                    'medal'       => 'bg-gradient-to-br from-slate-100 via-slate-300 to-slate-400',
                                                    'medalSize'   => 'h-9 w-9 sm:h-10 sm:w-10',
                                                    'ribbon'      => 'bg-slate-400',
                                                    'score'       => 'text-slate-600',
                                                    'label'       => 'รองชนะเลิศอันดับ 1',
                                                    'labelShort'  => 'อันดับ 2',
                                                    'labelClass'  => 'border-slate-200 bg-slate-50 text-slate-600',
                                                    'riserBg'     => 'bg-gradient-to-b from-slate-50 via-slate-50/80 to-white',
                                                    'riserBorder' => 'border-slate-200',
                                                    'riserTop'    => 'bg-gradient-to-r from-slate-300 via-slate-400 to-slate-300',
                                                    'riserNum'    => 'text-slate-500',
                                                    'riserLabel'  => 'text-slate-600',
                                                    'riserH'      => 'h-14',
                                                    'crown'       => false,
                                                ],
                                                default => [
                                                    'order'       => 'order-3',
                                                    'card'        => 'w-[88px] sm:w-36',
                                                    'ring'        => 'ring-orange-200',
                                                    'hoverRing'   => 'hover:shadow-[0_16px_34px_-16px_rgba(251,146,60,0.35)]',
                                                    'medal'       => 'bg-gradient-to-br from-orange-200 via-orange-400 to-orange-500',
                                                    'medalSize'   => 'h-9 w-9 sm:h-10 sm:w-10',
                                                    'ribbon'      => 'bg-orange-400',
                                                    'score'       => 'text-orange-600',
                                                    'label'       => 'รองชนะเลิศอันดับ 2',
                                                    'labelShort'  => 'อันดับ 3',
                                                    'labelClass'  => 'border-orange-200 bg-orange-50 text-orange-700',
                                                    'riserBg'     => 'bg-gradient-to-b from-orange-50 via-orange-50/80 to-white',
                                                    'riserBorder' => 'border-orange-200',
                                                    'riserTop'    => 'bg-gradient-to-r from-orange-300 via-orange-500 to-orange-300',
                                                    'riserNum'    => 'text-orange-500',
                                                    'riserLabel'  => 'text-orange-700',
                                                    'riserH'      => 'h-12',
                                                    'crown'       => false,
                                                ],
                                            };
                                            $image = $submission
                                                ->files
                                                ->first(
                                                    fn ($file) => str_starts_with(
                                                        (string) $file->mime_type,
                                                        'image/'
                                                    )
                                                );
                                            $imageUrl = $image?->file_url;
                                        @endphp
                                        <div
                                            data-podium-item
                                            data-rank="{{ $rank }}"
                                            class="km-podium-item {{ $rankStyle['order'] }} {{ $rankStyle['card'] }} relative flex flex-col items-center"
                                        >
                                            {{-- Award title --}}
                                            <div
                                                class="relative z-20 mb-1 inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[9px] font-bold tracking-wide shadow-sm sm:text-[10px] {{ $rankStyle['labelClass'] }}"
                                            >
                                                @if ($rankStyle['crown'])
                                                    <svg viewBox="0 0 24 24" class="h-3 w-3 fill-current" aria-hidden="true">
                                                        <path d="M2 8l4 3 6-7 6 7 4-3-2 11H4L2 8Zm2 13h16v2H4v-2Z"/>
                                                    </svg>
                                                @else
                                                    <svg viewBox="0 0 24 24" class="h-3 w-3 fill-none stroke-current" stroke-width="2" aria-hidden="true">
                                                        <circle cx="12" cy="8" r="5"/>
                                                        <path d="m8.5 12-1 9 4.5-2.5L16.5 21l-1-9"/>
                                                    </svg>
                                                @endif
                                                <span class="sm:hidden">{{ $rankStyle['labelShort'] }}</span>
                                                <span class="hidden sm:inline">{{ $rankStyle['label'] }}</span>
                                            </div>
                                            {{-- medal / winner emblem --}}
                                            <div class="relative z-20 -mb-4 flex flex-col items-center">
                                                @if ($rankStyle['crown'])
                                                    <div class="mb-1 flex items-center gap-1 text-amber-400" aria-hidden="true">
                                                        <span class="h-1 w-1 rounded-full bg-amber-300"></span>
                                                        <svg viewBox="0 0 24 24" class="h-4 w-4 fill-current drop-shadow-sm">
                                                            <path d="M2 8l4 3 6-7 6 7 4-3-2 11H4L2 8Zm2 13h16v2H4v-2Z"/>
                                                        </svg>
                                                        <span class="h-1 w-1 rounded-full bg-amber-300"></span>
                                                    </div>
                                                @endif
                                                <div
                                                    class="{{ $rankStyle['medalSize'] }} flex items-center justify-center rounded-full border-[3px] border-white shadow-sm ring-1 ring-black/5 {{ $rankStyle['medal'] }}"
                                                >
                                                    <span class="text-sm font-black text-white drop-shadow-sm sm:text-base">
                                                        {{ $rank }}
                                                    </span>
                                                </div>
                                                <div class="-mt-0.5 flex gap-0.5" aria-hidden="true">
                                                    <span class="km-ribbon-l h-2.5 w-2 {{ $rankStyle['ribbon'] }}"></span>
                                                    <span class="km-ribbon-r h-2.5 w-2 {{ $rankStyle['ribbon'] }}"></span>
                                                </div>
                                            </div>
                                            {{-- Award card --}}
                                            <div
                                                class="relative z-10 w-full overflow-hidden rounded-xl border border-slate-200 bg-white pt-4 shadow-sm ring-1 transition-all duration-200 hover:-translate-y-0.5 {{ $rankStyle['ring'] }} {{ $rankStyle['hoverRing'] }}"
                                            >
                                                <div
                                                    class="absolute inset-x-0 top-0 h-1 {{ $rankStyle['riserTop'] }}"
                                                    aria-hidden="true"
                                                ></div>
                                                <div
                                                    class="relative flex aspect-square items-center justify-center overflow-hidden bg-slate-50/70 p-1.5 {{ $imageUrl ? 'km-image-frame is-loading' : 'km-empty-pattern' }}"
                                                >
                                                    @if ($imageUrl)
                                                        <img
                                                            src="{{ $imageUrl }}"
                                                            alt="{{ $submission->project_title }}"
                                                            class="h-full w-full object-contain"
                                                            loading="lazy"
                                                            data-km-image
                                                        >
                                                    @else
                                                        <div class="flex flex-col items-center gap-1.5 text-slate-400">
                                                            <svg class="h-6 w-6 text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                                                <circle cx="8.5" cy="9" r="1.5"/>
                                                                <path d="m4 17 5-5 4 4 2-2 5 4"/>
                                                            </svg>
                                                            <span class="text-[9px]">ยังไม่มีภาพ</span>
                                                        </div>
                                                    @endif
                                                    @if ($submission->is_shared_rank)
                                                        <span
                                                            class="absolute left-1.5 top-1.5 rounded-full bg-slate-900/80 font-bold text-white shadow-sm text-xs px-2.5 py-1"
                                                        >
                                                            ร่วม
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="border-t border-slate-100 px-2.5 py-2">
                                                    <h4
                                                        class="line-clamp-1 text-[11px] font-bold text-slate-900"
                                                    >
                                                        {{ $submission->project_title }}
                                                    </h4>
                                                    <p
                                                        class="mt-0.5 truncate text-slate-400 text-xs"
                                                    >
                                                        {{ $submission->submission_code }}
                                                    </p>
                                                    <div class="mt-1.5 flex items-end justify-between gap-1">
                                                        <span class="text-[9px] font-medium leading-none text-slate-500 sm:text-[10px]">
                                                            คะแนน
                                                        </span>
                                                        <span
                                                            class="text-sm font-black leading-none tabular-nums {{ $rankStyle['score'] }}"
                                                            data-count-score="{{ number_format((float) $submission->final_score, 2, '.', '') }}"
                                                        >
                                                            {{ number_format((float) $submission->final_score, 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Podium pedestal --}}
                                            <div class="relative mt-1.5 w-full">
                                                <div
                                                    class="relative flex w-full flex-col items-center justify-center overflow-hidden rounded-t-lg border {{ $rankStyle['riserBorder'] }} {{ $rankStyle['riserBg'] }} {{ $rankStyle['riserH'] }} shadow-[inset_0_1px_2px_rgba(255,255,255,0.9)]"
                                                >
                                                    <div
                                                        class="absolute inset-x-0 top-0 h-1 {{ $rankStyle['riserTop'] }}"
                                                        aria-hidden="true"
                                                    ></div>
                                                    <span
                                                        class="text-[9px] font-bold leading-none sm:text-[10px] {{ $rankStyle['riserLabel'] }}"
                                                    >
                                                        อันดับ
                                                    </span>
                                                    <span
                                                        class="mt-0.5 text-xl font-black leading-none {{ $rankStyle['riserNum'] }}"
                                                    >
                                                        {{ $rank }}
                                                    </span>
                                                </div>
                                                <div
                                                    class="mx-1 h-1.5 rounded-b-md border-x border-b {{ $rankStyle['riserBorder'] }} bg-white/90"
                                                    aria-hidden="true"
                                                ></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div
                                    class="pointer-events-none relative mx-auto mt-0.5 h-px w-full max-w-7xl bg-gradient-to-r from-transparent via-slate-200 to-transparent"
                                    aria-hidden="true"
                                ></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
@endif
        {{-- =====================================================
            FEATURED WORKS
        ====================================================== --}}
        @if ($featuredItems->isNotEmpty())
            <section class="mt-4">
                <div class="mb-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-yellow-100 text-yellow-700"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M8 21h8"/>
                                <path d="M12 17v4"/>
                                <path d="M7 4h10v5a5 5 0 01-10 0V4Z"/>
                                <path d="M7 6H4a1 1 0 00-1 1v1a4 4 0 004 4"/>
                                <path d="M17 6h3a1 1 0 011 1v1a4 4 0 01-4 4"/>
                            </svg>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-yellow-600"
                            >
                                ผลงานที่ได้รับการแนะนำ
                            </p>
                            <h2
                                class="text-slate-900 text-base font-semibold"
                            >
                                ผลงานเด่น
                            </h2>
                        </div>
                    </div>
                </div>
                <div
                    class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
                >
                    @foreach ($featuredItems as $item)
                        @php
                            $submission = $item->submission;
                            $isManual = $item->submission_id === null;
                            $media = $resolveKmMedia($item);
                            $imageUrl = $media['url'];
                            $sourceType = $isManual
                                ? 'องค์ความรู้'
                                : 'การแข่งขัน';
                            $sourceDetail = $isManual
                                ? ($item->category?->category_name ?? 'ไม่ระบุหมวดหมู่')
                                : ($submission?->competition?->title ?? 'ไม่ระบุการแข่งขัน');
                        @endphp
                        <article
                            class="km-reveal km-card-motion group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:border-emerald-200"
                        >
                            <a href="{{ route('knowledge.show', $item) }}" class="block">
                                <div
                                    class="relative aspect-[16/10] overflow-hidden bg-slate-100 {{ $imageUrl ? 'km-image-frame is-loading' : 'km-empty-pattern' }}"
                                >
                                    @if ($imageUrl)
                                        <img
                                            data-km-media="{{ $media['kind'] }}"
                                            src="{{ $imageUrl }}"
                                            alt="{{ $item->title }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            loading="lazy"
                                            data-km-image
                                        >
                                    @elseif ($media['document_type'])
                                        <div data-km-media="document" data-file-type="{{ $media['document_type'] }}" class="flex h-full flex-col items-center justify-center gap-2 text-slate-500">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200/80">
                                                <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                    <path d="M6 2h9l3 3v17H6z"/>
                                                    <path d="M14 2v4h4"/>
                                                    <path d="M9 13h6M9 17h4"/>
                                                </svg>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-sm font-bold text-slate-700">{{ $media['document_type'] }}</p>
                                                <p class="mt-0.5 text-xs">เอกสารแนบ</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex h-full flex-col items-center justify-center gap-2 text-slate-400">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/80 shadow-sm ring-1 ring-slate-200/80">
                                                @if ($isManual)
                                                    <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                                    </svg>
                                                @else
                                                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                        <path d="M8 21h8M12 17v4"/>
                                                        <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                                        <path d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4"/>
                                                        <path d="M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium">
                                                {{ $isManual ? 'องค์ความรู้' : 'ผลงานการแข่งขัน' }}
                                            </span>
                                        </div>
                                    @endif
                                    <div
                                        class="absolute left-3 top-3"
                                    >
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-yellow-400 font-bold text-yellow-950 shadow-sm text-xs px-2.5 py-1"
                                        >
                                            ★ ผลงานแนะนำ
                                        </span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="mb-1.5 flex items-center gap-2">
                                        <span
                                            class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $isManual ? 'bg-slate-100 text-slate-600' : 'bg-emerald-50 text-emerald-700' }}"
                                        >
                                            @if ($isManual)
                                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                                </svg>
                                            @else
                                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="M8 21h8"/>
                                                    <path d="M12 17v4"/>
                                                    <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                                    <path d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4"/>
                                                    <path d="M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                                                </svg>
                                            @endif
                                            {{ $sourceType }}
                                        </span>
                                        <p class="min-w-0 line-clamp-1 text-xs font-medium text-slate-500">
                                            {{ $sourceDetail }}
                                        </p>
                                    </div>
                                    <h3
                                        class="line-clamp-2 text-slate-800 transition group-hover:text-emerald-700 text-base font-semibold"
                                    >
                                        {{ $item->title }}
                                    </h3>
                                    @if (! $isManual && $item->summary)
                                        <p
                                            class="mt-1.5 line-clamp-2 leading-6 text-slate-500 text-xs"
                                        >
                                            {{ $item->summary }}
                                        </p>
                                    @endif
                                    <div
                                        class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3"
                                    >
                                        <div class="min-w-0">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                                    <path d="M16 3v4M8 3v4M3 10h18"/>
                                                </svg>
                                                เผยแพร่เมื่อ
                                            </span>
                                            <span class="mt-0.5 block text-xs font-semibold text-slate-600">
                                                {{ $item->published_at?->format('d/m/Y') ?? '-' }}
                                            </span>
                                        </div>
                                        @if (! $isManual)
                                            <div class="shrink-0 text-right">
                                                <span class="inline-flex items-center justify-end gap-1 text-[10px] font-medium text-slate-400">
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>
                                                    </svg>
                                                    คะแนนรวม
                                                </span>
                                                <span
                                                    class="mt-0.5 block text-sm font-bold tabular-nums text-emerald-600"
                                                >
                                                    {{ $submission?->final_score !== null
                                                        ? number_format((float) $submission->final_score, 2)
                                                        : '-' }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
        {{-- =====================================================
            ALL WORKS
        ====================================================== --}}
        <section class="mt-4">
            <div
                class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p
                        class="text-xs font-medium text-emerald-600"
                    >
                        คลังผลงาน
                    </p>
                    <h2
                        class="mt-1 text-slate-900 text-base font-semibold"
                    >
                        ผลงานทั้งหมด
                    </h2>
                    <p
                        class="mt-1 text-slate-500 text-xs"
                    >
                        ผลงานที่ผ่านการตรวจสอบและเผยแพร่แล้ว
                    </p>
                </div>
            {{-- SORT --}}
                <form
                    method="GET"
                    action="{{ route('home') }}"
                    id="sort-form"
                    class="w-full sm:w-auto sm:self-end"
                >
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if (request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <input type="hidden" name="sort" id="sort-value" value="{{ request('sort', 'latest') }}">
                    {{-- Custom Dropdown --}}
                    <div class="flex items-center gap-2 sm:justify-end">
                        <span class="shrink-0 text-xs font-medium text-slate-500">
                            เรียงตาม
                        </span>
                        <div class="relative min-w-0 flex-1 sm:flex-none" id="sort-dropdown">
                        @php
                            $sortOptions = [
                                'latest' => ['label' => 'ล่าสุด', 'icon' => 'clock'],
                                'score'  => ['label' => 'คะแนนสูงสุด', 'icon' => 'star'],
                                'title'  => ['label' => 'ชื่อผลงาน', 'icon' => 'text'],
                            ];
                            $currentSort = request('sort', 'latest');
                        @endphp
                        {{-- Trigger button --}}
                        <button
                            type="button"
                            id="sort-trigger"
                            aria-haspopup="true"
                            aria-controls="sort-panel"
                            aria-label="เรียงลำดับผลงาน"
                            class="flex w-full min-w-0 items-center justify-between rounded-xl border border-slate-200 bg-white text-sm font-bold text-slate-700 shadow-sm transition duration-200 hover:border-emerald-300 hover:shadow-sm sm:w-44 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 justify-center h-9 px-3"
                        >
                            <span id="sort-label">{{ $sortOptions[$currentSort]['label'] }}</span>
                            <svg
                                id="sort-chevron"
                                class="h-4 w-4 text-slate-400 transition-transform duration-200"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>
                        {{-- Panel --}}
                        <div
                            id="sort-panel"
                            class="km-sort-panel absolute right-0 z-20 mt-2 w-full min-w-[11rem] overflow-hidden rounded-xl bg-white p-1.5 shadow-lg ring-1 ring-slate-200 sm:w-44 border border-slate-200"
                        >
                            @foreach ($sortOptions as $value => $option)
                                <button
                                    type="button"
                                    data-value="{{ $value }}"
                                    data-label="{{ $option['label'] }}"
                                    class="sort-option flex w-full items-center justify-between rounded-xl text-left text-sm font-semibold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 {{ $currentSort === $value ? 'bg-emerald-50 text-emerald-700' : '' }} justify-center h-9 px-3"
                                >
                                    {{ $option['label'] }}
                                    @if ($option['icon'] === 'clock')
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="9"/>
                                            <path d="M12 7v5l3 3"/>
                                        </svg>
                                    @elseif ($option['icon'] === 'star')
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2 9.5 8.5 2 9.5l5.5 5-1.5 7.5 6-4 6 4-1.5-7.5 5.5-5-7.5-1L12 2Z"/>
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M4 6h16M4 12h10M4 18h7"/>
                                        </svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                </form>
            </div>
            {{-- WORKS --}}
            @if ($knowledgeItems->isNotEmpty())
                <div
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    @foreach ($knowledgeItems as $item)
                        @php
                            $submission = $item->submission;
                            $isManual = $item->submission_id === null;
                            $media = $resolveKmMedia($item);
                            $imageUrl = $media['url'];
                            $sourceType = $isManual
                                ? 'องค์ความรู้'
                                : 'การแข่งขัน';
                            $sourceDetail = $isManual
                                ? ($item->category?->category_name ?? 'ไม่ระบุหมวดหมู่')
                                : ($submission?->competition?->title ?? 'ไม่ระบุการแข่งขัน');
                        @endphp
                        <article
                            class="km-reveal km-card-motion group h-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-emerald-200"
                        >
                            <a
                                href="{{ route('knowledge.show', $item) }}"
                                class="flex h-full flex-col focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-inset"
                            >
                                <div
                                    class="relative aspect-[4/3] overflow-hidden bg-slate-100 {{ $imageUrl ? 'km-image-frame is-loading' : 'km-empty-pattern' }}"
                                >
                                    @if ($imageUrl)
                                        <img
                                            data-km-media="{{ $media['kind'] }}"
                                            src="{{ $imageUrl }}"
                                            alt="{{ $item->title }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                            loading="lazy"
                                            data-km-image
                                        >
                                    @elseif ($media['document_type'])
                                        <div data-km-media="document" data-file-type="{{ $media['document_type'] }}" class="flex h-full flex-col items-center justify-center gap-2 text-slate-500">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/85 shadow-sm ring-1 ring-slate-200/80">
                                                <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                    <path d="M6 2h9l3 3v17H6z"/>
                                                    <path d="M14 2v4h4"/>
                                                    <path d="M9 13h6M9 17h4"/>
                                                </svg>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-sm font-bold text-slate-700">{{ $media['document_type'] }}</p>
                                                <p class="mt-0.5 text-xs">เอกสารแนบ</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex h-full flex-col items-center justify-center gap-2 text-slate-400">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/85 shadow-sm ring-1 ring-slate-200/80">
                                                @if ($isManual)
                                                    <svg class="h-6 w-6 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                                    </svg>
                                                @else
                                                    <svg class="h-6 w-6 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                        <path d="M8 21h8M12 17v4"/>
                                                        <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                                        <path d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4"/>
                                                        <path d="M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium">
                                                {{ $isManual ? 'องค์ความรู้' : 'ผลงานการแข่งขัน' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-1 flex-col p-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $isManual ? 'bg-slate-100 text-slate-600' : 'bg-emerald-50 text-emerald-700' }}"
                                        >
                                            @if ($isManual)
                                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                                </svg>
                                            @else
                                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="M8 21h8"/>
                                                    <path d="M12 17v4"/>
                                                    <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                                    <path d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4"/>
                                                    <path d="M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                                                </svg>
                                            @endif
                                            {{ $sourceType }}
                                        </span>
                                        <p class="min-w-0 line-clamp-1 text-xs font-medium text-slate-500">
                                            {{ $sourceDetail }}
                                        </p>
                                    </div>
                                    <h3
                                        class="mt-1.5 line-clamp-2 leading-6 text-slate-900 transition group-hover:text-emerald-700 text-base font-semibold"
                                    >
                                        {{ $item->title }}
                                    </h3>
                                    @if (! $isManual && $item->summary)
                                        <p
                                            class="mt-1.5 line-clamp-2 leading-5 text-slate-500 text-xs"
                                        >
                                            {{ $item->summary }}
                                        </p>
                                    @endif
                                    <div
                                        class="mt-auto flex items-end justify-between gap-3 border-t border-slate-100 pt-3"
                                    >
                                        <div class="min-w-0">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                                    <path d="M16 3v4M8 3v4M3 10h18"/>
                                                </svg>
                                                เผยแพร่เมื่อ
                                            </span>
                                            <span class="mt-0.5 block text-xs font-semibold text-slate-600">
                                                {{ $item->published_at?->format('d/m/Y') ?? '-' }}
                                            </span>
                                        </div>
                                        @if (! $isManual)
                                            <div class="shrink-0 text-right">
                                                <span class="inline-flex items-center justify-end gap-1 text-[10px] font-medium text-slate-400">
                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>
                                                    </svg>
                                                    คะแนนรวม
                                                </span>
                                                <span
                                                    class="mt-0.5 block text-base font-extrabold tabular-nums text-slate-800"
                                                >
                                                    {{ $submission?->final_score !== null
                                                        ? number_format((float) $submission->final_score, 2)
                                                        : '-' }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
                {{-- Pagination --}}
                @if ($knowledgeItems->hasPages())
                    <div class="mt-4">
                        {{ $knowledgeItems->links() }}
                    </div>
                @endif
            @else
                <div
                    class="km-reveal km-empty-pattern rounded-xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center shadow-sm"
                >
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                    >
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="3"
                                y="4"
                                width="18"
                                height="16"
                                rx="2"
                            />
                            <circle
                                cx="8.5"
                                cy="9"
                                r="1.5"
                            />
                            <path d="m4 17 5-5 4 4 2-2 5 4"/>
                        </svg>
                    </div>
                    <h3
                        class="mt-3 text-slate-700 text-base font-semibold"
                    >
                        ยังไม่มีผลงาน
                    </h3>
                    <p
                        class="mt-1 text-slate-400 text-xs"
                    >
                        ยังไม่มีผลงานที่ตรงกับเงื่อนไขที่ค้นหา
                    </p>
                </div>
            @endif
        </section>
    </main>
            {{-- พื้นที่ว่างด้านขวา --}}
            <aside
                class="hidden lg:block"
                aria-label="พื้นที่ด้านขวา"
            >
            </aside>
        </div>
    </div>
    {{-- =========================================================
        FOOTER
    ========================================================== --}}
    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 text-center sm:px-4 lg:px-4">
            <p class="text-sm font-semibold text-slate-700">
                คลังผลงานการประกวดและองค์ความรู้
            </p>
            <p class="mx-auto mt-1 max-w-2xl leading-6 text-slate-500 text-xs">
                พื้นที่รวบรวมผลงานและองค์ความรู้จากการแข่งขัน
            </p>
        </div>
    </footer>
</div>
@endsection
