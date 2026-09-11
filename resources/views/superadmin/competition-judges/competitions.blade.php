@extends('layouts.app')

@section('title', 'จัดการกรรมการ')

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6M22 11h-6"/>
                </svg>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">
                    จัดการกรรมการ
                </h1>
                <p class="mt-0.5 text-xs text-slate-500">
                    เลือกการแข่งขันเพื่อดูและแต่งตั้งกรรมการผู้ตัดสิน
                </p>
            </div>
        </div>

        <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/>
            </svg>
            {{ number_format($competitions->total()) }} การแข่งขัน
        </span>
    </div>
@endsection

@section('content')
    @php
        $hasSearch = trim((string) $search) !== '';
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-8">

        {{-- Search --}}
        <form
            method="GET"
            action="{{ route('superadmin.competitions.judges.list') }}"
            class="rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                        </svg>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900">
                                ค้นหาการแข่งขัน
                            </h2>

                            @if ($hasSearch)
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700">
                                    กำลังค้นหา
                                </span>
                            @endif
                        </div>

                        <p class="mt-1 text-xs text-slate-500">
                            ค้นหาจากชื่อการแข่งขันเพื่อเข้าสู่หน้าจัดการกรรมการ
                        </p>
                    </div>
                </div>

                @if ($hasSearch)
                    <a
                        href="{{ route('superadmin.competitions.judges.list') }}"
                        class="inline-flex h-8 shrink-0 items-center justify-center gap-1.5 self-start rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 sm:self-auto"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M3 6h18"/>
                            <path d="M8 6V4h8v2"/>
                            <path d="M19 6l-1 14H6L5 6"/>
                        </svg>
                        ล้างการค้นหา
                    </a>
                @endif
            </div>

            <div class="p-4 sm:p-5">
                <label for="competition-search" class="mb-1.5 block text-xs font-semibold text-slate-600">
                    ชื่อการแข่งขัน
                </label>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <div class="relative min-w-0 flex-1">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                        </svg>

                        <input
                            id="competition-search"
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            placeholder="พิมพ์ชื่อการแข่งขัน..."
                            class="h-10 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex h-10 shrink-0 items-center justify-center gap-1.5 rounded-lg bg-slate-900 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                        </svg>
                        ค้นหา
                    </button>
                </div>

                @if ($hasSearch)
                    <p class="mt-2 text-xs text-slate-500">
                        ผลลัพธ์สำหรับ
                        <span class="font-semibold text-slate-700">“{{ $search }}”</span>
                    </p>
                @endif
            </div>
        </form>

        {{-- Results header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">
                    การแข่งขันทั้งหมด
                </h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    เลือกการ์ดการแข่งขันที่ต้องการจัดการรายชื่อกรรมการ
                </p>
            </div>

            <p class="text-xs font-medium text-slate-500">
                แสดง {{ number_format($competitions->count()) }} รายการในหน้านี้
            </p>
        </div>

        @if ($competitions->isEmpty())
            {{-- Empty state --}}
            <section class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center shadow-sm sm:py-14">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v12H4z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V5h8v2"/>
                    </svg>
                </div>

                <h3 class="mt-3 text-sm font-bold text-slate-800">
                    ไม่พบการแข่งขัน
                </h3>

                <p class="mx-auto mt-1 max-w-md text-xs leading-5 text-slate-500">
                    @if ($hasSearch)
                        ไม่พบการแข่งขันที่ตรงกับคำค้นหา “{{ $search }}” ลองเปลี่ยนคำค้นหาหรือแสดงรายการทั้งหมด
                    @else
                        ยังไม่มีการแข่งขันในระบบสำหรับการจัดการกรรมการ
                    @endif
                </p>

                @if ($hasSearch)
                    <a
                        href="{{ route('superadmin.competitions.judges.list') }}"
                        class="mt-4 inline-flex h-8 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                        แสดงการแข่งขันทั้งหมด
                    </a>
                @endif
            </section>
        @else
            {{-- Competition cards --}}
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($competitions as $competition)
                    @php
                        $creatorProfile = $competition->creator?->adminProfile;

                        $creatorName = trim(
                            ($creatorProfile?->first_name ?? '') . ' ' .
                            ($creatorProfile?->last_name ?? '')
                        );

                        $creatorName = $creatorName !== ''
                            ? $creatorName
                            : ($competition->creator?->username ?? 'ไม่ระบุผู้สร้าง');

                        $coverUrl = $competition->cover_image
                            ? asset('storage/' . $competition->cover_image)
                            : null;

                        $statusConfig = match ($competition->status) {
                            'open' => [
                                'label' => 'เปิดรับผลงาน',
                                'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                'dot' => 'bg-emerald-500',
                            ],
                            'draft' => [
                                'label' => 'แบบร่าง',
                                'class' => 'border-slate-200 bg-slate-100 text-slate-600',
                                'dot' => 'bg-slate-400',
                            ],
                            'closed' => [
                                'label' => 'ปิดแล้ว',
                                'class' => 'border-red-200 bg-red-50 text-red-700',
                                'dot' => 'bg-red-500',
                            ],
                            default => [
                                'label' => ucfirst($competition->status),
                                'class' => 'border-amber-200 bg-amber-50 text-amber-700',
                                'dot' => 'bg-amber-500',
                            ],
                        };
                    @endphp

                    <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-blue-200 hover:shadow-md">
                        {{-- Cover --}}
                        <div class="relative h-40 overflow-hidden border-b border-slate-100 bg-slate-100">
                            @if ($coverUrl)
                                <img
                                    src="{{ $coverUrl }}"
                                    alt="รูปปก {{ $competition->title }}"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                                >
                            @else
                                <div class="flex h-full flex-col items-center justify-center gap-2 bg-gradient-to-br from-blue-50 via-slate-50 to-white text-blue-300">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/80 ring-1 ring-blue-100">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                        </svg>
                                    </div>
                                    <span class="text-[11px] font-medium text-slate-400">
                                        ไม่มีรูปปก
                                    </span>
                                </div>
                            @endif

                            <div class="absolute left-3 top-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold shadow-sm backdrop-blur {{ $statusConfig['class'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                    {{ $statusConfig['label'] }}
                                </span>
                            </div>

                            <div class="absolute right-3 top-3">
                                <span class="inline-flex items-center gap-1 rounded-full border border-white/70 bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm backdrop-blur">
                                    <svg class="h-3 w-3 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                    </svg>
                                    {{ number_format($competition->judge_assignments_count) }} คน
                                </span>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex flex-1 flex-col p-4">
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-blue-600">
                                    {{ $competition->category?->category_name ?? 'ไม่ระบุหมวดหมู่' }}
                                </p>

                                <h3 class="mt-1.5 line-clamp-2 min-h-[48px] text-base font-bold leading-6 text-slate-900 transition group-hover:text-blue-700">
                                    {{ $competition->title }}
                                </h3>
                            </div>

                            <dl class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-100 bg-slate-50/70 px-3">
                                <div class="flex items-center justify-between gap-4 py-2.5">
                                    <dt class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500">
                                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <circle cx="12" cy="8" r="4"/>
                                            <path d="M4 21a8 8 0 0 1 16 0"/>
                                        </svg>
                                        ผู้ดูแลการแข่งขัน
                                    </dt>
                                    <dd class="max-w-[55%] truncate text-right text-xs font-semibold text-slate-700">
                                        {{ $creatorName }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4 py-2.5">
                                    <dt class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500">
                                        <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                        </svg>
                                        กรรมการที่แต่งตั้ง
                                    </dt>
                                    <dd class="text-xs font-bold text-blue-700">
                                        {{ number_format($competition->judge_assignments_count) }} คน
                                    </dd>
                                </div>
                            </dl>

                            <div class="mt-auto border-t border-slate-100 pt-3">
                                <a
                                    href="{{ route('superadmin.competitions.judges.index', $competition) }}"
                                    class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path stroke-linecap="round" d="M19 8v6M22 11h-6"/>
                                    </svg>
                                    จัดการกรรมการ
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($competitions->hasPages())
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    {{ $competitions->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
