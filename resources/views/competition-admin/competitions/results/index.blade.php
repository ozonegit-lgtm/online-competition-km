@extends('layouts.app')

@section('title', 'ผลการแข่งขัน')

@section('header')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                <span>ศูนย์ผลการแข่งขัน</span>
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
                <span class="truncate">{{ $competition->title }}</span>
            </div>

            <h1 class="mt-1 text-xl font-bold text-slate-900">
                ผลการแข่งขัน
            </h1>
        </div>

        <a
            href="{{ route('competition-admin.results.index') }}"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            กลับหน้ารวม
        </a>
    </div>
@endsection

@section('content')
    @php
        $topSubmissions = $rankedSubmissions
            ->filter(
                fn ($submission) =>
                    (int) $submission->rank <= 3
            )
            ->values();

        $rankGroups = $topSubmissions->groupBy(
            fn ($submission) => (int) $submission->rank
        );

        $hasSharedRank = $rankGroups->contains(
            fn ($group) => $group->count() > 1
        );

        $sessionStatus = $session?->status ?? 'waiting';
        $resultsPublished = (bool) $competition->publish_scores;

        $publishRouteReady = Route::has(
            'competition-admin.competitions.results.publish'
        );

        $unpublishRouteReady = Route::has(
            'competition-admin.competitions.results.unpublish'
        );

        $completionPercent = $totalSubmissions > 0
            ? min(
                100,
                (int) round(
                    ($completedSubmissionCount / $totalSubmissions) * 100
                )
            )
            : 0;

        $sessionConfig = match ($sessionStatus) {
            'ended' => [
                'label' => 'จบการตัดสินแล้ว',
                'dot' => 'bg-emerald-400',
                'class' => 'bg-emerald-400/10 text-emerald-100 ring-emerald-300/20',
            ],
            'closed' => [
                'label' => 'ปิดห้องแล้ว',
                'dot' => 'bg-slate-300',
                'class' => 'bg-white/10 text-slate-100 ring-white/15',
            ],
            'live' => [
                'label' => 'กำลัง Live',
                'dot' => 'bg-red-400',
                'class' => 'bg-red-400/10 text-red-100 ring-red-300/20',
            ],
            'paused' => [
                'label' => 'หยุดชั่วคราว',
                'dot' => 'bg-amber-400',
                'class' => 'bg-amber-400/10 text-amber-100 ring-amber-300/20',
            ],
            default => [
                'label' => 'รอเริ่มตัดสิน',
                'dot' => 'bg-blue-300',
                'class' => 'bg-blue-400/10 text-blue-100 ring-blue-300/20',
            ],
        };
    @endphp

    <div class="mx-auto w-full max-w-6xl space-y-4">
        {{-- Competition overview --}}
        <section
            id="result-publication-panel"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="relative overflow-hidden bg-gradient-to-r from-[#071a3d] via-[#0b2b63] to-[#0b4f6c] px-5 py-5 text-white sm:px-6">
                <div class="pointer-events-none absolute -right-12 -top-16 h-40 w-40 rounded-full bg-white/5"></div>
                <div class="pointer-events-none absolute -bottom-20 right-24 h-44 w-44 rounded-full border border-white/10"></div>

                <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15">
                            <svg class="h-6 w-6 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M18.75 4.236c.982.143 1.954.317 2.916.52a6.003 6.003 0 0 1-5.397 4.972M12 2.25c-1.58 0-3.12.13-4.625.379A11.953 11.953 0 0 0 12 11.25a11.953 11.953 0 0 0 4.625-8.621A28.423 28.423 0 0 0 12 2.25Z" />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-200">
                                Competition Results
                            </p>
                            <h2 class="mt-1 truncate text-lg font-bold sm:text-xl">
                                {{ $competition->title }}
                            </h2>
                            <p class="mt-1 text-xs text-slate-300">
                                {{ $competition->category?->category_name ?? 'ไม่ระบุหมวดหมู่' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col items-start gap-2 lg:items-end">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 {{ $sessionConfig['class'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $sessionConfig['dot'] }}"></span>
                                {{ $sessionConfig['label'] }}
                            </span>

                            @if ($resultsPublished)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-3 py-1.5 text-xs font-semibold text-emerald-100 ring-1 ring-emerald-300/20">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                        <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.18A10.01 10.01 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.38.147.8 0 1.18A10.01 10.01 0 0 1 10 17C5.743 17 2.107 14.34.664 10.59ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                                    </svg>
                                    เผยแพร่ผลการแข่งขันแล้ว
                                </span>
                            @endif
                        </div>

                        @if ($isReadyForResults)
                            @if ($resultsPublished)
                                <x-ajax-form
                                    :action="$unpublishRouteReady
                                        ? route('competition-admin.competitions.results.unpublish', $competition)
                                        : '#'"
                                    method="DELETE"
                                    confirm="ยืนยันถอนประกาศผลการแข่งขันนี้จากหน้า Public หรือไม่?"
                                    success="ถอนประกาศผลการแข่งขันเรียบร้อยแล้ว"
                                    loading="กำลังถอนประกาศ..."
                                    target="#result-publication-panel"
                                >
                                    <button
                                        type="submit"
                                        @disabled(! $unpublishRouteReady)
                                        class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-white/20 bg-white/10 px-3 text-xs font-bold text-white transition hover:bg-white/20 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m3.98 8.223 2.806 2.806M21 21 3 3m9.753 9.752a3 3 0 0 1-4.506-4.504M9.88 4.24A9.72 9.72 0 0 1 12 4c4.257 0 7.893 2.66 9.336 6.41a1.65 1.65 0 0 1 0 1.18 10.01 10.01 0 0 1-2.091 3.368M6.228 6.228a10.04 10.04 0 0 0-3.564 4.182 1.65 1.65 0 0 0 0 1.18A10.01 10.01 0 0 0 12 18c.757 0 1.494-.084 2.203-.242" />
                                        </svg>
                                        <span data-ajax-submit-label>
                                            ถอนประกาศผล
                                        </span>
                                    </button>
                                </x-ajax-form>
                            @else
                                <x-ajax-form
                                    :action="$publishRouteReady
                                        ? route('competition-admin.competitions.results.publish', $competition)
                                        : '#'"
                                    method="POST"
                                    confirm="ยืนยันเผยแพร่ผลอันดับ 1–3 บนหน้า Public หรือไม่?"
                                    success="เผยแพร่ผลการแข่งขันเรียบร้อยแล้ว"
                                    loading="กำลังเผยแพร่..."
                                    target="#result-publication-panel"
                                >
                                    <button
                                        type="submit"
                                        @disabled(! $publishRouteReady)
                                        class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-emerald-400 px-3.5 text-xs font-bold text-emerald-950 shadow-sm transition hover:bg-emerald-300 disabled:cursor-not-allowed disabled:bg-slate-400 disabled:text-white"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <span data-ajax-submit-label>
                                            เผยแพร่ผลการแข่งขัน
                                        </span>
                                    </button>
                                </x-ajax-form>
                            @endif
                        @else
                            <span class="text-xs font-medium text-amber-200">
                                ต้องให้คะแนนครบก่อนจึงจะเผยแพร่ได้
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 divide-x divide-y divide-slate-100 sm:grid-cols-4 sm:divide-y-0">
                <div class="px-4 py-3">
                    <p class="text-[11px] font-medium text-slate-500">ผลงานทั้งหมด</p>
                    <div class="mt-1 flex items-baseline gap-1">
                        <span class="text-lg font-black text-slate-900">{{ $totalSubmissions }}</span>
                        <span class="text-[11px] text-slate-400">ผลงาน</span>
                    </div>
                </div>

                <div class="px-4 py-3">
                    <p class="text-[11px] font-medium text-slate-500">คะแนนครบแล้ว</p>
                    <div class="mt-1 flex items-baseline gap-1">
                        <span class="text-lg font-black text-emerald-600">{{ $completedSubmissionCount }}</span>
                        <span class="text-[11px] text-slate-400">/ {{ $totalSubmissions }}</span>
                    </div>
                </div>

                <div class="px-4 py-3">
                    <p class="text-[11px] font-medium text-slate-500">เกณฑ์ที่ใช้งาน</p>
                    <div class="mt-1 flex items-baseline gap-1">
                        <span class="text-lg font-black text-slate-900">{{ $activeRubricCount }}</span>
                        <span class="text-[11px] text-slate-400">เกณฑ์</span>
                    </div>
                </div>

                <div class="px-4 py-3">
                    <p class="text-[11px] font-medium text-slate-500">กรรมการตอบรับ</p>
                    <div class="mt-1 flex items-baseline gap-1">
                        <span class="text-lg font-black text-slate-900">{{ $acceptedJudgeCount }}</span>
                        <span class="text-[11px] text-slate-400">คน</span>
                    </div>
                </div>
            </div>
        </section>

        @if ($isReadyForResults)
            {{-- Ranking podium --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600 ring-1 ring-amber-200">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497M5.25 4.236a6.003 6.003 0 0 0 2.48 5.492M18.75 4.236a6.003 6.003 0 0 1-2.481 5.492M12 2.25c-1.58 0-3.12.13-4.625.379A11.953 11.953 0 0 0 12 11.25a11.953 11.953 0 0 0 4.625-8.621A28.423 28.423 0 0 0 12 2.25Z" />
                                </svg>
                            </span>
                            <h2 class="font-bold text-slate-900">ผลงานอันดับ 1–3</h2>
                        </div>
                        <p class="mt-1 pl-9 text-xs text-slate-500">
                            คะแนนที่ยืนยันครบจากกรรมการที่ตอบรับเท่านั้น
                        </p>
                    </div>

                    @if ($hasSharedRank)
                        <span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                            มีอันดับร่วม
                        </span>
                    @endif
                </div>

                <div class="relative overflow-hidden bg-slate-50/80 px-4 pb-5 pt-8 sm:px-6 sm:pb-6 sm:pt-10">
                    <div class="pointer-events-none absolute left-1/2 top-6 h-40 w-40 -translate-x-1/2 rounded-full bg-amber-200/20 blur-3xl"></div>

                    <div class="relative mx-auto grid max-w-4xl gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:items-end">
                        @forelse ($topSubmissions as $submission)
                            @php
                                $rank = (int) $submission->rank;

                                $isShared = (
                                    $rankGroups->get($rank)?->count() ?? 0
                                ) > 1;

                                $rankConfig = match ($rank) {
                                    1 => [
                                        'label' => 'อันดับ 1',
                                        'accent' => 'from-amber-300 via-yellow-400 to-amber-500',
                                        'border' => 'border-amber-300',
                                        'number' => 'bg-amber-400 text-amber-950 ring-amber-200',
                                        'score' => 'text-amber-600',
                                        'soft' => 'bg-amber-50 text-amber-700',
                                    ],
                                    2 => [
                                        'label' => 'อันดับ 2',
                                        'accent' => 'from-slate-300 via-slate-200 to-slate-400',
                                        'border' => 'border-slate-300',
                                        'number' => 'bg-slate-300 text-slate-800 ring-slate-100',
                                        'score' => 'text-slate-700',
                                        'soft' => 'bg-slate-100 text-slate-600',
                                    ],
                                    default => [
                                        'label' => 'อันดับ 3',
                                        'accent' => 'from-orange-300 via-orange-200 to-orange-400',
                                        'border' => 'border-orange-300',
                                        'number' => 'bg-orange-300 text-orange-950 ring-orange-100',
                                        'score' => 'text-orange-700',
                                        'soft' => 'bg-orange-50 text-orange-700',
                                    ],
                                };

                                $orderClass = ! $hasSharedRank
                                    ? match ($rank) {
                                        1 => 'lg:order-2 lg:-translate-y-4',
                                        2 => 'lg:order-1',
                                        3 => 'lg:order-3',
                                        default => '',
                                    }
                                    : '';

                                $primaryFile = $submission->files
                                    ->firstWhere('is_primary', true)
                                    ?? $submission->files->first();

                                $isImage = $primaryFile
                                    && str_starts_with(
                                        (string) $primaryFile->mime_type,
                                        'image/'
                                    );
                            @endphp

                            <article class="{{ $orderClass }} group overflow-hidden rounded-2xl border {{ $rankConfig['border'] }} bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                                <div class="h-1.5 bg-gradient-to-r {{ $rankConfig['accent'] }}"></div>

                                <div class="relative flex h-36 items-center justify-center overflow-hidden bg-white p-3">
                                    @if ($isImage)
                                        <img
                                            src="{{ $primaryFile->file_url }}"
                                            alt="{{ $submission->project_title }}"
                                            class="h-full w-full object-contain transition duration-300 group-hover:scale-[1.03]"
                                        >
                                    @else
                                        <div class="flex flex-col items-center text-slate-400">
                                            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h7.5M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                            <span class="mt-2 text-[11px] font-semibold uppercase">
                                                {{ $primaryFile?->file_extension ?? 'ไม่มีไฟล์' }}
                                            </span>
                                        </div>
                                    @endif

                                    <span class="absolute left-3 top-3 flex h-9 min-w-9 items-center justify-center rounded-full px-2 text-sm font-black shadow-sm ring-4 {{ $rankConfig['number'] }}">
                                        {{ $rank }}
                                    </span>

                                    @if ($isShared)
                                        <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $rankConfig['soft'] }}">
                                            อันดับร่วม
                                        </span>
                                    @endif
                                </div>

                                <div class="border-t border-slate-100 px-4 py-3.5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                                {{ $submission->submission_code }}
                                            </p>
                                            <h3 class="mt-1 line-clamp-2 text-sm font-bold leading-5 text-slate-900">
                                                {{ $submission->project_title }}
                                            </h3>
                                            @if ($submission->team_name)
                                                <p class="mt-1 truncate text-[11px] text-slate-500">
                                                    ทีม {{ $submission->team_name }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="shrink-0 text-right">
                                            <p class="text-[10px] text-slate-400">คะแนน</p>
                                            <p class="text-xl font-black {{ $rankConfig['score'] }}">
                                                {{ number_format((float) $submission->final_score, 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center">
                                <p class="text-sm font-semibold text-slate-700">ยังไม่มีผลงานที่พร้อมจัดอันดับ</p>
                                <p class="mt-1 text-xs text-slate-500">ตรวจสอบว่ากรรมการส่งคะแนนครบทุกเกณฑ์แล้ว</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374L10.052 3.38c.866-1.5 3.03-1.5 3.896 0l7.355 12.748ZM12 15.75h.008v.008H12v-.008Z" />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="font-bold text-slate-900">ผลการแข่งขันยังไม่พร้อม</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            ต้องจบห้องตัดสิน และกรรมการที่ตอบรับต้องส่งคะแนนตามเกณฑ์ที่ใช้งานครบทุกผลงาน
                        </p>
                    </div>

                    <div class="w-full sm:w-56">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                            <span>ความครบถ้วน</span>
                            <span>{{ $completedSubmissionCount }} / {{ $totalSubmissions }}</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-amber-400 transition-all" style="width: {{ $completionPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
