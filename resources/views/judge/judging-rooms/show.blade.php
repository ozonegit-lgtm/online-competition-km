@extends('layouts.app')

@section('title', 'ห้องตัดสิน')

@section('header')
    @php
        $statusConfig = match ($session->status) {
            'waiting' => [
                'label' => 'รอเริ่ม',
                'class' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'dot' => 'bg-amber-500',
            ],
            'live' => [
                'label' => 'กำลัง Live',
                'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'dot' => 'bg-emerald-500 animate-pulse',
            ],
            'paused' => [
                'label' => 'หยุดชั่วคราว',
                'class' => 'bg-orange-50 text-orange-700 ring-orange-200',
                'dot' => 'bg-orange-500',
            ],
            'ended' => [
                'label' => 'จบการตัดสิน',
                'class' => 'bg-violet-50 text-violet-700 ring-violet-200',
                'dot' => 'bg-violet-500',
            ],
            'closed' => [
                'label' => 'ปิดห้องแล้ว',
                'class' => 'bg-slate-100 text-slate-600 ring-slate-200',
                'dot' => 'bg-slate-400',
            ],
            default => [
                'label' => $session->status,
                'class' => 'bg-slate-100 text-slate-600 ring-slate-200',
                'dot' => 'bg-slate-400',
            ],
        };
    @endphp

    <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M8 12h8M8 16h5M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="break-words text-xl font-bold leading-tight text-slate-800 sm:text-2xl">
                        {{ $competition->title }}
                    </h1>

                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        ห้องตัดสินสำหรับกรรมการ
                    </p>
                </div>
            </div>
        </div>

        <span
            id="room-status"
            class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full px-3 py-2
                   text-xs font-semibold ring-1 sm:px-4 sm:text-sm
                   {{ $statusConfig['class'] }}"
        >
            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $statusConfig['dot'] }}"></span>
            {{ $statusConfig['label'] }}
        </span>
    </div>
@endsection

@section('content')
    @php
        $isLive = $session->isLive();

        $totalRubrics = $rubrics->count();

        $completedRubrics = $rubrics->filter(function ($rubric) use ($scores) {
            return $scores->get($rubric->id)?->score !== null;
        })->count();

        $totalScore = $scores->sum(function ($score) {
            return $score->score ?? 0;
        });

        $maxTotalScore = $rubrics->sum(function ($rubric) {
            return $rubric->max_score ?? 0;
        });

        $progressPercent = $totalRubrics > 0
            ? round(($completedRubrics / $totalRubrics) * 100)
            : 0;

        $isSubmitted =
            $rubrics->isNotEmpty() &&
            $scores->count() === $rubrics->count() &&
            $scores->every(
                fn ($score) => $score->submitted_at !== null
            );

        /*
        |--------------------------------------------------------------------------
        | Submitter data
        |--------------------------------------------------------------------------
        | ข้อมูลผู้ส่งถูกบันทึกโดยตรงในตาราง submissions
        | จากหน้าแบบฟอร์มส่งผลงาน
        |--------------------------------------------------------------------------
        */

        $submitterName = $submission?->contact_name;
        $submitterEmail = $submission?->contact_email;
        $submitterPhone = $submission?->contact_phone;

        /*
        |--------------------------------------------------------------------------
        | Optional submitter data
        |--------------------------------------------------------------------------
        | ตอนนี้ระบบยังไม่มีคอลัมน์มาตรฐานสำหรับ LINE / หน่วยงาน
        | จึงปล่อยเป็น null จนกว่าจะเพิ่มฟิลด์เหล่านี้อย่างเป็นทางการ
        |--------------------------------------------------------------------------
        */

        $submitterLine = null;
        $submitterWorkplace = null;

        $hasSubmitterInfo =
            filled($submitterName) ||
            filled($submitterEmail) ||
            filled($submitterPhone);
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-4 sm:space-y-6">

        {{-- Room notice --}}
        @if ($session->isWaiting())
            <div class="overflow-hidden rounded-2xl border border-amber-200 bg-amber-50">
                <div class="flex gap-3 p-4 sm:p-5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-amber-800 sm:text-base">
                            กำลังรอผู้จัดเริ่มการตัดสิน
                        </p>

                        <p class="mt-1 text-xs leading-5 text-amber-700 sm:text-sm">
                            คุณสามารถดูผลงานและเตรียมข้อมูลได้ แต่ยังไม่สามารถให้คะแนนได้
                        </p>
                    </div>
                </div>
            </div>

        @elseif ($session->isPaused())
            <div class="overflow-hidden rounded-2xl border border-orange-200 bg-orange-50">
                <div class="flex gap-3 p-4 sm:p-5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M8 5v14M16 5v14"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-orange-800 sm:text-base">
                            การตัดสินถูกหยุดชั่วคราว
                        </p>

                        <p class="mt-1 text-xs leading-5 text-orange-700 sm:text-sm">
                            กรุณารอผู้จัดดำเนินการ Live ต่อ จึงจะสามารถให้คะแนนได้
                        </p>
                    </div>
                </div>
            </div>

        @elseif ($session->isEnded() || $session->isClosed())
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                <div class="flex gap-3 p-4 sm:p-5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-slate-500">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m8 12 2.5 2.5L16 9"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-slate-700 sm:text-base">
                            ห้องนี้จบการตัดสินแล้ว
                        </p>

                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                            ไม่สามารถแก้ไขหรือส่งคะแนนเพิ่มเติมได้
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main content --}}
        <div class="grid min-w-0 grid-cols-1 gap-4 sm:gap-6 xl:grid-cols-5">

            {{-- =========================================================
                 LEFT / SUBMISSION
                 ========================================================= --}}
            <section class="min-w-0 space-y-4 sm:space-y-6 xl:col-span-3">

                {{-- Submission information --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    {{-- Header --}}
                    <div class="border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5">
                        <div class="flex items-start justify-between gap-3">

                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <path d="M14 2v6h6"/>
                                        <path d="M8 13h8M8 17h5"/>
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-800 sm:text-base">
                                        ผลงานที่กำลังตัดสิน
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        ตรวจสอบข้อมูลและผลงานก่อนให้คะแนน
                                    </p>
                                </div>
                            </div>

                            @if ($submission)
                                <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1.5 text-[11px] font-semibold text-slate-500">
                                    {{ $submission->submission_code }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if ($submission)

                        <div class="p-4 sm:p-6">

                            {{-- Project title --}}
                            <div>
                                <h2 class="break-words text-xl font-bold leading-tight text-slate-800 sm:text-2xl">
                                    {{ $submission->project_title }}
                                </h2>

                                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-slate-500 sm:text-sm">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M8 12h8M8 16h5M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/>
                                        </svg>
                                        {{ $submission->submission_code }}
                                    </span>

                                    @if ($submission->team_name)
                                        <span class="text-slate-300">•</span>

                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                            ทีม {{ $submission->team_name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Submitter information --}}
                            <div class="mt-6 overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50/80 to-white">

                                <div class="border-b border-blue-100 px-4 py-4 sm:px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 21a8 8 0 0 0-16 0"/>
                                                <circle cx="12" cy="7" r="4"/>
                                            </svg>
                                        </div>

                                        <div>
                                            <h3 class="text-sm font-bold text-slate-800">
                                                ข้อมูลผู้ส่งงาน
                                            </h3>

                                            <p class="mt-0.5 text-xs text-slate-500">
                                                ข้อมูลสำหรับกรรมการประกอบการพิจารณาผลงาน
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if ($hasSubmitterInfo)
                                    <div class="grid grid-cols-1 gap-px bg-blue-100 sm:grid-cols-2">

                                        {{-- Name --}}
                                        @if (filled($submitterName))
                                            <div class="bg-white p-4">
                                                <p class="text-[11px] font-medium text-slate-400">
                                                    ชื่อผู้ส่งงาน
                                                </p>

                                                <p class="mt-1.5 break-words text-sm font-semibold text-slate-800">
                                                    {{ $submitterName }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Phone --}}
                                        @if (filled($submitterPhone))
                                            <div class="bg-white p-4">
                                                <p class="text-[11px] font-medium text-slate-400">
                                                    เบอร์โทรศัพท์
                                                </p>

                                                <a
                                                    href="tel:{{ $submitterPhone }}"
                                                    class="mt-1.5 inline-flex items-center gap-1.5 break-all text-sm font-semibold text-blue-600 hover:text-blue-700"
                                                >
                                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 3.11 5.18 2 2 0 0 1 5.11 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L9 10.73a16 16 0 0 0 4.27 4.27l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92Z"/>
                                                    </svg>
                                                    {{ $submitterPhone }}
                                                </a>
                                            </div>
                                        @endif

                                        {{-- Email --}}
                                        @if (filled($submitterEmail))
                                            <div class="bg-white p-4">
                                                <p class="text-[11px] font-medium text-slate-400">
                                                    อีเมล
                                                </p>

                                                <a
                                                    href="mailto:{{ $submitterEmail }}"
                                                    class="mt-1.5 inline-flex max-w-full items-center gap-1.5 break-all text-sm font-semibold text-blue-600 hover:text-blue-700"
                                                >
                                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                                                        <path d="m3 7 9 6 9-6"/>
                                                    </svg>
                                                    {{ $submitterEmail }}
                                                </a>
                                            </div>
                                        @endif

                                        {{-- LINE --}}
                                        @if (filled($submitterLine))
                                            <div class="bg-white p-4">
                                                <p class="text-[11px] font-medium text-slate-400">
                                                    LINE ID
                                                </p>

                                                <p class="mt-1.5 break-words text-sm font-semibold text-slate-800">
                                                    {{ $submitterLine }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Workplace --}}
                                        @if (filled($submitterWorkplace))
                                            <div class="bg-white p-4 sm:col-span-2">
                                                <p class="text-[11px] font-medium text-slate-400">
                                                    หน่วยงาน / สถานที่ทำงาน
                                                </p>

                                                <p class="mt-1.5 break-words text-sm font-semibold text-slate-800">
                                                    {{ $submitterWorkplace }}
                                                </p>
                                            </div>
                                        @endif

                                    </div>
                                @else
                                    <div class="p-5">
                                        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="9"/>
                                                    <path d="M12 8v4M12 16h.01"/>
                                                </svg>
                                            </div>

                                            <div>
                                                <p class="text-xs font-semibold text-amber-800">
                                                    ไม่พบข้อมูลผู้ส่งงาน
                                                </p>

                                                <p class="mt-1 text-xs leading-5 text-amber-700">
                                                    ระบบยังไม่ได้ส่งข้อมูลผู้ส่งงานมาพร้อมกับ Submission นี้
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Project description --}}
                            @if ($submission->project_description)
                                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-slate-500 ring-1 ring-slate-200">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M8 6h13M8 12h13M8 18h13"/>
                                                <path d="M3 6h.01M3 12h.01M3 18h.01"/>
                                            </svg>
                                        </div>

                                        <p class="text-xs font-bold text-slate-600 sm:text-sm">
                                            รายละเอียดผลงาน
                                        </p>
                                    </div>

                                    <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-slate-600 sm:leading-7">
                                        {{ $submission->project_description }}
                                    </p>
                                </div>
                            @endif

                            {{-- Current preview --}}
                            @if ($currentFile?->file_path)
                                @php
                                    $previewUrl = asset('storage/' . $currentFile->file_path);

                                    $extension = strtolower(
                                        pathinfo(
                                            $currentFile->file_path,
                                            PATHINFO_EXTENSION
                                        )
                                    );

                                    $isImage = in_array(
                                        $extension,
                                        ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
                                        true
                                    );
                                @endphp

                                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">

                                    <div class="flex min-w-0 items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3.5 sm:px-5">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full bg-blue-600"></span>

                                                <p class="text-[11px] font-bold uppercase tracking-wide text-blue-600 sm:text-xs">
                                                    ไฟล์ที่กำลังนำเสนอ
                                                </p>
                                            </div>

                                            <p class="mt-1.5 break-all text-xs font-medium leading-5 text-slate-700 sm:truncate sm:text-sm">
                                                {{ $currentFile->original_name
                                                    ?? $currentFile->file_name
                                                    ?? $submission->project_title }}
                                            </p>
                                        </div>

                                        <span class="shrink-0 rounded-lg bg-blue-50 px-2.5 py-1.5 text-[10px] font-bold text-blue-600 sm:text-xs">
                                            กำลังนำเสนอ
                                        </span>
                                    </div>

                                    @if ($isImage)

                                        <div class="flex min-h-[240px] max-h-[65vh] items-center justify-center overflow-hidden bg-slate-100 p-3 sm:min-h-[360px] sm:p-5">
                                            <img
                                                src="{{ $previewUrl }}"
                                                alt="{{ $submission->project_title }}"
                                                class="max-h-[60vh] max-w-full rounded-xl object-contain shadow-sm"
                                            >
                                        </div>

                                    @else

                                        <div class="p-8 text-center sm:p-10">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200">
                                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                    <path d="M14 2v6h6"/>
                                                    <path d="M8 13h8M8 17h5"/>
                                                </svg>
                                            </div>

                                            <p class="mt-4 text-sm font-semibold text-slate-600">
                                                ไฟล์นี้ไม่สามารถแสดงตัวอย่างได้
                                            </p>

                                            <p class="mt-1 text-xs text-slate-400">
                                                เปิดไฟล์เพื่อดูผลงานฉบับเต็ม
                                            </p>

                                            <a
                                                href="{{ $previewUrl }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3
                                                       text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700
                                                       sm:w-auto"
                                            >
                                                เปิดผลงาน

                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M14 3h7v7"/>
                                                    <path d="M10 14 21 3"/>
                                                    <path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>
                                                </svg>
                                            </a>
                                        </div>

                                    @endif
                                </div>
                            @endif

                        @else

                            <div class="rounded-2xl border-2 border-dashed border-slate-200 px-4 py-16 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M4 7h16M4 12h16M4 17h10"/>
                                    </svg>
                                </div>

                                <p class="mt-4 text-sm font-semibold text-slate-600">
                                    ยังไม่ได้เลือกผลงาน
                                </p>

                                <p class="mt-1 text-xs leading-5 text-slate-400">
                                    กรุณารอผู้จัดเลือกผลงานสำหรับการตัดสิน
                                </p>
                            </div>

                        @endif
                    </div>
                </div>

                {{-- Files --}}
                @if ($submission)
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <path d="M14 2v6h6"/>
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-sm font-bold text-slate-800 sm:text-base">
                                        ไฟล์ผลงาน
                                    </h2>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        ไฟล์ประกอบการตัดสินทั้งหมด
                                    </p>
                                </div>
                            </div>

                            <span class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-500">
                                {{ $submission->files->count() }} ไฟล์
                            </span>
                        </div>

                        <div class="space-y-2.5 p-3 sm:space-y-3 sm:p-4">
                            @forelse ($submission->files as $file)

                                <div
                                    class="flex min-w-0 flex-col gap-3 rounded-xl border p-3 transition sm:flex-row sm:items-center sm:justify-between sm:p-4
                                           {{ $currentFile?->id === $file->id
                                                ? 'border-blue-300 bg-blue-50/70'
                                                : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50' }}"
                                >
                                    <div class="flex min-w-0 items-start gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                                            {{ $currentFile?->id === $file->id
                                                ? 'bg-blue-100 text-blue-600'
                                                : 'bg-slate-100 text-slate-500' }}"
                                        >
                                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <path d="M14 2v6h6"/>
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="break-all text-sm font-medium leading-5 text-slate-700 sm:truncate">
                                                {{ $file->original_name
                                                    ?? $file->file_name
                                                    ?? 'ไฟล์ผลงาน' }}
                                            </p>

                                            @if ($currentFile?->id === $file->id)
                                                <p class="mt-1 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                                    ไฟล์ที่ผู้จัดกำลังนำเสนอ
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($file->file_path)
                                        <a
                                            href="{{ asset('storage/' . $file->file_path) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-white px-3.5 py-2.5
                                                   text-xs font-semibold text-blue-600 ring-1 ring-blue-200
                                                   transition hover:bg-blue-50 sm:w-auto"
                                        >
                                            เปิดไฟล์

                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 3h7v7"/>
                                                <path d="M10 14 21 3"/>
                                                <path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>

                            @empty

                                <div class="py-8 text-center text-sm text-slate-400">
                                    ไม่มีไฟล์แนบ
                                </div>

                            @endforelse
                        </div>
                    </div>
                @endif

            </section>

            {{-- =========================================================
                 RIGHT / SCORING
                 ========================================================= --}}
            <section class="min-w-0 xl:col-span-2">

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    {{-- Scoring header --}}
                    <div class="border-b border-slate-200 bg-white p-4 sm:p-5">

                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 3v18M3 12h18"/>
                                        <path d="M7 7h10M7 17h10"/>
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-base font-bold text-slate-800">
                                        ให้คะแนน
                                    </h2>

                                    <p class="text-xs text-slate-500">
                                        ประเมินผลงานตามเกณฑ์
                                    </p>
                                </div>
                            </div>

                            @if ($isSubmitted)
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    ส่งแล้ว
                                </span>
                            @elseif ($isLive)
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                                    Live
                                </span>
                            @endif
                        </div>

                        @if ($submission && $rubrics->isNotEmpty())
                            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">

                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-600">
                                            ความคืบหน้าการให้คะแนน
                                        </p>

                                        <p class="mt-0.5 text-[11px] text-slate-400">
                                            {{ $completedRubrics }} จาก {{ $totalRubrics }} เกณฑ์
                                        </p>
                                    </div>

                                    <span class="text-sm font-bold text-blue-600">
                                        {{ $progressPercent }}%
                                    </span>
                                </div>

                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                                    <div
                                        class="h-full rounded-full bg-blue-600 transition-all duration-300"
                                        style="width: {{ $progressPercent }}%"
                                    ></div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2.5">
                                    <div class="rounded-xl bg-white px-3.5 py-3 ring-1 ring-slate-200">
                                        <p class="text-[10px] font-medium text-slate-400">
                                            คะแนนปัจจุบัน
                                        </p>

                                        <p class="mt-0.5 text-lg font-bold text-slate-800">
                                            {{ number_format($totalScore, 2) }}

                                            <span class="text-xs font-medium text-slate-400">
                                                / {{ number_format($maxTotalScore, 2) }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-white px-3.5 py-3 ring-1 ring-slate-200">
                                        <p class="text-[10px] font-medium text-slate-400">
                                            เหลือ
                                        </p>

                                        <p class="mt-0.5 text-lg font-bold text-slate-800">
                                            {{ max($totalRubrics - $completedRubrics, 0) }}

                                            <span class="text-xs font-medium text-slate-400">
                                                เกณฑ์
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if (!$submission)

                        <div class="p-8 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 8v4M12 16h.01"/>
                                </svg>
                            </div>

                            <p class="mt-3 text-sm font-semibold text-slate-600">
                                รอผู้จัดเลือกผลงาน
                            </p>

                            <p class="mt-1 text-xs leading-5 text-slate-400">
                                เมื่อมีผลงาน ระบบจะแสดงแบบให้คะแนนที่นี่
                            </p>
                        </div>

                    @elseif ($rubrics->isEmpty())

                        <div class="p-8 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-500">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 8v4M12 16h.01"/>
                                </svg>
                            </div>

                            <p class="mt-3 text-sm font-semibold text-red-600">
                                ยังไม่มีเกณฑ์การให้คะแนน
                            </p>

                            <p class="mt-1 text-xs leading-5 text-slate-400">
                                กรุณาติดต่อผู้จัดการแข่งขัน
                            </p>
                        </div>

                    @else

                        {{-- Score form --}}
                        <form
                            action="{{ route(
                                'judge.judging-rooms.scores.draft',
                                $session
                            ) }}"
                            method="POST"
                        >
                            @csrf

                            <div class="divide-y divide-slate-100">

                                @foreach ($rubrics as $index => $rubric)
                                    @php
                                        $existingScore = $scores->get($rubric->id);
                                        $hasScore = $existingScore?->score !== null;
                                        $scoreSubmitted = $existingScore?->submitted_at !== null;
                                    @endphp

                                    <div class="p-4 sm:p-5">

                                        <div class="flex items-start gap-3">

                                            <div
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                                                {{ $hasScore
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : 'bg-slate-100 text-slate-500' }}"
                                            >
                                                @if ($hasScore)
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <path d="m5 12 4 4L19 6"/>
                                                    </svg>
                                                @else
                                                    <span class="text-xs font-bold">
                                                        {{ $index + 1 }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">

                                                    <div class="min-w-0">
                                                        <label
                                                            for="score_{{ $rubric->id }}"
                                                            class="block break-words text-sm font-bold leading-5 text-slate-700"
                                                        >
                                                            {{ $rubric->criteria_name }}
                                                        </label>

                                                        @if ($rubric->description)
                                                            <p class="mt-1 break-words text-xs leading-5 text-slate-500">
                                                                {{ $rubric->description }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    <span class="w-fit shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                                        เต็ม {{ number_format($rubric->max_score, 2) }}
                                                    </span>

                                                </div>
                                            </div>
                                        </div>

                                        {{-- Score --}}
                                        <div class="mt-4">
                                            <label
                                                for="score_{{ $rubric->id }}"
                                                class="mb-1.5 block text-xs font-semibold text-slate-500"
                                            >
                                                คะแนนที่ให้
                                            </label>

                                            <div class="relative">
                                                <input
                                                    id="score_{{ $rubric->id }}"
                                                    type="number"
                                                    name="scores[{{ $rubric->id }}][score]"
                                                    value="{{ old(
                                                        "scores.{$rubric->id}.score",
                                                        $existingScore?->score
                                                    ) }}"
                                                    min="0"
                                                    max="{{ $rubric->max_score }}"
                                                    step="0.01"
                                                    inputmode="decimal"
                                                    required
                                                    @disabled(!$isLive || $isSubmitted)
                                                    class="w-full rounded-xl border border-slate-300
                                                           bg-slate-50 px-4 py-3.5 pr-20 text-xl
                                                           font-bold text-slate-800 outline-none
                                                           transition placeholder:text-slate-300
                                                           focus:border-blue-500 focus:bg-white
                                                           focus:ring-4 focus:ring-blue-100
                                                           disabled:cursor-not-allowed
                                                           disabled:bg-slate-100
                                                           disabled:opacity-70"
                                                    placeholder="0"
                                                >

                                                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-xs font-semibold text-slate-400">
                                                    / {{ number_format($rubric->max_score, 2) }}
                                                </span>
                                            </div>

                                            @error("scores.{$rubric->id}.score")
                                                <p class="mt-2 text-xs font-medium leading-5 text-red-600">
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        {{-- Comment --}}
                                        <div class="mt-3">
                                            <label
                                                for="comment_{{ $rubric->id }}"
                                                class="mb-1.5 block text-xs font-semibold text-slate-500"
                                            >
                                                ความคิดเห็น

                                                <span class="font-normal text-slate-400">
                                                    (ไม่บังคับ)
                                                </span>
                                            </label>

                                            <textarea
                                                id="comment_{{ $rubric->id }}"
                                                name="scores[{{ $rubric->id }}][comment]"
                                                rows="3"
                                                placeholder="เขียนความคิดเห็นเพิ่มเติมเกี่ยวกับเกณฑ์นี้..."
                                                @disabled(!$isLive || $isSubmitted)
                                                class="w-full resize-y rounded-xl border border-slate-300
                                                       bg-slate-50 px-4 py-3 text-sm leading-6
                                                       text-slate-700 outline-none transition
                                                       placeholder:text-slate-300
                                                       focus:border-blue-500 focus:bg-white
                                                       focus:ring-4 focus:ring-blue-100
                                                       disabled:cursor-not-allowed
                                                       disabled:bg-slate-100
                                                       disabled:opacity-70"
                                            >{{ old(
                                                "scores.{$rubric->id}.comment",
                                                $existingScore?->comment
                                            ) }}</textarea>
                                        </div>

                                    </div>
                                @endforeach

                            </div>

                            {{-- Action area --}}
                            <div class="border-t border-slate-200 bg-slate-50 p-4 sm:p-5">

                                @if ($isSubmitted)

                                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <path d="m5 12 4 4L19 6"/>
                                                </svg>
                                            </div>

                                            <div>
                                                <p class="text-sm font-bold text-emerald-700">
                                                    ยืนยันคะแนนเรียบร้อยแล้ว
                                                </p>

                                                <p class="mt-1 text-xs leading-5 text-emerald-600">
                                                    คะแนนของคุณถูกส่งให้ระบบแล้ว และไม่สามารถแก้ไขได้
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                @elseif ($isLive)

                                    <button
                                        type="submit"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-blue-200
                                               bg-white px-4 py-3.5 text-sm font-bold text-blue-600
                                               shadow-sm transition hover:bg-blue-50 active:bg-blue-100"
                                    >
                                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/>
                                            <path d="M17 21v-8H7v8M7 3v5h8"/>
                                        </svg>

                                        บันทึกร่างคะแนน
                                    </button>

                                @else

                                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
                                        <p class="text-sm font-semibold text-slate-600">
                                            ยังไม่สามารถให้คะแนนได้
                                        </p>

                                        <p class="mt-1 text-xs leading-5 text-slate-400">
                                            ระบบจะเปิดให้บันทึกคะแนนเมื่อห้องกำลัง Live
                                        </p>
                                    </div>

                                @endif
                            </div>
                        </form>

                        {{-- Final submit --}}
                        @if ($isLive && !$isSubmitted)

                            <div class="border-t border-slate-100 bg-white p-4 sm:p-5">

                                <div class="mb-3 flex items-start gap-2.5">
                                    <svg
                                        class="mt-0.5 h-4 w-4 shrink-0 text-amber-500"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path d="M10.3 3.9 2.2 18a2 2 0 0 0 1.7 3h16.2a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                                        <path d="M12 9v4M12 17h.01"/>
                                    </svg>

                                    <p class="text-xs leading-5 text-slate-500">
                                        ตรวจสอบคะแนนให้ครบถ้วนก่อนยืนยัน

                                        <span class="font-semibold text-slate-700">
                                            หลังส่งแล้วจะไม่สามารถแก้ไขได้
                                        </span>
                                    </p>
                                </div>

                                <form
                                    action="{{ route(
                                        'judge.judging-rooms.scores.submit',
                                        $session
                                    ) }}"
                                    method="POST"
                                    onsubmit="return confirm('ยืนยันส่งคะแนนของผลงานนี้หรือไม่?\n\nหลังยืนยันแล้วจะไม่สามารถแก้ไขคะแนนได้')"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl
                                               bg-emerald-600 px-4 py-3.5 text-sm font-bold
                                               text-white shadow-sm transition
                                               hover:bg-emerald-700 active:bg-emerald-800"
                                    >
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 2 11 13"/>
                                            <path d="m22 2-7 20-4-9-9-4Z"/>
                                        </svg>

                                        ยืนยันส่งคะแนน
                                    </button>
                                </form>

                            </div>

                        @endif

                    @endif

                </div>
            </section>
        </div>

        {{-- Back --}}
        <div class="pt-1">
            <a
                href="{{ route('judge.judging-rooms.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                       border border-slate-300 bg-white px-5 py-3 text-sm font-semibold
                       text-slate-600 transition hover:bg-slate-50 active:bg-slate-100
                       sm:w-auto sm:py-2.5"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m15 18-6-6 6-6"/>
                </svg>

                กลับหน้ารายการห้อง
            </a>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stateUrl = @json(
                route('judge.judging-rooms.state', $session)
            );

            let currentVersion = Number(
                @json($session->state_version)
            );

            let polling = false;

            const checkRoomState = async () => {
                if (polling || document.hidden) {
                    return;
                }

                polling = true;

                try {
                    const response = await fetch(stateUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        return;
                    }

                    const state = await response.json();

                    if (
                        Number(state.state_version) !== currentVersion
                    ) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error(
                        'ไม่สามารถตรวจสถานะห้องได้',
                        error
                    );
                } finally {
                    polling = false;
                }
            };

            setInterval(checkRoomState, 2000);
        });
    </script>
@endpush