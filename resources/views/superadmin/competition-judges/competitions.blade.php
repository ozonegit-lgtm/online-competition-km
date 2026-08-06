@extends('layouts.app')

@section('title', 'จัดการกรรมการ')

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row
                sm:items-end sm:justify-between">

        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                จัดการกรรมการ ผู้ลงคะแนน
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                เลือกการแข่งขันที่ต้องการแต่งตั้งกรรมการ
            </p>
        </div>

        <span class="inline-flex w-fit items-center rounded-full
                     border border-blue-200 bg-blue-50 px-3 py-1.5
                     text-sm font-semibold text-blue-700">
            {{ number_format($competitions->total()) }}
            การแข่งขัน
        </span>
    </div>
@endsection

@section('content')
    <div class="mx-auto max-w-7xl">

        {{-- ช่องค้นหา --}}
        <form
            method="GET"
            action="{{ route(
                'superadmin.competitions.judges.list'
            ) }}"
            class="mb-6 rounded-2xl border border-slate-200
                   bg-white p-4 shadow-sm"
        >
            <div class="flex flex-col gap-3 sm:flex-row">

                <div class="relative flex-1">
                    <svg
                        class="pointer-events-none absolute left-4 top-1/2
                               h-5 w-5 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M20 20l-4-4"/>
                    </svg>

                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="ค้นหาชื่อการแข่งขัน..."
                        class="w-full rounded-xl border border-slate-300
                               py-3 pl-12 pr-4 text-sm outline-none
                               transition focus:border-blue-500
                               focus:ring-4 focus:ring-blue-100"
                    >
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center
                           rounded-xl bg-blue-600 px-6 py-3
                           text-sm font-semibold text-white transition
                           hover:bg-blue-700 focus:outline-none
                           focus:ring-4 focus:ring-blue-200"
                >
                    ค้นหา
                </button>

                @if ($search !== '')
                    <a
                        href="{{ route(
                            'superadmin.competitions.judges.list'
                        ) }}"
                        class="inline-flex items-center justify-center
                               rounded-xl border border-slate-300 bg-white
                               px-5 py-3 text-sm font-semibold
                               text-slate-700 transition hover:bg-slate-100"
                    >
                        ล้างการค้นหา
                    </a>
                @endif
            </div>
        </form>

        @if ($competitions->isEmpty())

            {{-- ไม่มีข้อมูล --}}
            <section class="rounded-2xl border border-dashed
                            border-slate-300 bg-white px-6 py-16
                            text-center shadow-sm">

                <div class="mx-auto flex h-16 w-16 items-center
                            justify-center rounded-2xl bg-slate-100
                            text-slate-400">
                    <svg
                        class="h-8 w-8"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M3 7h6l2 2h10v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <path d="M3 7V5a2 2 0 012-2h4l2 2h8a2 2 0 012 2v2"/>
                    </svg>
                </div>

                <h2 class="mt-4 text-lg font-bold text-slate-800">
                    ไม่พบการแข่งขัน
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    @if ($search !== '')
                        ไม่พบการแข่งขันที่ตรงกับคำค้นหา
                        “{{ $search }}”
                    @else
                        ยังไม่มีการแข่งขันในระบบ
                    @endif
                </p>

                @if ($search !== '')
                    <a
                        href="{{ route(
                            'superadmin.competitions.judges.list'
                        ) }}"
                        class="mt-5 inline-flex rounded-xl bg-blue-600
                               px-5 py-3 text-sm font-semibold text-white
                               transition hover:bg-blue-700"
                    >
                        แสดงการแข่งขันทั้งหมด
                    </a>
                @endif
            </section>

        @else

            {{-- รายการแข่งขัน --}}
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

                @foreach ($competitions as $competition)
                    @php
                        $creatorProfile =
                            $competition->creator?->adminProfile;

                        $creatorName = trim(
                            ($creatorProfile?->first_name ?? '') . ' ' .
                            ($creatorProfile?->last_name ?? '')
                        );

                        $creatorName = $creatorName !== ''
                            ? $creatorName
                            : (
                                $competition->creator?->username
                                ?? 'ไม่ระบุผู้สร้าง'
                            );

                        $coverUrl = $competition->cover_image
                            ? asset(
                                'storage/' .
                                $competition->cover_image
                            )
                            : null;
                    @endphp

                    <article class="group overflow-hidden rounded-2xl
                                    border border-slate-200 bg-white
                                    shadow-sm transition
                                    hover:-translate-y-1
                                    hover:border-blue-300
                                    hover:shadow-lg">

                        {{-- รูปปก --}}
                        <div class="relative h-44 overflow-hidden
                                    bg-gradient-to-br from-blue-100
                                    via-sky-50 to-slate-100">

                            @if ($coverUrl)
                                <img
                                    src="{{ $coverUrl }}"
                                    alt="รูปปก {{ $competition->title }}"
                                    class="h-full w-full object-cover
                                           transition duration-300
                                           group-hover:scale-105"
                                >
                            @else
                                <div class="flex h-full items-center
                                            justify-center text-blue-300">
                                    <svg
                                        class="h-16 w-16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    >
                                        <path d="M3 7h6l2 2h10v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                        <path d="M3 7V5a2 2 0 012-2h4l2 2h8a2 2 0 012 2v2"/>
                                    </svg>
                                </div>
                            @endif

                            {{-- สถานะ --}}
                            <div class="absolute right-3 top-3">
                                @if ($competition->status === 'open')
                                    <span class="rounded-full border
                                                 border-emerald-200
                                                 bg-emerald-50 px-3 py-1
                                                 text-xs font-semibold
                                                 text-emerald-700 shadow-sm">
                                        เปิดรับผลงาน
                                    </span>
                                @elseif ($competition->status === 'draft')
                                    <span class="rounded-full border
                                                 border-slate-200
                                                 bg-slate-50 px-3 py-1
                                                 text-xs font-semibold
                                                 text-slate-600 shadow-sm">
                                        แบบร่าง
                                    </span>
                                @elseif ($competition->status === 'closed')
                                    <span class="rounded-full border
                                                 border-red-200 bg-red-50
                                                 px-3 py-1 text-xs
                                                 font-semibold text-red-700
                                                 shadow-sm">
                                        ปิดแล้ว
                                    </span>
                                @else
                                    <span class="rounded-full border
                                                 border-amber-200
                                                 bg-amber-50 px-3 py-1
                                                 text-xs font-semibold
                                                 text-amber-700 shadow-sm">
                                        {{ $competition->status }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- รายละเอียด --}}
                        <div class="p-5">

                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0
                                            items-center justify-center
                                            rounded-xl bg-blue-100
                                            text-blue-700">
                                    <svg
                                        class="h-6 w-6"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M3 7h6l2 2h10v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <h2 class="line-clamp-2 font-bold
                                               text-slate-900">
                                        {{ $competition->title }}
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $competition->category?->category_name
                                            ?? 'ไม่ระบุหมวดหมู่' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3 border-t
                                        border-slate-200 pt-4">

                                {{-- ผู้สร้าง --}}
                                <div class="flex items-center justify-between
                                            gap-4 text-sm">
                                    <span class="text-slate-500">
                                        ผู้ดูแลการแข่งขัน
                                    </span>

                                    <span class="truncate font-semibold
                                                 text-slate-700">
                                        {{ $creatorName }}
                                    </span>
                                </div>

                                {{-- จำนวนกรรมการ --}}
                                <div class="flex items-center justify-between
                                            gap-4 text-sm">
                                    <span class="text-slate-500">
                                        กรรมการที่แต่งตั้ง
                                    </span>

                                    <span class="inline-flex items-center
                                                 rounded-full bg-blue-100
                                                 px-2.5 py-1 text-xs
                                                 font-bold text-blue-700">
                                        {{ number_format(
                                            $competition
                                                ->judge_assignments_count
                                        ) }}
                                        คน
                                    </span>
                                </div>
                            </div>

                            {{-- ปุ่ม --}}
                            <a
                                href="{{ route(
                                    'superadmin.competitions.judges.index',
                                    $competition
                                ) }}"
                                class="mt-5 inline-flex w-full items-center
                                       justify-center gap-2 rounded-xl
                                       bg-green-600 px-4 py-3
                                       text-sm font-semibold text-white
                                       transition hover:bg-green-700
                                       focus:outline-none focus:ring-4
                                       focus:ring-blue-200"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M19 8v6"/>
                                    <path d="M22 11h-6"/>
                                </svg>

                                จัดการกรรมการ 
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($competitions->hasPages())
                <div class="mt-8">
                    {{ $competitions->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection