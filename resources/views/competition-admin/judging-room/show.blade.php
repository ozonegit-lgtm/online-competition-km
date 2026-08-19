@extends('layouts.app')

@section('title', 'ควบคุมห้องตัดสิน')

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
        <div class="flex min-w-0 items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                    <path d="M8 9h8M8 13h5M8 17h8"/>
                </svg>
            </div>

            <div class="min-w-0">
                <h1 class="break-words text-xl font-bold leading-tight text-slate-800 sm:text-2xl">
                    ห้องตัดสิน
                </h1>

                <p class="mt-1 break-words text-xs text-slate-500 sm:text-sm">
                    {{ $competition->title }}
                </p>
            </div>
        </div>

        <span
            class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full px-3 py-2
                   text-xs font-semibold ring-1 sm:px-4 sm:text-sm
                   {{ $statusConfig['class'] }}"
        >
            <span class="h-2.5 w-2.5 rounded-full {{ $statusConfig['dot'] }}"></span>
            {{ $statusConfig['label'] }}
        </span>
    </div>
@endsection

@section('content')
    @php
        $currentSubmission = isset($currentSubmission)
            ? $currentSubmission
            : $session->currentSubmission;

        $displayFile = $currentFile ?? $session->currentFile ?? null;

        $filePath = $displayFile?->file_path;
        $fileUrl = $filePath
            ? asset('storage/' . $filePath)
            : null;

        $extension = strtolower(
            pathinfo($filePath ?? '', PATHINFO_EXTENSION)
        );

        $isImage = in_array(
            $extension,
            ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
            true
        );

        /*
         * ข้อมูลผู้ส่งผลงาน
         *
         * ใช้ข้อมูลจาก Submission โดยตรง
         * เพื่อให้ Admin เห็นข้อมูลเดียวกับที่ผู้ส่งบันทึกไว้
         */
        $contactName = $currentSubmission?->contact_name;
        $contactEmail = $currentSubmission?->contact_email;
        $contactPhone = $currentSubmission?->contact_phone;
        $teamName = $currentSubmission?->team_name;
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-5 sm:space-y-6">

        {{-- =========================================================
             ROOM CONTROL
        ========================================================== --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M12 3v18"/>
                                <path d="M3 12h18"/>
                                <circle cx="12" cy="12" r="9"/>
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-slate-800 sm:text-base">
                                ควบคุมห้องตัดสิน
                            </h2>

                            <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                                จัดการสถานะห้องและควบคุมการแสดงผลงานให้กรรมการ
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                        <span class="text-[11px] font-medium text-slate-400">
                            Room Version
                        </span>

                        <span class="text-xs font-bold text-slate-700">
                            {{ $session->state_version }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50/70 px-4 py-4 sm:px-6">

                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">

                    @if ($session->isWaiting())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.start',
                                $competition
                            ) }}"
                            method="POST"
                            class="w-full sm:w-auto"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                                       bg-emerald-600 px-5 py-3 text-sm font-bold text-white
                                       shadow-sm transition hover:bg-emerald-700
                                       focus:outline-none focus:ring-4 focus:ring-emerald-100
                                       sm:w-auto"
                            >
                                <svg
                                    class="h-4.5 w-4.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="m8 5 11 7-11 7V5Z"/>
                                </svg>

                                เริ่ม Live
                            </button>
                        </form>
                    @endif

                    @if ($session->isLive())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.pause',
                                $competition
                            ) }}"
                            method="POST"
                            class="w-full sm:w-auto"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                                       bg-amber-500 px-5 py-3 text-sm font-bold text-white
                                       shadow-sm transition hover:bg-amber-600
                                       focus:outline-none focus:ring-4 focus:ring-amber-100
                                       sm:w-auto"
                            >
                                <svg
                                    class="h-4.5 w-4.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M8 5v14M16 5v14"/>
                                </svg>

                                หยุดชั่วคราว
                            </button>
                        </form>
                    @endif

                    @if ($session->isPaused())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.resume',
                                $competition
                            ) }}"
                            method="POST"
                            class="w-full sm:w-auto"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                                       bg-emerald-600 px-5 py-3 text-sm font-bold text-white
                                       shadow-sm transition hover:bg-emerald-700
                                       focus:outline-none focus:ring-4 focus:ring-emerald-100
                                       sm:w-auto"
                            >
                                <svg
                                    class="h-4.5 w-4.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="m5 12 4 4L19 6"/>
                                </svg>

                                Live ต่อ
                            </button>
                        </form>
                    @endif

                    @if ($session->isLive() || $session->isPaused())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.end',
                                $competition
                            ) }}"
                            method="POST"
                            class="w-full sm:w-auto"
                            onsubmit="return confirm('ยืนยันจบการตัดสินหรือไม่?')"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                                       border border-red-200 bg-white px-5 py-3 text-sm font-bold
                                       text-red-600 transition hover:bg-red-50
                                       focus:outline-none focus:ring-4 focus:ring-red-100
                                       sm:w-auto"
                            >
                                <svg
                                    class="h-4.5 w-4.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M6 6h12v12H6z"/>
                                </svg>

                                จบการตัดสิน
                            </button>
                        </form>
                    @endif

                    @if ($session->isEnded())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.close',
                                $competition
                            ) }}"
                            method="POST"
                            class="w-full sm:w-auto"
                            onsubmit="return confirm('ปิดห้องแล้วจะไม่สามารถตัดสินต่อได้ ยืนยันหรือไม่?')"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                                       bg-slate-800 px-5 py-3 text-sm font-bold text-white
                                       shadow-sm transition hover:bg-slate-900
                                       focus:outline-none focus:ring-4 focus:ring-slate-200
                                       sm:w-auto"
                            >
                                <svg
                                    class="h-4.5 w-4.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                                </svg>

                                ปิดห้อง
                            </button>
                        </form>
                    @endif

                    @if ($session->isClosed())
                        <div class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500">
                            <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                            ห้องนี้ถูกปิดแล้ว
                        </div>
                    @endif

                </div>
            </div>
        </section>


        {{-- =========================================================
             SUMMARY
        ========================================================== --}}
        <section class="grid gap-3 sm:grid-cols-3 sm:gap-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            ผลงานพร้อมตัดสิน
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-800 sm:text-3xl">
                            {{ $submissions->count() }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6"/>
                            <path d="M8 13h8M8 17h5"/>
                        </svg>
                    </div>
                </div>

                <p class="mt-2 text-[11px] text-slate-400">
                    ผลงานที่สามารถเลือกนำเสนอได้
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            เกณฑ์ที่เปิดใช้งาน
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-800 sm:text-3xl">
                            {{ $rubrics->count() }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M8 6h13M8 12h13M8 18h13"/>
                            <path d="M3 6h.01M3 12h.01M3 18h.01"/>
                        </svg>
                    </div>
                </div>

                <p class="mt-2 text-[11px] text-slate-400">
                    เกณฑ์ที่กรรมการใช้ประเมิน
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            กรรมการในห้อง
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-800 sm:text-3xl">
                            {{ $assignments->count() }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="9" cy="8" r="3"/>
                            <path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/>
                            <path d="M16 5.5a3 3 0 0 1 0 5.8"/>
                            <path d="M18 14.5c1.8.9 3 2.8 3 5.5"/>
                        </svg>
                    </div>
                </div>

                <p class="mt-2 text-[11px] text-slate-400">
                    กรรมการที่ได้รับมอบหมาย
                </p>
            </div>

        </section>


        {{-- =========================================================
             CURRENT SUBMISSION
        ========================================================== --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Section Header --}}
            <div class="border-b border-slate-100 bg-white px-4 py-4 sm:px-6 sm:py-5">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 items-start gap-3">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white shadow-sm">
                            1
                        </div>

                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-slate-800 sm:text-base">
                                เลือกผลงานที่ต้องการแสดง
                            </h2>

                            <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                                ผลงานที่เลือกจะแสดงให้กรรมการทุกคนในห้องเห็นทันที
                            </p>
                        </div>

                    </div>

                    @if (!$session->isEnded() && !$session->isClosed())

                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.submission',
                                $competition
                            ) }}"
                            method="POST"
                            class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-2xl"
                        >
                            @csrf
                            @method('PUT')

                            <select
                                name="submission_id"
                                required
                                class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white
                                       px-4 py-3 text-sm text-slate-700 outline-none transition
                                       focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >
                                <option value="">
                                    เลือกผลงาน
                                </option>

                                @foreach ($submissions as $submission)
                                    <option
                                        value="{{ $submission->id }}"
                                        @selected(
                                            $session->current_submission_id === $submission->id
                                        )
                                    >
                                        {{ $submission->submission_code }}
                                        — {{ $submission->project_title }}
                                    </option>
                                @endforeach
                            </select>

                            <button
                                type="submit"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl
                                       bg-blue-600 px-5 py-3 text-sm font-bold text-white
                                       shadow-sm transition hover:bg-blue-700
                                       focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>

                                แสดงผลงาน
                            </button>
                        </form>

                    @else

                        <div class="rounded-xl bg-slate-100 px-4 py-3 text-xs font-medium text-slate-500 sm:text-sm">
                            ห้องจบหรือปิดแล้ว ไม่สามารถเปลี่ยนผลงานได้
                        </div>

                    @endif

                </div>

                @error('submission_id')
                    <div class="mt-3 flex items-center gap-2 rounded-xl bg-red-50 px-3 py-2.5 text-xs font-medium text-red-600">
                        <svg
                            class="h-4 w-4 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 8v4M12 16h.01"/>
                        </svg>

                        {{ $message }}
                    </div>
                @enderror

            </div>


            {{-- Current Submission --}}
            @if ($currentSubmission)

                <div class="p-4 sm:p-6">

                    {{-- Submission title --}}
                    <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between">

                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2">

                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    กำลังแสดง
                                </span>

                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">
                                    {{ $currentSubmission->submission_code }}
                                </span>

                            </div>

                            <h2 class="mt-3 break-words text-xl font-bold leading-tight text-slate-800 sm:text-2xl">
                                {{ $currentSubmission->project_title }}
                            </h2>

                            @if ($currentSubmission->team_name)
                                <p class="mt-2 flex items-center gap-2 text-sm text-slate-500">
                                    <svg
                                        class="h-4 w-4 shrink-0 text-slate-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <circle cx="9" cy="8" r="3"/>
                                        <path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/>
                                        <path d="M16 5.5a3 3 0 0 1 0 5.8"/>
                                        <path d="M18 14.5c1.8.9 3 2.8 3 5.5"/>
                                    </svg>

                                    ทีม {{ $currentSubmission->team_name }}
                                </p>
                            @endif

                        </div>

                        <span class="w-fit shrink-0 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                            {{ $currentSubmission->status }}
                        </span>

                    </div>


                    {{-- Main submission layout --}}
                    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">

                        {{-- LEFT --}}
                        <div class="min-w-0 space-y-5">

                            {{-- Preview --}}
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">

                                <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3">
                                    <div class="flex min-w-0 items-center gap-2">

                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z"/>
                                                <circle cx="9" cy="9" r="1.5"/>
                                                <path d="m4 17 5-5 4 4 2-2 5 5"/>
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-700">
                                                ผลงานที่กำลังนำเสนอ
                                            </p>

                                            @if ($displayFile)
                                                <p class="mt-0.5 truncate text-[11px] text-slate-400">
                                                    {{ $displayFile->original_name
                                                        ?? $displayFile->file_name
                                                        ?? 'ไฟล์ผลงาน' }}
                                                </p>
                                            @endif
                                        </div>

                                    </div>

                                    <span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-600">
                                        Live View
                                    </span>
                                </div>

                                @if ($fileUrl && $isImage)

                                    <div class="flex min-h-[320px] items-center justify-center overflow-hidden bg-slate-100 p-3 sm:min-h-[460px] sm:p-5">
                                        <img
                                            src="{{ $fileUrl }}"
                                            alt="ผลงาน {{ $currentSubmission->project_title }}"
                                            class="max-h-[70vh] w-auto max-w-full rounded-xl object-contain shadow-sm"
                                        >
                                    </div>

                                @elseif ($fileUrl)

                                    <div class="flex min-h-[320px] flex-col items-center justify-center p-8 text-center sm:min-h-[460px]">

                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                                            <svg
                                                class="h-7 w-7"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            >
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <path d="M14 2v6h6"/>
                                                <path d="M8 13h8M8 17h5"/>
                                            </svg>
                                        </div>

                                        <p class="mt-4 text-sm font-bold text-slate-700">
                                            ไฟล์นี้ไม่สามารถแสดงตัวอย่างได้
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            เปิดไฟล์ในหน้าต่างใหม่เพื่อดูผลงาน
                                        </p>

                                        <a
                                            href="{{ $fileUrl }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600
                                                   px-5 py-3 text-sm font-bold text-white shadow-sm
                                                   transition hover:bg-blue-700"
                                        >
                                            เปิดผลงาน

                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path d="M14 3h7v7"/>
                                                <path d="M10 14 21 3"/>
                                                <path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>
                                            </svg>
                                        </a>

                                    </div>

                                @else

                                    <div class="flex min-h-[320px] flex-col items-center justify-center p-8 text-center sm:min-h-[460px]">

                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-300 shadow-sm">
                                            <svg
                                                class="h-7 w-7"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            >
                                                <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z"/>
                                                <path d="M8 12h8M12 8v8"/>
                                            </svg>
                                        </div>

                                        <p class="mt-4 text-sm font-semibold text-slate-600">
                                            ยังไม่ได้เลือกไฟล์สำหรับแสดง
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            เลือกไฟล์จากรายการไฟล์ผลงานด้านข้าง
                                        </p>

                                    </div>

                                @endif

                            </div>


                            {{-- Description --}}
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">

                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path d="M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"/>
                                            <path d="M8 8h8M8 12h8M8 16h5"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-bold text-slate-700">
                                            รายละเอียดผลงาน
                                        </h3>

                                        <p class="text-[11px] text-slate-400">
                                            ข้อมูลประกอบการพิจารณาของกรรมการ
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-xl bg-slate-50 p-4">
                                    <p class="whitespace-pre-line break-words text-sm leading-7 text-slate-600">
                                        {{ $currentSubmission->project_description ?: 'ไม่มีรายละเอียดผลงาน' }}
                                    </p>
                                </div>

                            </div>

                        </div>


                        {{-- RIGHT --}}
                        <aside class="min-w-0 space-y-4">

                            {{-- =================================================
                                 SENDER INFORMATION
                            ================================================== --}}
                            <div class="overflow-hidden rounded-2xl border border-blue-100 bg-white">

                                <div class="border-b border-blue-100 bg-blue-50/60 px-4 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white">
                                            <svg
                                                class="h-5 w-5"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <circle cx="12" cy="8" r="3.5"/>
                                                <path d="M4.5 20c.7-3.7 3.3-6 7.5-6s6.8 2.3 7.5 6"/>
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <h3 class="text-sm font-bold text-slate-800">
                                                ข้อมูลผู้ส่งผลงาน
                                            </h3>

                                            <p class="mt-0.5 text-[11px] text-slate-500">
                                                ข้อมูลที่ผู้ส่งกรอกในแบบฟอร์ม
                                            </p>
                                        </div>

                                    </div>

                                </div>

                                <div class="divide-y divide-slate-100">

                                    {{-- Name --}}
                                    <div class="p-4">

                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                            ชื่อผู้ติดต่อ
                                        </p>

                                        <div class="mt-2 flex items-start gap-2">

                                            <svg
                                                class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <circle cx="12" cy="8" r="3"/>
                                                <path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/>
                                            </svg>

                                            <p class="break-words text-sm font-semibold text-slate-700">
                                                {{ $contactName ?: 'ไม่ระบุชื่อ' }}
                                            </p>

                                        </div>

                                    </div>


                                    {{-- Email --}}
                                    <div class="p-4">

                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                            อีเมล
                                        </p>

                                        <div class="mt-2 flex items-start gap-2">

                                            <svg
                                                class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                                <path d="m3 7 9 6 9-6"/>
                                            </svg>

                                            @if ($contactEmail)
                                                <a
                                                    href="mailto:{{ $contactEmail }}"
                                                    class="break-all text-sm font-medium text-blue-600 transition hover:text-blue-700 hover:underline"
                                                >
                                                    {{ $contactEmail }}
                                                </a>
                                            @else
                                                <p class="text-sm text-slate-400">
                                                    ไม่ระบุอีเมล
                                                </p>
                                            @endif

                                        </div>

                                    </div>


                                    {{-- Phone --}}
                                    <div class="p-4">

                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                            เบอร์โทรศัพท์
                                        </p>

                                        <div class="mt-2 flex items-start gap-2">

                                            <svg
                                                class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path d="M6.5 3h3L11 7l-2 2a15 15 0 0 0 6 6l2-2 4 1.5v3c0 1.1-.9 2-2 2C10.4 19.5 4.5 13.6 4.5 6a2 2 0 0 1 2-3Z"/>
                                            </svg>

                                            @if ($contactPhone)
                                                <a
                                                    href="tel:{{ $contactPhone }}"
                                                    class="text-sm font-medium text-blue-600 transition hover:text-blue-700 hover:underline"
                                                >
                                                    {{ $contactPhone }}
                                                </a>
                                            @else
                                                <p class="text-sm text-slate-400">
                                                    ไม่ระบุเบอร์โทรศัพท์
                                                </p>
                                            @endif

                                        </div>

                                    </div>


                                    {{-- Team --}}
                                    <div class="p-4">

                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                            ทีม / หน่วยงาน
                                        </p>

                                        <div class="mt-2 flex items-start gap-2">

                                            <svg
                                                class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <circle cx="9" cy="8" r="3"/>
                                                <path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/>
                                                <path d="M16 5.5a3 3 0 0 1 0 5.8"/>
                                                <path d="M18 14.5c1.8.9 3 2.8 3 5.5"/>
                                            </svg>

                                            <p class="break-words text-sm font-medium text-slate-700">
                                                {{ $teamName ?: 'ไม่ระบุทีม' }}
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- Submission Meta --}}
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">

                                <h3 class="text-sm font-bold text-slate-700">
                                    ข้อมูลผลงาน
                                </h3>

                                <dl class="mt-4 space-y-3">

                                    <div class="flex items-start justify-between gap-4">
                                        <dt class="text-xs text-slate-400">
                                            รหัสผลงาน
                                        </dt>

                                        <dd class="text-right text-xs font-bold text-slate-700">
                                            {{ $currentSubmission->submission_code }}
                                        </dd>
                                    </div>

                                    <div class="flex items-start justify-between gap-4">
                                        <dt class="text-xs text-slate-400">
                                            สถานะ
                                        </dt>

                                        <dd>
                                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                                                {{ $currentSubmission->status }}
                                            </span>
                                        </dd>
                                    </div>

                                </dl>

                            </div>


                            {{-- Files --}}
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">

                                <div class="flex items-center justify-between gap-3">

                                    <div>
                                        <h3 class="text-sm font-bold text-slate-700">
                                            ไฟล์ผลงาน
                                        </h3>

                                        <p class="mt-0.5 text-[11px] text-slate-400">
                                            ไฟล์ทั้งหมดของผลงานนี้
                                        </p>
                                    </div>

                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500">
                                        {{ $currentSubmission->files->count() }} ไฟล์
                                    </span>

                                </div>


                                <div class="mt-4 space-y-2">

                                    @forelse ($currentSubmission->files as $file)

                                        @php
                                            $isCurrentFile = $session->current_file_id === $file->id;
                                            $fileExtension = strtolower(
                                                pathinfo($file->file_path ?? '', PATHINFO_EXTENSION)
                                            );
                                        @endphp

                                        <div
                                            class="group rounded-xl border p-3 transition
                                            {{ $isCurrentFile
                                                ? 'border-blue-200 bg-blue-50/70'
                                                : 'border-slate-200 bg-white hover:bg-slate-50' }}"
                                        >

                                            <div class="flex items-start gap-3">

                                                <div
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                                    {{ $isCurrentFile
                                                        ? 'bg-blue-100 text-blue-600'
                                                        : 'bg-slate-100 text-slate-500' }}"
                                                >
                                                    <svg
                                                        class="h-4 w-4"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                        <path d="M14 2v6h6"/>
                                                    </svg>
                                                </div>


                                                <div class="min-w-0 flex-1">

                                                    <p class="break-all text-xs font-semibold leading-5 text-slate-700">
                                                        {{ $file->original_name
                                                            ?? $file->file_name
                                                            ?? 'ไฟล์ผลงาน' }}
                                                    </p>

                                                    @if ($fileExtension)
                                                        <p class="mt-0.5 text-[10px] uppercase text-slate-400">
                                                            {{ $fileExtension }} file
                                                        </p>
                                                    @endif

                                                    @if ($isCurrentFile)
                                                        <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2 py-1 text-[10px] font-bold text-blue-700">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                                            กำลังแสดงให้กรรมการ
                                                        </span>
                                                    @endif

                                                </div>

                                            </div>


                                            @if ($file->file_path)

                                                <a
                                                    href="{{ asset('storage/' . $file->file_path) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="mt-3 inline-flex w-full items-center justify-center gap-2
                                                           rounded-lg border border-slate-200 bg-white px-3 py-2
                                                           text-xs font-semibold text-blue-600 transition
                                                           hover:border-blue-200 hover:bg-blue-50"
                                                >
                                                    เปิดไฟล์

                                                    <svg
                                                        class="h-3.5 w-3.5"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                    >
                                                        <path d="M14 3h7v7"/>
                                                        <path d="M10 14 21 3"/>
                                                        <path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>
                                                    </svg>
                                                </a>

                                            @endif

                                        </div>

                                    @empty

                                        <div class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
                                            <p class="text-xs text-slate-400">
                                                ไม่มีไฟล์แนบ
                                            </p>
                                        </div>

                                    @endforelse

                                </div>

                            </div>

                        </aside>

                    </div>

                </div>

            @else

                <div class="px-5 py-16 text-center sm:px-6">

                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z"/>
                            <path d="M8 12h8M12 8v8"/>
                        </svg>
                    </div>

                    <h2 class="mt-4 text-base font-bold text-slate-700">
                        ยังไม่ได้เลือกผลงาน
                    </h2>

                    <p class="mx-auto mt-1 max-w-md text-xs leading-5 text-slate-400 sm:text-sm">
                        เลือกผลงานจากรายการด้านบนเพื่อเริ่มแสดงให้กรรมการในห้องตัดสิน
                    </p>

                </div>

            @endif

        </section>


        {{-- =========================================================
             JUDGES
        ========================================================== --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 px-4 py-4 sm:px-6">

                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle cx="9" cy="8" r="3"/>
                                <path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/>
                                <path d="M16 5.5a3 3 0 0 1 0 5.8"/>
                                <path d="M18 14.5c1.8.9 3 2.8 3 5.5"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-sm font-bold text-slate-800 sm:text-base">
                                กรรมการในห้อง
                            </h2>

                            <p class="mt-0.5 text-[11px] text-slate-400 sm:text-xs">
                                รายชื่อและสถานะการส่งคะแนน
                            </p>
                        </div>

                    </div>

                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-500">
                        {{ $assignments->count() }} คน
                    </span>

                </div>

            </div>


            <div class="divide-y divide-slate-100">

                @forelse ($assignments as $assignment)

                    @php
                        $judgeName =
                            $assignment->judge->name
                            ?? $assignment->judge->username
                            ?? 'ไม่พบข้อมูลกรรมการ';

                        $judgeEmail =
                            $assignment->judge->email
                            ?? '-';

                        $hasSubmitted =
                            $assignment->submitted_at !== null;
                    @endphp

                    <div class="flex flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                        <div class="flex min-w-0 items-center gap-3">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-500">
                                {{ mb_substr($judgeName, 0, 1) }}
                            </div>

                            <div class="min-w-0">

                                <p class="truncate text-sm font-semibold text-slate-700">
                                    {{ $judgeName }}
                                </p>

                                <p class="mt-0.5 break-all text-xs text-slate-400">
                                    {{ $judgeEmail }}
                                </p>

                            </div>

                        </div>


                        <div class="flex items-center justify-between gap-4 sm:justify-end">

                            <div class="text-left sm:text-right">

                                <p class="text-xs font-semibold text-slate-600">
                                    {{ $assignment->assignment_status }}
                                </p>

                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ $hasSubmitted
                                        ? 'ส่งคะแนนแล้ว'
                                        : 'ยังไม่ส่งคะแนน' }}
                                </p>

                            </div>

                            @if ($hasSubmitted)

                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path d="m5 12 4 4L19 6"/>
                                    </svg>
                                </span>

                            @else

                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 7v5l3 2"/>
                                    </svg>
                                </span>

                            @endif

                        </div>

                    </div>

                @empty

                    <div class="px-5 py-12 text-center">

                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle cx="9" cy="8" r="3"/>
                                <path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/>
                            </svg>
                        </div>

                        <p class="mt-3 text-sm font-semibold text-slate-600">
                            ยังไม่มีกรรมการในห้องนี้
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            เพิ่มกรรมการก่อนเริ่มการตัดสิน
                        </p>

                    </div>

                @endforelse

            </div>

        </section>


        {{-- Back --}}
        <div class="pt-1">

            <a
                href="{{ route('competition-admin.judging-rooms.index') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                       border border-slate-300 bg-white px-5 py-3 text-sm font-semibold
                       text-slate-600 transition hover:bg-slate-50 active:bg-slate-100
                       sm:w-auto sm:py-2.5"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="m15 18-6-6 6-6"/>
                </svg>

                กลับหน้ารายการห้อง
            </a>

        </div>

    </div>
@endsection