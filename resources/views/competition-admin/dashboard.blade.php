@extends('layouts.app')

@section('title', 'แดชบอร์ดผู้จัดการแข่งขัน')

@section('header')
    <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">
            แดชบอร์ดผู้จัดการแข่งขัน
        </h1>

        <p class="mt-1 text-xs text-slate-500">
            ภาพรวมการแข่งขัน ผลงาน การตัดสิน และองค์ความรู้ของคุณ
        </p>
    </div>
@endsection


@section('content')

    @php
        $competitionStatusLabels = [
            'draft' => 'ฉบับร่าง',
            'published' => 'เผยแพร่แล้ว',
            'open' => 'เปิดรับผลงาน',
            'closed' => 'ปิดรับผลงาน',
            'judging' => 'กำลังตัดสิน',
            'completed' => 'เสร็จสิ้น',
            'upcoming' => 'ยังไม่เปิดรับ',
            'waiting_result' => 'รอประกาศผล',
        ];

        $competitionStatusClasses = [
            'draft' => 'bg-slate-100 text-slate-600',
            'published' => 'bg-blue-50 text-blue-700',
            'open' => 'bg-emerald-50 text-emerald-700',
            'closed' => 'bg-rose-50 text-rose-700',
            'judging' => 'bg-violet-50 text-violet-700',
            'completed' => 'bg-green-50 text-green-700',
            'upcoming' => 'bg-amber-50 text-amber-700',
            'waiting_result' => 'bg-slate-100 text-slate-700',
        ];

        $submissionStatusLabels = [
            'submitted' => 'ส่งแล้ว',
            'under_review' => 'กำลังตรวจสอบ',
            'qualified' => 'ผ่าน',
            'disqualified' => 'ตัดสิทธิ์',
            'judged' => 'ตัดสินแล้ว',
        ];
    @endphp


    <div class="mx-auto w-full max-w-7xl space-y-6">

        {{-- ============================================================= --}}
        {{-- Welcome --}}
        {{-- ============================================================= --}}

        <section
            class="
                relative overflow-hidden rounded-2xl
                border border-slate-200
                bg-white p-5 shadow-sm
                sm:p-6
            "
        >
            <div
                class="
                    pointer-events-none absolute -right-20 -top-20
                    h-52 w-52 rounded-full bg-blue-50
                "
            ></div>

            <div
                class="
                    relative flex flex-col gap-5
                    lg:flex-row lg:items-center lg:justify-between
                "
            >

                <div class="flex items-center gap-4">

                    <div
                        class="
                            flex h-12 w-12 shrink-0 items-center justify-center
                            rounded-2xl bg-blue-600 text-white
                            shadow-sm shadow-blue-600/20
                        "
                    >
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 21h8m-4-4v4M7 4h10v3a5 5 0 0 1-10 0V4Zm0 1H4v2a4 4 0 0 0 4 4m9-6h3v2a4 4 0 0 1-4 4"
                            />
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            ยินดีต้อนรับ
                        </p>

                        <h2 class="mt-0.5 text-lg font-bold text-slate-900">
                            {{ auth()->user()->username }}
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ auth()->user()->role?->display_name ?? 'Competition Admin' }}
                        </p>
                    </div>

                </div>


                <a
                    href="{{ route('competition-admin.competitions.create') }}"
                    class="
                        inline-flex h-10 items-center justify-center gap-2
                        rounded-xl bg-blue-600 px-4
                        text-sm font-semibold text-white
                        shadow-sm
                        transition
                        hover:bg-blue-700
                        focus:outline-none focus:ring-4 focus:ring-blue-100
                    "
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>

                    สร้างการแข่งขัน
                </a>

            </div>
        </section>



        {{-- ============================================================= --}}
        {{-- Primary Stats --}}
        {{-- ============================================================= --}}

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Competitions --}}
            <a
                href="{{ route('competition-admin.competitions.index') }}"
                class="
                    group rounded-2xl border border-slate-200
                    bg-white p-5 shadow-sm transition
                    hover:-translate-y-0.5 hover:border-blue-200
                    hover:shadow-md
                "
            >
                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            การแข่งขันทั้งหมด
                        </p>

                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($stats['total_competitions']) }}
                        </p>
                    </div>

                    <div
                        class="
                            flex h-10 w-10 items-center justify-center
                            rounded-xl bg-blue-50 text-blue-600
                        "
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 21h8m-4-4v4M7 4h10v3a5 5 0 0 1-10 0V4Z"
                            />
                        </svg>
                    </div>

                </div>

                <div class="mt-4 flex items-center gap-2 text-xs">
                    <span
                        class="
                            rounded-full bg-emerald-50
                            px-2 py-1 font-semibold text-emerald-700
                        "
                    >
                        {{ number_format($stats['open_competitions']) }}
                        เปิดรับผลงาน
                    </span>
                </div>
            </a>


            {{-- Submissions --}}
            <a
                href="{{ route('competition-admin.submissions.index') }}"
                class="
                    group rounded-2xl border border-slate-200
                    bg-white p-5 shadow-sm transition
                    hover:-translate-y-0.5 hover:border-blue-200
                    hover:shadow-md
                "
            >
                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            ผลงานที่ส่งเข้ามา
                        </p>

                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($stats['total_submissions']) }}
                        </p>
                    </div>

                    <div
                        class="
                            flex h-10 w-10 items-center justify-center
                            rounded-xl bg-violet-50 text-violet-600
                        "
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M7 3h7l4 4v14H7V3Zm7 0v5h5M10 13h5M10 17h5"
                            />
                        </svg>
                    </div>

                </div>

                <div class="mt-4 text-xs text-slate-500">
                    ตัดสิทธิ์
                    <span class="font-semibold text-rose-600">
                        {{ number_format($stats['disqualified_submissions']) }}
                    </span>
                    ผลงาน
                </div>
            </a>


            {{-- Judges --}}
            <div
                class="
                    rounded-2xl border border-slate-200
                    bg-white p-5 shadow-sm
                "
            >
                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            กรรมการตอบรับ
                        </p>

                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($stats['accepted_judges']) }}
                        </p>
                    </div>

                    <div
                        class="
                            flex h-10 w-10 items-center justify-center
                            rounded-xl bg-amber-50 text-amber-600
                        "
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="9" cy="8" r="3" />
                            <path stroke-linecap="round" d="M3 20c.5-4 2.5-6 6-6s5.5 2 6 6" />
                            <path stroke-linecap="round" d="M17 8h4M19 6v4" />
                        </svg>
                    </div>

                </div>

                <div class="mt-4 text-xs text-slate-500">
                    รอตอบรับ
                    <span class="font-semibold text-amber-600">
                        {{ number_format($stats['pending_judges']) }}
                    </span>
                    รายการ
                </div>
            </div>


            {{-- Judging --}}
            <a
                href="{{ route('competition-admin.judging-rooms.index') }}"
                class="
                    group rounded-2xl border border-slate-200
                    bg-white p-5 shadow-sm transition
                    hover:-translate-y-0.5 hover:border-blue-200
                    hover:shadow-md
                "
            >
                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            ห้องตัดสินที่กำลังใช้งาน
                        </p>

                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ number_format($stats['active_judging_sessions']) }}
                        </p>
                    </div>

                    <div
                        class="
                            flex h-10 w-10 items-center justify-center
                            rounded-xl bg-emerald-50 text-emerald-600
                        "
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect x="4" y="5" width="16" height="14" rx="2" />
                            <path stroke-linecap="round" d="m10 9 5 3-5 3V9Z" />
                        </svg>
                    </div>

                </div>

                <p class="mt-4 text-xs text-slate-500">
                    Live หรือหยุดชั่วคราว
                </p>
            </a>

        </section>



        {{-- ============================================================= --}}
        {{-- Results + KM --}}
        {{-- ============================================================= --}}

        <section class="grid gap-4 lg:grid-cols-2">

            {{-- Results --}}
            <a
                href="{{ route('competition-admin.results.index') }}"
                class="
                    rounded-2xl border border-slate-200
                    bg-white p-5 shadow-sm
                    transition hover:border-blue-200 hover:shadow-md
                "
            >
                <div class="flex items-center justify-between gap-4">

                    <div>
                        <p class="text-sm font-semibold text-slate-900">
                            ผลการแข่งขัน
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            สถานะการประกาศผลการแข่งขันของคุณ
                        </p>
                    </div>

                    <svg
                        class="h-5 w-5 text-slate-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" d="m9 6 6 6-6 6" />
                    </svg>

                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">

                    <div class="rounded-xl bg-emerald-50 p-3">
                        <p class="text-2xl font-bold text-emerald-700">
                            {{ number_format($stats['published_results']) }}
                        </p>

                        <p class="mt-1 text-xs font-medium text-emerald-700">
                            ประกาศแล้ว
                        </p>
                    </div>

                    <div class="rounded-xl bg-amber-50 p-3">
                        <p class="text-2xl font-bold text-amber-700">
                            {{ number_format($stats['waiting_results']) }}
                        </p>

                        <p class="mt-1 text-xs font-medium text-amber-700">
                            รอประกาศ
                        </p>
                    </div>

                </div>
            </a>


            {{-- KM --}}
            <a
                href="{{ route('competition-admin.km.index') }}"
                class="
                    rounded-2xl border border-slate-200
                    bg-white p-5 shadow-sm
                    transition hover:border-blue-200 hover:shadow-md
                "
            >
                <div class="flex items-center justify-between gap-4">

                    <div>
                        <p class="text-sm font-semibold text-slate-900">
                            Knowledge Management
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            องค์ความรู้ที่อยู่ภายใต้สิทธิ์ของคุณ
                        </p>
                    </div>

                    <svg
                        class="h-5 w-5 text-slate-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" d="m9 6 6 6-6 6" />
                    </svg>

                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">

                    <div class="rounded-xl bg-blue-50 p-3">
                        <p class="text-2xl font-bold text-blue-700">
                            {{ number_format($stats['total_knowledge_items']) }}
                        </p>

                        <p class="mt-1 text-xs font-medium text-blue-700">
                            ทั้งหมด
                        </p>
                    </div>

                    <div class="rounded-xl bg-emerald-50 p-3">
                        <p class="text-2xl font-bold text-emerald-700">
                            {{ number_format($stats['published_knowledge_items']) }}
                        </p>

                        <p class="mt-1 text-xs font-medium text-emerald-700">
                            เผยแพร่แล้ว
                        </p>
                    </div>

                </div>
            </a>

        </section>



        {{-- ============================================================= --}}
        {{-- Quick Actions --}}
        {{-- ============================================================= --}}

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div>
                <h2 class="text-sm font-semibold text-slate-900">
                    เมนูด่วน
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    เข้าถึงส่วนที่ใช้งานบ่อย
                </p>
            </div>


            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                <a
                    href="{{ route('competition-admin.competitions.index') }}"
                    class="
                        flex items-center gap-3 rounded-xl
                        border border-slate-200 p-3
                        transition
                        hover:border-blue-200 hover:bg-blue-50/50
                    "
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <span class="text-lg font-bold">1</span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-800">
                            จัดการการแข่งขัน
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            สร้างและแก้ไขการแข่งขัน
                        </p>
                    </div>
                </a>


                <a
                    href="{{ route('competition-admin.submissions.index') }}"
                    class="
                        flex items-center gap-3 rounded-xl
                        border border-slate-200 p-3
                        transition
                        hover:border-blue-200 hover:bg-blue-50/50
                    "
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        <span class="text-lg font-bold">2</span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-800">
                            ผลงานที่ส่ง
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            ตรวจสอบผลงานการแข่งขัน
                        </p>
                    </div>
                </a>


                <a
                    href="{{ route('competition-admin.judging-rooms.index') }}"
                    class="
                        flex items-center gap-3 rounded-xl
                        border border-slate-200 p-3
                        transition
                        hover:border-blue-200 hover:bg-blue-50/50
                    "
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <span class="text-lg font-bold">3</span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-800">
                            ห้องตัดสิน
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            ควบคุม Live Judging
                        </p>
                    </div>
                </a>


                <a
                    href="{{ route('competition-admin.results.index') }}"
                    class="
                        flex items-center gap-3 rounded-xl
                        border border-slate-200 p-3
                        transition
                        hover:border-blue-200 hover:bg-blue-50/50
                    "
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <span class="text-lg font-bold">4</span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-800">
                            ผลการแข่งขัน
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            ตรวจอันดับและประกาศผล
                        </p>
                    </div>
                </a>


                <a
                    href="{{ route('competition-admin.km.index') }}"
                    class="
                        flex items-center gap-3 rounded-xl
                        border border-slate-200 p-3
                        transition
                        hover:border-blue-200 hover:bg-blue-50/50
                    "
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600">
                        <span class="text-lg font-bold">5</span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-800">
                            จัดการ KM
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            เผยแพร่องค์ความรู้
                        </p>
                    </div>
                </a>


                <a
                    href="{{ route('competition-admin.km.create') }}"
                    class="
                        flex items-center gap-3 rounded-xl
                        border border-slate-200 p-3
                        transition
                        hover:border-blue-200 hover:bg-blue-50/50
                    "
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <span class="text-lg font-bold">+</span>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-800">
                            เพิ่มองค์ความรู้
                        </p>

                        <p class="mt-0.5 text-[11px] text-slate-500">
                            สร้าง KM แบบ Manual
                        </p>
                    </div>
                </a>

            </div>
        </section>



        {{-- ============================================================= --}}
        {{-- Recent Data --}}
        {{-- ============================================================= --}}

        <div class="grid gap-6 xl:grid-cols-2">

            {{-- Recent Competitions --}}
            <section
                class="
                    overflow-hidden rounded-2xl
                    border border-slate-200
                    bg-white shadow-sm
                "
            >
                <div
                    class="
                        flex items-center justify-between gap-4
                        border-b border-slate-100
                        px-5 py-4
                    "
                >
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            การแข่งขันล่าสุด
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            การแข่งขันที่คุณจัดการล่าสุด
                        </p>
                    </div>

                    <a
                        href="{{ route('competition-admin.competitions.index') }}"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-700"
                    >
                        ดูทั้งหมด
                    </a>
                </div>


                <div class="divide-y divide-slate-100">

                    @forelse ($recentCompetitions as $competition)

                        @php
                            $status = $competition->display_status ?? 'draft';
                        @endphp

                        <a
                            href="{{ route('competition-admin.competitions.show', $competition) }}"
                            class="
                                block px-5 py-4 transition
                                hover:bg-slate-50
                            "
                        >
                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0">

                                    <div class="flex items-center gap-2">
                                        <h3
                                            class="
                                                truncate text-sm font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ $competition->title }}
                                        </h3>
                                    </div>


                                    <p class="mt-1 truncate text-xs text-slate-500">
                                        {{ $competition->category?->category_name ?? 'ไม่ระบุประเภท' }}
                                    </p>


                                    <div
                                        class="
                                            mt-2 flex flex-wrap items-center
                                            gap-x-4 gap-y-1
                                            text-[11px] text-slate-500
                                        "
                                    >
                                        <span>
                                            ผลงาน
                                            <strong class="text-slate-700">
                                                {{ number_format($competition->submissions_count) }}
                                            </strong>
                                        </span>

                                        <span>
                                            กรรมการ
                                            <strong class="text-slate-700">
                                                {{ number_format($competition->accepted_judges_count) }}
                                            </strong>
                                        </span>
                                    </div>

                                </div>


                                <span
                                    class="
                                        shrink-0 rounded-full px-2.5 py-1
                                        text-[10px] font-semibold
                                        {{ $competitionStatusClasses[$status] ?? 'bg-slate-100 text-slate-600' }}
                                    "
                                >
                                    {{ $competitionStatusLabels[$status] ?? $status }}
                                </span>

                            </div>
                        </a>

                    @empty

                        <div class="px-5 py-10 text-center">
                            <p class="text-sm font-medium text-slate-700">
                                ยังไม่มีการแข่งขัน
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                เริ่มสร้างการแข่งขันแรกของคุณ
                            </p>
                        </div>

                    @endforelse

                </div>
            </section>



            {{-- Recent Submissions --}}
            <section
                class="
                    overflow-hidden rounded-2xl
                    border border-slate-200
                    bg-white shadow-sm
                "
            >
                <div
                    class="
                        flex items-center justify-between gap-4
                        border-b border-slate-100
                        px-5 py-4
                    "
                >
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">
                            ผลงานล่าสุด
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            ผลงานที่ส่งเข้าการแข่งขันล่าสุด
                        </p>
                    </div>

                    <a
                        href="{{ route('competition-admin.submissions.index') }}"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-700"
                    >
                        ดูทั้งหมด
                    </a>
                </div>


                <div class="divide-y divide-slate-100">

                    @forelse ($recentSubmissions as $submission)

                        <div class="px-5 py-4">

                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0">

                                    <p
                                        class="
                                            truncate text-sm font-semibold
                                            text-slate-800
                                        "
                                    >
                                        {{ $submission->project_title }}
                                    </p>


                                    <p class="mt-1 truncate text-xs text-slate-500">
                                        {{ $submission->competition?->title ?? '-' }}
                                    </p>


                                    <div
                                        class="
                                            mt-2 flex flex-wrap items-center
                                            gap-x-3 gap-y-1
                                            text-[11px] text-slate-400
                                        "
                                    >
                                        <span>
                                            {{ $submission->submission_code }}
                                        </span>

                                        @if ($submission->submitted_at)
                                            <span>
                                                {{ $submission->submitted_at->format('d/m/Y H:i') }}
                                            </span>
                                        @endif
                                    </div>

                                </div>


                                <span
                                    class="
                                        shrink-0 rounded-full
                                        bg-slate-100 px-2.5 py-1
                                        text-[10px] font-semibold
                                        text-slate-600
                                    "
                                >
                                    {{ $submissionStatusLabels[$submission->status] ?? $submission->status }}
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="px-5 py-10 text-center">
                            <p class="text-sm font-medium text-slate-700">
                                ยังไม่มีผลงานส่งเข้ามา
                            </p>
                        </div>

                    @endforelse

                </div>
            </section>

        </div>

    </div>
@endsection