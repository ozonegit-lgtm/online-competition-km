@extends('layouts.app')

@section('title', 'แต่งตั้งกรรมการ')

@section('header')
    <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-100">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6M22 11h-6"/>
            </svg>
        </div>

        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">
                แต่งตั้งกรรมการ
            </h1>
            <p class="mt-0.5 truncate text-xs text-slate-500">
                เลือกผู้ตัดสินสำหรับการแข่งขัน {{ $competition->title }}
            </p>
        </div>
    </div>
@endsection

@section('content')
    @php
        $selectedJudgeIds = collect(old('judge_ids', $assignedJudgeIds))
            ->map(fn ($id) => (int) $id)
            ->all();

        $selectedJudgeCount = count($selectedJudgeIds);
        $availableJudgeCount = $judges->count();
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-8">

        {{-- Navigation --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a
                href="{{ route('superadmin.competitions.judges.list', $competition) }}"
                class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                </svg>
                กลับหน้ารายชื่อกรรมการ
            </a>

            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="hidden sm:inline">สถานะการแก้ไข</span>

                @if ($judgesLocked)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        ล็อกแล้ว
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        แก้ไขได้
                    </span>
                @endif
            </div>
        </div>

        {{-- Validation --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path stroke-linecap="round" d="M12 8v5M12 17h.01"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-bold text-red-800">
                            ไม่สามารถบันทึกรายชื่อกรรมการได้
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-xs leading-5 text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Locked notice --}}
        @if ($judgesLocked)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="5" y="10" width="14" height="10" rx="2"/>
                            <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-amber-900">
                            รายชื่อกรรมการถูกล็อกแล้ว
                        </p>
                        <p class="mt-1 text-xs leading-5 text-amber-800">
                            การแข่งขันเริ่มเข้าสู่กระบวนการตัดสินแล้ว จึงเปิดดูรายชื่อได้อย่างเดียวและไม่สามารถเพิ่มหรือถอดกรรมการได้
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Competition context --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-4 p-4 sm:p-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-blue-600">
                            การแข่งขัน
                        </p>
                        <h2 class="mt-1 break-words text-base font-bold leading-6 text-slate-900 sm:text-lg">
                            {{ $competition->title }}
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            จัดการผู้ใช้ Role Judge ที่ได้รับสิทธิ์เข้าห้องตัดสินของการแข่งขันนี้
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                    <div class="min-w-[112px] rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            Judge ทั้งหมด
                        </p>
                        <p class="mt-1 text-lg font-bold tabular-nums text-slate-900">
                            {{ number_format($availableJudgeCount) }}
                            <span class="text-xs font-medium text-slate-500">คน</span>
                        </p>
                    </div>

                    <div class="min-w-[112px] rounded-xl border border-blue-200 bg-blue-50 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-blue-500">
                            เลือกไว้
                        </p>
                        <p class="mt-1 text-lg font-bold tabular-nums text-blue-700">
                            {{ number_format($selectedJudgeCount) }}
                            <span class="text-xs font-medium text-blue-600">คน</span>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <form
            method="POST"
            action="{{ route('superadmin.competitions.judges.sync', $competition) }}"
        >
            @csrf
            @method('PUT')

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{-- List header --}}
                <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            เลือกกรรมการ
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            คลิกที่การ์ดหรือช่องเลือกของผู้ใช้ที่ต้องการแต่งตั้ง
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            มี Judge {{ number_format($availableJudgeCount) }} คน
                        </span>

                        @if ($selectedJudgeCount > 0)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                </svg>
                                เลือกไว้ {{ number_format($selectedJudgeCount) }} คน
                            </span>
                        @endif
                    </div>
                </div>


                {{-- Search Judge --}}
                @if ($judges->isNotEmpty())
                    <div class="border-b border-slate-100 bg-slate-50/50 p-3 sm:p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="relative min-w-0 flex-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg
                                        class="h-[18px] w-[18px]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <circle cx="11" cy="11" r="7"/>
                                        <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                                    </svg>
                                </div>

                                <input
                                    id="judge-search"
                                    type="search"
                                    placeholder="ค้นหาชื่อ Username อีเมล ตำแหน่ง หรือหน่วยงาน..."
                                    autocomplete="off"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-11 text-sm font-medium text-slate-700 shadow-sm outline-none transition placeholder:font-normal placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                >

                                <button
                                    id="judge-search-clear"
                                    type="button"
                                    title="ล้างการค้นหา"
                                    aria-label="ล้างการค้นหา"
                                    class="absolute inset-y-0 right-0 hidden items-center justify-center px-4 text-slate-400 transition hover:text-slate-700"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="flex h-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm">
                                <span id="judge-search-count">
                                    {{ number_format($availableJudgeCount) }}
                                </span>
                                <span class="ml-1">คน</span>
                            </div>
                        </div>

                        <p class="mt-2 text-[11px] text-slate-500">
                            ค้นหาแบบทันทีโดยไม่ต้องโหลดหน้าใหม่
                        </p>
                    </div>
                @endif

                @if ($judges->isEmpty())
                    {{-- Empty state --}}
                    <div class="flex flex-col items-center justify-center px-4 py-12 text-center sm:py-14">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path stroke-linecap="round" d="M19 8v6M22 11h-6"/>
                            </svg>
                        </div>

                        <h3 class="mt-3 text-sm font-bold text-slate-800">
                            ยังไม่มีผู้ใช้งาน Role Judge
                        </h3>
                        <p class="mt-1 max-w-md text-xs leading-5 text-slate-500">
                            ต้องมีบัญชีผู้ใช้ Role Judge ก่อน จึงจะสามารถแต่งตั้งกรรมการสำหรับการแข่งขันนี้ได้
                        </p>

                        @unless ($judgesLocked)
                            <a
                                href="{{ route('superadmin.createUser') }}"
                                class="mt-4 inline-flex h-8 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                                </svg>
                                สร้างบัญชี Judge
                            </a>
                        @endunless
                    </div>
                @else
                    {{-- Judge cards --}}
                    <div id="judge-grid" class="grid gap-3 p-4 sm:p-5 md:grid-cols-2 2xl:grid-cols-3">
                        @foreach ($judges as $judge)
                            @php
                                $profile = $judge->adminProfile;

                                $displayName = trim(
                                    ($profile?->first_name ?? '') . ' ' .
                                    ($profile?->last_name ?? '')
                                );

                                $displayName = $displayName !== ''
                                    ? $displayName
                                    : $judge->username;

                                $avatarUrl = $profile?->avatar
                                    ? asset('storage/' . $profile->avatar)
                                    : null;

                                $isSelected = in_array(
                                    (int) $judge->id,
                                    $selectedJudgeIds,
                                    true
                                );

                                $assignment = $competition
                                    ->judgeAssignments
                                    ->firstWhere('judge_id', $judge->id);
                            @endphp

                            <label
                                data-judge-card
                                data-judge-search="{{ $displayName }} {{ $judge->username }} {{ $judge->email }} {{ $profile?->position }} {{ $profile?->organization }}"
                                class="group relative flex min-h-[132px] flex-col rounded-xl border p-4 transition
                                    {{ $judgesLocked ? 'cursor-not-allowed opacity-75' : 'cursor-pointer' }}
                                    {{ $isSelected
                                        ? 'border-blue-300 bg-blue-50/70 ring-1 ring-blue-100'
                                        : 'border-slate-200 bg-white hover:border-blue-200 hover:bg-slate-50/70 hover:shadow-sm' }}"
                            >
                                <div class="flex min-w-0 items-start gap-3 pr-8">
                                    @if ($avatarUrl)
                                        <img
                                            src="{{ $avatarUrl }}"
                                            alt="{{ $displayName }}"
                                            class="h-11 w-11 shrink-0 rounded-full object-cover ring-2 ring-white shadow-sm"
                                        >
                                    @else
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700 ring-2 ring-white shadow-sm">
                                            {{ mb_strtoupper(mb_substr($displayName, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <div class="flex min-w-0 flex-wrap items-center gap-1.5">
                                            <p class="min-w-0 truncate text-sm font-bold text-slate-900">
                                                {{ $displayName }}
                                            </p>

                                            @if ($assignment)
                                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    แต่งตั้งแล้ว
                                                </span>
                                            @elseif ($isSelected)
                                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">
                                                    เลือกแล้ว
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-1 truncate text-xs text-slate-500">
                                            {{ $judge->email }}
                                        </p>

                                        @if ($profile?->position || $profile?->organization)
                                            <div class="mt-2 space-y-1 border-t border-slate-100 pt-2">
                                                @if ($profile?->position)
                                                    <p class="truncate text-[11px] font-medium text-slate-600">
                                                        {{ $profile->position }}
                                                    </p>
                                                @endif

                                                @if ($profile?->organization)
                                                    <p class="truncate text-[11px] text-slate-400">
                                                        {{ $profile->organization }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <input
                                    type="checkbox"
                                    name="judge_ids[]"
                                    value="{{ $judge->id }}"
                                    @checked($isSelected)
                                    @disabled($judgesLocked)
                                    class="absolute right-4 top-4 h-[18px] w-[18px] rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-400 disabled:cursor-not-allowed disabled:opacity-60"
                                >

                                <div class="mt-auto flex items-center justify-between gap-3 pt-3 text-[10px]">
                                    <span class="font-medium text-slate-400">
                                        Role Judge
                                    </span>

                                    @if ($isSelected)
                                        <span class="inline-flex items-center gap-1 font-semibold text-blue-700">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                            </svg>
                                            อยู่ในรายการที่เลือก
                                        </span>
                                    @else
                                        <span class="font-medium text-slate-400">
                                            คลิกเพื่อเลือก
                                        </span>
                                    @endif
                                </div>
                            </label>
                        @endforeach

                        <div
                            id="judge-search-empty"
                            class="hidden py-12 text-center md:col-span-2 2xl:col-span-3"
                        >
                            <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <circle cx="11" cy="11" r="7"/>
                                    <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                                </svg>
                            </div>

                            <p class="mt-3 text-sm font-semibold text-slate-700">
                                ไม่พบกรรมการ
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                ลองค้นหาด้วยชื่อ Username อีเมล ตำแหน่ง หรือหน่วยงาน
                            </p>
                        </div>
                    </div>

                    {{-- Action footer --}}
                    <div class="border-t border-slate-200 bg-slate-50/70 px-4 py-4 sm:px-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            @if ($judgesLocked)
                                <div class="flex items-center gap-2 text-xs font-medium text-amber-700">
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <rect x="5" y="10" width="14" height="10" rx="2"/>
                                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                    </svg>
                                    ดูรายชื่อได้อย่างเดียว เนื่องจากเริ่มการตัดสินแล้ว
                                </div>
                            @else
                                <div>
                                    <p class="text-xs font-semibold text-slate-700">
                                        ตรวจสอบรายชื่อก่อนบันทึก
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-slate-500">
                                        กรรมการที่บันทึกจะได้รับสิทธิ์เข้าห้องตัดสินของการแข่งขันนี้
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 sm:shrink-0">
                                    <a
                                        href="{{ route('superadmin.competitions.judges.list', $competition) }}"
                                        class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300"
                                    >
                                        ยกเลิก
                                    </a>

                                    <button
                                        type="submit"
                                        class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5 9 16l10-10"/>
                                        </svg>
                                        บันทึกรายชื่อกรรมการ
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </section>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('judge-search');
            const clearButton = document.getElementById('judge-search-clear');
            const countElement = document.getElementById('judge-search-count');
            const emptyElement = document.getElementById('judge-search-empty');

            if (!searchInput) {
                return;
            }

            const cards = Array.from(
                document.querySelectorAll('[data-judge-card]')
            );

            const normalize = (value) => {
                return (value || '')
                    .toString()
                    .toLocaleLowerCase('th')
                    .trim();
            };

            const filterJudges = () => {
                const keyword = normalize(searchInput.value);
                let visibleCount = 0;

                cards.forEach((card) => {
                    const searchableText = normalize(
                        card.dataset.judgeSearch || card.textContent
                    );

                    const isVisible =
                        keyword === '' ||
                        searchableText.includes(keyword);

                    card.classList.toggle('hidden', !isVisible);

                    if (isVisible) {
                        visibleCount++;
                    }
                });

                if (countElement) {
                    countElement.textContent =
                        visibleCount.toLocaleString('th-TH');
                }

                if (emptyElement) {
                    emptyElement.classList.toggle(
                        'hidden',
                        visibleCount !== 0
                    );
                }

                if (clearButton) {
                    clearButton.classList.toggle(
                        'hidden',
                        keyword === ''
                    );

                    clearButton.classList.toggle(
                        'flex',
                        keyword !== ''
                    );
                }
            };

            searchInput.addEventListener('input', filterJudges);

            clearButton?.addEventListener('click', () => {
                searchInput.value = '';
                filterJudges();
                searchInput.focus();
            });
        });
    </script>
@endsection
