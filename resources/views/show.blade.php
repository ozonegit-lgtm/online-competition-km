@extends('layouts.km', ['title' => $knowledgeItem->title])

@section('content')
@php
    $submission = $knowledgeItem->submission;
    $competition = $submission?->competition;
    $isCompetition = $knowledgeItem->submission_id !== null;
    $isTeamCompetition = $isCompetition && $competition?->competition_type === 'team';

    $submissionDetails = collect();
    if ($isCompetition && $submission) {
        $submissionDetails = $submission->fieldValues
            ->sortBy('field.sort_order')
            ->filter(function ($value) {
                $field = $value->field;

                return $field
                    && ! in_array($field->field_type, ['file', 'email', 'phone', 'tel'], true)
                    && ! in_array($field->field_name, ['email', 'phone', 'tel', 'contact_email', 'contact_phone'], true)
                    && ! in_array($field->system_field, ['contact_email', 'contact_phone'], true);
            })
            ->map(function ($value) {
                $decodedValue = json_decode((string) $value->field_value);
                $displayValue = is_array($decodedValue)
                    ? collect($decodedValue)
                        ->filter(fn ($option) => is_scalar($option) && trim((string) $option) !== '')
                        ->implode(', ')
                    : (string) $value->field_value;

                return ['label' => $value->field->label, 'value' => $displayValue];
            })
            ->filter(fn ($detail) => trim($detail['value']) !== '');
    }

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

    if ($isCompetition) {
        $ownerName = $isTeamCompetition
            ? ($submission?->team_name ?: 'ไม่ระบุชื่อทีม')
            : ($submission?->contact_name ?: 'ไม่ระบุชื่อบุคคล');
    } else {
        $ownerName = $knowledgeItem->creator?->username ?: 'ไม่ระบุ';
    }

    $submissionRank = $isCompetition ? (int) ($submission?->rank ?? 0) : 0;
    $isSharedRank = $isCompetition && (bool) ($submission?->is_shared_rank ?? false);

    $rankBadge = match ($submissionRank) {
        1 => [
            'label' => 'อันดับ 1',
            'class' => 'bg-amber-50 text-amber-700',
            'iconClass' => 'bg-amber-400 text-white',
        ],
        2 => [
            'label' => 'อันดับ 2',
            'class' => 'bg-slate-100 text-slate-700',
            'iconClass' => 'bg-slate-400 text-white',
        ],
        3 => [
            'label' => 'อันดับ 3',
            'class' => 'bg-orange-50 text-orange-700',
            'iconClass' => 'bg-orange-400 text-white',
        ],
        default => null,
    };

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
    {{-- =========================================================
        TOPBAR
        Compact แต่ไม่บีบเกินไป และใช้ visual language เดียวกับ index
    ========================================================== --}}
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-14 w-full max-w-7xl items-center justify-between gap-4 px-4">
            <a
                href="{{ route('home') }}"
                class="flex min-w-0 items-center gap-2.5 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2"
            >
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 19.5V8.8a2 2 0 0 1 1.1-1.79l6-3a2 2 0 0 1 1.8 0l6 3A2 2 0 0 1 20 8.8v10.7"/>
                        <path d="M3 20h18"/>
                        <path d="M8 11v5M12 11v5M16 11v5"/>
                    </svg>
                </span>

                <span class="truncate text-sm font-semibold text-slate-800">
                    คลังผลงานและองค์ความรู้
                </span>
            </a>

            <div class="flex shrink-0 items-center gap-1">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex h-9 items-center justify-center rounded-xl px-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                >
                    หน้าแรก
                </a>

                <details class="relative">
                    <summary
                        class="flex h-9 cursor-pointer list-none items-center justify-center gap-1.5 rounded-xl px-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 [&::-webkit-details-marker]:hidden"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <span class="hidden sm:inline">ค้นหา</span>
                    </summary>

                    <div class="absolute right-0 top-11 z-30 w-[min(22rem,calc(100vw-2rem))] rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                        <form method="GET" action="{{ route('home') }}" class="flex gap-2">
                            <input
                                type="search"
                                name="search"
                                placeholder="ค้นหาผลงาน หรือชื่อการแข่งขัน..."
                                class="h-10 min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                            >
                            <button
                                type="submit"
                                class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 px-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20"
                            >
                                ค้นหา
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </header>

    {{-- =========================================================
        SAME 3-COLUMN SYSTEM AS INDEX
    ========================================================== --}}
    <div class="relative mx-auto w-full max-w-7xl px-4 py-5">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[160px_minmax(0,1fr)_160px] xl:grid-cols-[200px_minmax(0,1fr)_200px]">
            <aside class="hidden lg:block" aria-hidden="true"></aside>

            <main class="min-w-0">
                {{-- Breadcrumb --}}
                <nav class="mb-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400" aria-label="Breadcrumb">
                    <div class="flex min-w-0 items-center gap-2">
                        <a href="{{ route('home') }}" class="font-medium transition hover:text-emerald-700">
                            หน้าแรก
                        </a>
                        <span>/</span>
                        <span class="truncate text-slate-500">รายละเอียดผลงาน</span>
                    </div>

                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-emerald-700"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                        กลับไปคลังผลงาน
                    </a>
                </nav>

                {{-- =====================================================
                    MASTER DETAIL CARD
                    ลดทั้งระบบแบบสมดุล ไม่ย่อจน UI จิ๋ว
                ====================================================== --}}
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="p-5 sm:p-6">
                        {{-- Header --}}
                        <header>
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Source / KM type --}}
                                @if ($isCompetition)
                                    <span class="inline-flex h-8 items-center gap-1.5 rounded-full bg-blue-50 px-3 text-xs font-semibold text-blue-700">
                                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M8 21h8"/>
                                            <path d="M12 17v4"/>
                                            <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                            <path d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4"/>
                                            <path d="M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                                        </svg>
                                        <span>Competition KM</span>
                                    </span>
                                @else
                                    <span class="inline-flex h-8 items-center gap-1.5 rounded-full bg-blue-50 px-3 text-xs font-semibold text-blue-700">
                                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                        <span>Manual KM</span>
                                    </span>
                                @endif

                                {{-- Publication status --}}
                                <span class="inline-flex h-8 items-center gap-1.5 rounded-full bg-emerald-50 px-3 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                    <span>เผยแพร่แล้ว</span>
                                </span>

                                {{-- Manual KM content type --}}
                                @if (! $isCompetition)
                                    <span class="inline-flex h-8 items-center rounded-full bg-slate-100 px-3 text-xs font-semibold text-slate-600">
                                        {{ $knowledgeTypeLabel }}
                                    </span>
                                @endif

                                {{-- Featured --}}
                                @if ($knowledgeItem->is_featured)
                                    <span class="inline-flex h-8 items-center gap-1.5 rounded-full bg-amber-50 px-3 text-xs font-semibold text-amber-700">
                                        <svg class="h-3.5 w-3.5 shrink-0 fill-amber-400 text-amber-400" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="m12 2.75 2.75 5.57 6.15.89-4.45 4.34 1.05 6.12L12 16.78l-5.5 2.89 1.05-6.12L3.1 9.21l6.15-.89L12 2.75Z"/>
                                        </svg>
                                        <span>Featured</span>
                                    </span>
                                @endif

                                {{-- Competition rank --}}
                                @if ($rankBadge)
                                    <span class="inline-flex h-8 items-center gap-1.5 rounded-full px-3 text-xs font-semibold {{ $rankBadge['class'] }}">
                                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-black {{ $rankBadge['iconClass'] }}">
                                            {{ $submissionRank }}
                                        </span>
                                        <span>{{ $rankBadge['label'] }}{{ $isSharedRank ? ' ร่วม' : '' }}</span>
                                    </span>
                                @endif
                            </div>

                            @if ($isCompetition && $competition?->title)
                                <p class="mt-3 text-xs font-medium text-emerald-700">
                                    {{ $competition->title }}
                                </p>
                            @endif

                            <h1 class="mt-2 text-2xl font-bold leading-tight tracking-tight text-slate-900 sm:text-3xl">
                                {{ $knowledgeItem->title }}
                            </h1>

                            @if ($knowledgeItem->summary)
                                <p class="mt-3 max-w-3xl whitespace-pre-line text-sm leading-7 text-slate-500">
                                    {{ $knowledgeItem->summary }}
                                </p>
                            @endif

                            <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1.5 border-t border-slate-100 pt-3 text-xs text-slate-400">
                                <span>
                                    {{ $isCompetition ? ($isTeamCompetition ? 'ทีมผู้ส่งผลงาน' : 'ผู้ส่งผลงาน') : 'เผยแพร่โดย' }}
                                    <strong class="font-semibold text-slate-600">{{ $ownerName }}</strong>
                                </span>

                                @if ($publishedAt)
                                    <span class="hidden sm:inline">•</span>
                                    <span>เผยแพร่ {{ $publishedAt }}</span>
                                @endif

                                @if ($updatedAt && $updatedAt !== $publishedAt)
                                    <span class="hidden sm:inline">•</span>
                                    <span>ปรับปรุงล่าสุด {{ $updatedAt }}</span>
                                @endif
                            </div>

                            @if ($knowledgeItem->cover_image_url)
                                <figure class="mt-5">
                                    <div class="flex w-full items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3 sm:p-4">
                                        <img
                                            src="{{ $knowledgeItem->cover_image_url }}"
                                            alt="{{ $knowledgeItem->title }}"
                                            class="block max-h-[460px] w-auto max-w-full rounded-lg object-contain"
                                        >
                                    </div>
                                </figure>
                            @endif
                        </header>

                        {{-- =====================================================
                            SECTION: DETAILS
                        ====================================================== --}}
                        <section class="mt-6 border-t border-slate-100 pt-5">
                            <div class="mb-4">
                                <p class="text-xs font-medium text-emerald-600">เนื้อหาหลัก</p>
                                <h2 class="mt-1 text-lg font-semibold text-slate-900">รายละเอียดผลงาน</h2>
                            </div>

                            @if ($isCompetition)
                                @if ($submission?->project_description)
                                    <div class="whitespace-pre-line text-sm leading-7 text-slate-600">
                                        {{ $submission->project_description }}
                                    </div>
                                @elseif ($knowledgeItem->content)
                                    <div class="whitespace-pre-line text-sm leading-7 text-slate-600">
                                        {{ $knowledgeItem->content }}
                                    </div>
                                @elseif ($submissionDetails->isEmpty())
                                    <p class="text-sm text-slate-400">ไม่มีรายละเอียดเพิ่มเติม</p>
                                @endif

                                @if ($submissionDetails->isNotEmpty())
                                    <div class="mt-4 divide-y divide-slate-100 border-t border-slate-100">
                                        @foreach ($submissionDetails as $detail)
                                            <div class="py-3.5 first:pt-4 last:pb-0">
                                                <p class="text-xs font-medium text-slate-400">
                                                    {{ $detail['label'] }}
                                                </p>
                                                <p class="mt-1 whitespace-pre-line break-words text-sm leading-7 text-slate-700">
                                                    {{ $detail['value'] }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($submittedAt)
                                    <p class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-400">
                                        วันที่ส่งผลงาน
                                        <span class="font-medium text-slate-600">{{ $submittedAt }}</span>
                                    </p>
                                @endif
                            @else
                                @if ($knowledgeItem->content)
                                    <div class="whitespace-pre-line text-sm leading-7 text-slate-600">
                                        {{ $knowledgeItem->content }}
                                    </div>
                                @elseif ($knowledgeItem->summary)
                                    <div class="whitespace-pre-line text-sm leading-7 text-slate-600">
                                        {{ $knowledgeItem->summary }}
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400">ไม่มีรายละเอียดเพิ่มเติม</p>
                                @endif
                            @endif
                        </section>

                        @if ($isCompetition)
                            {{-- =================================================
                                SECTION: COMPETITION
                            ================================================== --}}
                            <section class="mt-6 border-t border-slate-100 pt-5">
                                <div class="mb-4">
                                    <p class="text-xs font-medium text-emerald-600">การแข่งขัน</p>
                                    <h2 class="mt-1 text-lg font-semibold text-slate-900">ข้อมูลการแข่งขัน</h2>
                                </div>

                                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="sm:col-span-2 lg:col-span-1">
                                        <dt class="text-xs font-medium text-slate-400">ชื่อการแข่งขัน</dt>
                                        <dd class="mt-1 text-sm font-semibold leading-6 text-slate-700">
                                            {{ $competition?->title ?? '-' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs font-medium text-slate-400">หมวดหมู่</dt>
                                        <dd class="mt-1 text-sm font-semibold text-slate-700">
                                            {{ $competition?->category?->category_name ?? '-' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs font-medium text-slate-400">ประเภทการแข่งขัน</dt>
                                        <dd class="mt-1 text-sm font-semibold text-slate-700">
                                            @if ($competition?->competition_type)
                                                {{ $isTeamCompetition ? 'ประเภททีม' : 'ประเภทบุคคล' }}
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </div>

                                    @if ($submission?->final_score !== null)
                                        <div>
                                            <dt class="text-xs font-medium text-slate-400">คะแนนรวม</dt>
                                            <dd class="mt-1 text-sm font-extrabold tabular-nums text-emerald-700">
                                                {{ number_format((float) $submission->final_score, 2) }}
                                            </dd>
                                        </div>
                                    @endif
                                </dl>

                                @if ($competition?->description)
                                    <p class="mt-4 whitespace-pre-line border-t border-slate-100 pt-3 text-xs leading-6 text-slate-500">
                                        {{ $competition->description }}
                                    </p>
                                @endif
                            </section>

                            {{-- =================================================
                                SECTION: AUTHOR / TEAM
                            ================================================== --}}
                            <section class="mt-6 border-t border-slate-100 pt-5">
                                <div class="mb-4">
                                    <p class="text-xs font-medium text-emerald-600">ผู้จัดทำ</p>
                                    <h2 class="mt-1 text-lg font-semibold text-slate-900">
                                        {{ $isTeamCompetition ? 'ทีมและผู้จัดทำ' : 'ผู้จัดทำผลงาน' }}
                                    </h2>
                                </div>

                                @if ($isTeamCompetition)
                                    <div class="mb-4 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <p class="text-xs font-medium text-slate-400">ชื่อทีมผู้ส่งผลงาน</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-800">
                                                {{ $submission?->team_name ?: 'ไม่ระบุชื่อทีม' }}
                                            </p>
                                        </div>

                                        @if ($submission?->contact_name)
                                            <div>
                                                <p class="text-xs font-medium text-slate-400">ผู้ส่งผลงาน / ผู้ประสานงาน</p>
                                                <p class="mt-1 text-sm font-semibold text-slate-800">
                                                    {{ $submission->contact_name }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($submission?->members?->isNotEmpty())
                                        <div class="overflow-hidden rounded-xl border border-slate-200">
                                            <div class="flex items-center justify-between bg-slate-50 px-4 py-2.5">
                                                <p class="text-xs font-semibold text-slate-600">สมาชิกในทีม</p>
                                                <span class="text-xs text-slate-400">{{ $submission->members->count() }} คน</span>
                                            </div>

                                            <div class="divide-y divide-slate-100">
                                                @foreach ($submission->members as $member)
                                                    <div class="flex flex-col gap-1.5 px-4 py-3 sm:flex-row sm:items-start sm:justify-between">
                                                        <div class="min-w-0">
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                <p class="text-sm font-semibold text-slate-800">
                                                                    {{ $member->fullname }}
                                                                </p>

                                                                @if ($member->is_team_leader)
                                                                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                                                        หัวหน้าทีม
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            @if ($member->organization)
                                                                <p class="mt-1 text-xs leading-5 text-slate-400">
                                                                    {{ $member->organization }}
                                                                </p>
                                                            @endif
                                                        </div>

                                                        @if ($member->position)
                                                            <span class="text-xs text-slate-400">
                                                                {{ $member->position }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div>
                                        <p class="text-xs font-medium text-slate-400">ชื่อผู้ส่งผลงาน</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-800">
                                            {{ $submission?->contact_name ?: 'ไม่ระบุชื่อบุคคล' }}
                                        </p>
                                    </div>
                                @endif
                            </section>
                        @endif

                        {{-- =====================================================
                            SECTION: FILES
                        ====================================================== --}}
                        <section class="mt-6 border-t border-slate-100 pt-5">
                            <div class="mb-4">
                                <p class="text-xs font-medium text-emerald-600">เอกสาร</p>
                                <h2 class="mt-1 text-lg font-semibold text-slate-900">เอกสารและไฟล์แนบ</h2>
                            </div>

                            @if ($isCompetition)
                                @if ($submission?->files?->isNotEmpty())
                                    <div class="overflow-hidden rounded-xl border border-slate-200">
                                        <div class="divide-y divide-slate-100">
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

                                                <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-slate-700" title="{{ $file->original_name }}">
                                                            {{ $file->original_name }}
                                                        </p>
                                                        <p class="mt-0.5 text-xs text-slate-400">
                                                            {{ $extension ?: 'FILE' }} · {{ $sizeLabel }}
                                                        </p>
                                                    </div>

                                                    <div class="flex shrink-0 gap-2 self-end sm:self-center">
                                                        <a
                                                            href="{{ $file->file_url }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                                        >
                                                            เปิดดู
                                                        </a>

                                                        <a
                                                            href="{{ $file->download_url }}"
                                                            class="inline-flex h-9 items-center justify-center rounded-xl bg-emerald-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20"
                                                        >
                                                            ดาวน์โหลด
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400">ไม่มีไฟล์แนบในผลงานนี้</p>
                                @endif
                            @else
                                @if ($knowledgeItem->attachment_path || $externalUrl)
                                    <div class="overflow-hidden rounded-xl border border-slate-200">
                                        <div class="divide-y divide-slate-100">
                                            @if ($knowledgeItem->attachment_path)
                                                <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-slate-700">
                                                            {{ $knowledgeItem->attachment_original_name ?: 'ไฟล์แนบองค์ความรู้' }}
                                                        </p>
                                                        <p class="mt-0.5 text-xs text-slate-400">ไฟล์แนบองค์ความรู้</p>
                                                    </div>

                                                    <a
                                                        href="{{ route('knowledge-items.attachment', $knowledgeItem) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex h-9 shrink-0 items-center justify-center self-end rounded-xl bg-emerald-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 sm:self-center"
                                                    >
                                                        เปิดไฟล์
                                                    </a>
                                                </div>
                                            @endif

                                            @if ($externalUrl)
                                                <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-semibold text-slate-700">ลิงก์ภายนอก</p>
                                                        <p class="mt-0.5 break-all text-xs text-slate-400">{{ $externalUrl }}</p>
                                                    </div>

                                                    <a
                                                        href="{{ $externalUrl }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex h-9 shrink-0 items-center justify-center self-end rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700 sm:self-center"
                                                    >
                                                        เปิดลิงก์
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400">ไม่มีไฟล์หรือลิงก์แนบ</p>
                                @endif
                            @endif
                        </section>

                        <footer class="mt-6 flex items-center justify-center border-t border-slate-100 pt-4">
                            <a
                                href="{{ route('home') }}"
                                class="inline-flex h-9 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m15 18-6-6 6-6"/>
                                </svg>
                                กลับไปคลังผลงานทั้งหมด
                            </a>
                        </footer>
                    </div>
                </article>
            </main>

            <aside class="hidden lg:block" aria-hidden="true"></aside>
        </div>
    </div>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 text-center">
            <p class="text-sm font-semibold text-slate-700">
                คลังผลงานการประกวดและองค์ความรู้
            </p>
            <p class="mx-auto mt-1 max-w-2xl text-xs leading-6 text-slate-500">
                พื้นที่รวบรวมผลงานและองค์ความรู้จากการแข่งขัน
            </p>
        </div>
    </footer>
</div>
@endsection