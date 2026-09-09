@extends('layouts.km', ['title' => $knowledgeItem->title])

@section('content')
@php
    $submission = $knowledgeItem->submission;
    $competition = $submission?->competition;
    $isCompetition = $knowledgeItem->submission_id !== null;

    $typeLabels = [
        'article' => 'บทความ',
        'document' => 'เอกสาร',
        'research' => 'งานวิจัย',
        'image' => 'รูปภาพ',
        'video' => 'วิดีโอ',
        'link' => 'ลิงก์',
        'other' => 'อื่น ๆ',
    ];

    $knowledgeType = $knowledgeItem->knowledge_type ?? 'article';
    $knowledgeTypeLabel = $typeLabels[$knowledgeType] ?? $knowledgeType;

    $publishedAt = $knowledgeItem->published_at?->format('d/m/Y H:i');
    $updatedAt = $knowledgeItem->updated_at?->format('d/m/Y H:i');
    $submittedAt = $submission?->submitted_at?->format('d/m/Y H:i');

    $ownerName = $isCompetition
        ? ($submission?->contact_name ?: ($submission?->team_name ?: 'ไม่ระบุ'))
        : ($knowledgeItem->creator?->username ?: 'ไม่ระบุ');

    $externalUrl = null;
    if (! empty($knowledgeItem->external_url)) {
        $candidateUrl = trim((string) $knowledgeItem->external_url);
        $scheme = strtolower((string) parse_url($candidateUrl, PHP_URL_SCHEME));

        if (
            filter_var($candidateUrl, FILTER_VALIDATE_URL)
            && in_array($scheme, ['http', 'https'], true)
        ) {
            $externalUrl = $candidateUrl;
        }
    }
@endphp

<div class="min-h-screen bg-slate-50 text-slate-800">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a
                href="{{ route('home') }}"
                class="flex min-w-0 items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 19.5V8.8a2 2 0 0 1 1.1-1.79l6-3a2 2 0 0 1 1.8 0l6 3A2 2 0 0 1 20 8.8v10.7"/>
                        <path d="M3 20h18"/>
                        <path d="M8 11v5"/>
                        <path d="M12 11v5"/>
                        <path d="M16 11v5"/>
                    </svg>
                </span>
                <span class="truncate text-sm font-bold text-slate-900 sm:text-base">
                    คลังผลงานและองค์ความรู้
                </span>
            </a>

            <div class="flex shrink-0 items-center gap-1 sm:gap-2">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex h-9 items-center justify-center rounded-lg px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                >
                    หน้าแรก
                </a>

                <details class="relative">
                    <summary class="flex h-9 cursor-pointer list-none items-center gap-2 rounded-lg px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 [&::-webkit-details-marker]:hidden">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m20 20-3.5-3.5"/>
                        </svg>
                        <span class="hidden sm:inline">ค้นหา</span>
                    </summary>

                    <div class="absolute right-0 top-11 z-50 w-[min(22rem,calc(100vw-2rem))] rounded-xl border border-slate-200 bg-white p-3 shadow-lg">
                        <form method="GET" action="{{ route('home') }}" class="flex gap-2">
                            <input
                                type="search"
                                name="search"
                                placeholder="ค้นหาผลงานหรือองค์ความรู้..."
                                class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10"
                            >
                            <button
                                type="submit"
                                class="inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-emerald-600 px-3 text-sm font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
                            >
                                ค้นหา
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(32px,1fr)_minmax(0,920px)_minmax(32px,1fr)] lg:gap-8">
            <div class="hidden lg:block" aria-hidden="true"></div>

            <main class="min-w-0">
                <nav class="mb-5 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400" aria-label="Breadcrumb">
                    <div class="flex min-w-0 items-center gap-2">
                        <a href="{{ route('home') }}" class="font-medium transition hover:text-emerald-700">
                            หน้าแรก
                        </a>
                        <span>/</span>
                        <span class="font-medium text-slate-500">รายละเอียด</span>
                    </div>

                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center gap-1.5 font-semibold text-slate-500 transition hover:text-emerald-700"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                        กลับไปคลังผลงาน
                    </a>
                </nav>

                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-5 sm:p-7 lg:p-9">
                        <header>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                    {{ $isCompetition ? 'ผลงานจากการแข่งขัน' : 'องค์ความรู้' }}
                                </span>

                                @if (! $isCompetition)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ $knowledgeTypeLabel }}
                                    </span>
                                @endif

                                @if ($knowledgeItem->is_featured)
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-200">
                                        ★ ผลงานแนะนำ
                                    </span>
                                @endif

                                @if ($isCompetition && $competition?->title)
                                    <span class="inline-flex max-w-full items-center truncate rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-500 ring-1 ring-inset ring-slate-200">
                                        {{ $competition->title }}
                                    </span>
                                @endif
                            </div>

                            <h1 class="mt-4 text-2xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-3xl lg:text-4xl">
                                {{ $knowledgeItem->title }}
                            </h1>

                            @if ($knowledgeItem->summary)
                                <p class="mt-3 max-w-3xl whitespace-pre-line text-base leading-8 text-slate-600 sm:text-lg">
                                    {{ $knowledgeItem->summary }}
                                </p>
                            @endif

                            <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl bg-slate-50 px-4 py-3 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <circle cx="12" cy="8" r="4"/>
                                        <path d="M4 21a8 8 0 0 1 16 0"/>
                                    </svg>
                                    {{ $isCompetition ? 'เจ้าของผลงาน' : 'เผยแพร่โดย' }}:
                                    <strong class="font-semibold text-slate-700">{{ $ownerName }}</strong>
                                </span>

                                @if ($publishedAt)
                                    <span class="hidden text-slate-300 sm:inline">•</span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                                            <path d="M16 3v4M8 3v4M3 11h18"/>
                                        </svg>
                                        เผยแพร่ {{ $publishedAt }}
                                    </span>
                                @endif

                                @if ($updatedAt && $updatedAt !== $publishedAt)
                                    <span class="hidden text-slate-300 sm:inline">•</span>
                                    <span>ปรับปรุงล่าสุด {{ $updatedAt }}</span>
                                @endif
                            </div>

                            @if ($knowledgeItem->cover_image_url)
                                <figure class="mt-6">
                                    <div class="relative aspect-[16/9] max-h-[500px] overflow-hidden rounded-xl bg-slate-100">
                                        <img
                                            src="{{ $knowledgeItem->cover_image_url }}"
                                            alt="{{ $knowledgeItem->title }}"
                                            class="h-full w-full object-cover"
                                        >
                                    </div>
                                </figure>
                            @endif
                        </header>

                        <section class="mt-8 border-t border-slate-100 pt-7">
                            <div class="mb-4 flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M4 4h16v16H4z"/>
                                        <path d="M8 8h8M8 12h8M8 16h5"/>
                                    </svg>
                                </span>
                                <h2 class="text-lg font-bold text-slate-900">รายละเอียดผลงาน</h2>
                            </div>

                            @if ($isCompetition)
                                @if ($submission?->project_description)
                                    <div class="whitespace-pre-line text-sm leading-8 text-slate-700 sm:text-base">
                                        {{ $submission->project_description }}
                                    </div>
                                @elseif ($knowledgeItem->content)
                                    <div class="whitespace-pre-line text-sm leading-8 text-slate-700 sm:text-base">
                                        {{ $knowledgeItem->content }}
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400">ไม่มีรายละเอียดเพิ่มเติม</p>
                                @endif

                                @if ($submittedAt)
                                    <div class="mt-5 inline-flex items-center gap-2 text-xs text-slate-500">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                                            <path d="M16 3v4M8 3v4M3 11h18"/>
                                        </svg>
                                        วันที่ส่งผลงาน:
                                        <strong class="font-semibold text-slate-700">{{ $submittedAt }}</strong>
                                    </div>
                                @endif
                            @else
                                @if ($knowledgeItem->content)
                                    <div class="whitespace-pre-line text-sm leading-8 text-slate-700 sm:text-base">
                                        {{ $knowledgeItem->content }}
                                    </div>
                                @elseif ($knowledgeItem->summary)
                                    <div class="whitespace-pre-line text-sm leading-8 text-slate-700 sm:text-base">
                                        {{ $knowledgeItem->summary }}
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400">ไม่มีรายละเอียดเพิ่มเติม</p>
                                @endif
                            @endif
                        </section>

                        @if ($isCompetition)
                            <section class="mt-8 border-t border-slate-100 pt-7">
                                <div class="mb-4 flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M8 21h8"/>
                                            <path d="M12 17v4"/>
                                            <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                            <path d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                                        </svg>
                                    </span>
                                    <h2 class="text-lg font-bold text-slate-900">ข้อมูลการแข่งขัน</h2>
                                </div>

                                <div class="rounded-xl bg-slate-50 p-4 sm:p-5">
                                    <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
                                        <div class="sm:col-span-2 lg:col-span-1">
                                            <dt class="text-xs font-medium text-slate-400">ชื่อการแข่งขัน</dt>
                                            <dd class="mt-1 text-sm font-semibold leading-6 text-slate-800">
                                                {{ $competition?->title ?? '-' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-slate-400">หมวดหมู่</dt>
                                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                                {{ $competition?->category?->category_name ?? '-' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-slate-400">ประเภทการแข่งขัน</dt>
                                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                                @if ($competition?->competition_type)
                                                    {{ $competition->competition_type === 'team' ? 'ประเภททีม' : 'ประเภทบุคคล' }}
                                                @else
                                                    -
                                                @endif
                                            </dd>
                                        </div>

                                        @if ($submission?->final_score !== null)
                                            <div>
                                                <dt class="text-xs font-medium text-slate-400">คะแนนรวม</dt>
                                                <dd class="mt-1 text-xl font-black tabular-nums text-emerald-700">
                                                    {{ number_format((float) $submission->final_score, 2) }}
                                                </dd>
                                            </div>
                                        @endif
                                    </dl>

                                    @if ($competition?->description)
                                        <div class="mt-5 border-t border-slate-200 pt-4">
                                            <p class="whitespace-pre-line text-sm leading-7 text-slate-600">
                                                {{ $competition->description }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </section>

                            <section class="mt-8 border-t border-slate-100 pt-7">
                                <div class="mb-4 flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        </svg>
                                    </span>
                                    <h2 class="text-lg font-bold text-slate-900">ทีมและผู้จัดทำ</h2>
                                </div>

                                @if ($submission?->team_name || $submission?->contact_name)
                                    <div class="mb-4 flex flex-col gap-4 rounded-xl bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-xs font-medium text-slate-400">ชื่อทีมผู้ส่งผลงาน</p>
                                            <p class="mt-1 text-sm font-bold text-slate-900">
                                                {{ $submission?->team_name ?: 'ไม่ระบุชื่อทีม' }}
                                            </p>
                                        </div>

                                        <div class="sm:text-right">
                                            <p class="text-xs font-medium text-slate-400">ผู้ส่งผลงาน</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                                {{ $submission?->contact_name ?: '-' }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if ($submission?->members?->isNotEmpty())
                                    <div class="divide-y divide-slate-100">
                                        @foreach ($submission->members as $member)
                                            <div class="flex flex-col gap-2 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <circle cx="12" cy="8" r="4"/>
                                                            <path d="M4 21a8 8 0 0 1 16 0"/>
                                                        </svg>
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <p class="font-semibold text-slate-900">{{ $member->fullname }}</p>

                                                            @if ($member->is_team_leader)
                                                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                                                    หัวหน้าทีม
                                                                </span>
                                                            @endif
                                                        </div>

                                                        @if ($member->organization || $member->position)
                                                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                                                {{ $member->organization ?? '' }}
                                                                @if ($member->organization && $member->position)
                                                                    •
                                                                @endif
                                                                {{ $member->position ?? '' }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400">ไม่มีข้อมูลสมาชิกในทีม</p>
                                @endif
                            </section>
                        @endif

                        <section class="mt-8 border-t border-slate-100 pt-7">
                            <div class="mb-4 flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M21.44 11.05 12.25 20.24a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                                    </svg>
                                </span>
                                <h2 class="text-lg font-bold text-slate-900">เอกสารและไฟล์แนบ</h2>
                            </div>

                            @if ($isCompetition)
                                @if ($submission?->files?->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach ($submission->files as $file)
                                            @php
                                                $bytes = (int) ($file->file_size ?? 0);
                                                $sizeLabel = $bytes >= 1024 * 1024
                                                    ? number_format($bytes / (1024 * 1024), 2) . ' MB'
                                                    : ($bytes >= 1024
                                                        ? number_format($bytes / 1024, 1) . ' KB'
                                                        : $bytes . ' B');

                                                $extension = strtoupper(
                                                    (string) (
                                                        $file->file_extension
                                                        ?: pathinfo((string) $file->original_name, PATHINFO_EXTENSION)
                                                    )
                                                );
                                            @endphp

                                            <div class="flex flex-col gap-3 rounded-xl bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-emerald-700 shadow-sm ring-1 ring-slate-200">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                                                            <path d="M14 2v6h6"/>
                                                        </svg>
                                                    </div>

                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-slate-900" title="{{ $file->original_name }}">
                                                            {{ $file->original_name }}
                                                        </p>
                                                        <p class="mt-1 text-xs text-slate-400">
                                                            {{ $extension ?: 'FILE' }} • {{ $sizeLabel }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="flex shrink-0 gap-2 self-end sm:self-center">
                                                    <a
                                                        href="{{ $file->file_url }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex h-9 items-center justify-center rounded-lg bg-white px-3 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100"
                                                    >
                                                        เปิดดู
                                                    </a>

                                                    <a
                                                        href="{{ $file->download_url }}"
                                                        class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-3 text-xs font-bold text-white transition hover:bg-emerald-700"
                                                    >
                                                        ดาวน์โหลด
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400">ไม่มีไฟล์แนบในผลงานนี้</p>
                                @endif
                            @else
                                <div class="space-y-2">
                                    @if ($knowledgeItem->attachment_path)
                                        <div class="flex flex-col gap-3 rounded-xl bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex min-w-0 items-center gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-emerald-700 shadow-sm ring-1 ring-slate-200">
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                                                        <path d="M14 2v6h6"/>
                                                    </svg>
                                                </div>

                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-slate-900">
                                                        {{ $knowledgeItem->attachment_original_name ?: 'ไฟล์แนบองค์ความรู้' }}
                                                    </p>
                                                    <p class="mt-1 text-xs text-slate-400">เอกสารแนบ</p>
                                                </div>
                                            </div>

                                            <a
                                                href="{{ route('knowledge-items.attachment', $knowledgeItem) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg bg-emerald-600 px-3 text-xs font-bold text-white transition hover:bg-emerald-700"
                                            >
                                                เปิดไฟล์
                                            </a>
                                        </div>
                                    @endif

                                    @if ($externalUrl)
                                        <div class="flex flex-col gap-3 rounded-xl bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-900">ลิงก์ภายนอก</p>
                                                <p class="mt-1 break-all text-xs text-slate-400">{{ $externalUrl }}</p>
                                            </div>

                                            <a
                                                href="{{ $externalUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg bg-white px-3 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100"
                                            >
                                                เปิดลิงก์
                                            </a>
                                        </div>
                                    @endif

                                    @if (! $knowledgeItem->attachment_path && ! $externalUrl)
                                        <p class="text-sm text-slate-400">ไม่มีไฟล์หรือลิงก์แนบ</p>
                                    @endif
                                </div>
                            @endif
                        </section>

                        <footer class="mt-8 flex flex-col items-center border-t border-slate-100 pt-7 text-center">
                            <a
                                href="{{ route('home') }}"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-100 px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-200 hover:text-emerald-700"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m15 18-6-6 6-6"/>
                                </svg>
                                กลับไปคลังผลงานทั้งหมด
                            </a>

                            <p class="mt-3 text-xs text-slate-400">
                                คลังผลงานและองค์ความรู้เพื่อการเรียนรู้และต่อยอด
                            </p>
                        </footer>
                    </div>
                </article>
            </main>

            <div class="hidden lg:block" aria-hidden="true"></div>
        </div>
    </div>
</div>
@endsection
