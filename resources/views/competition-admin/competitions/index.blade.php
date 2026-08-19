@extends('layouts.app')

@section('title', 'สร้างการแข่งขัน')

@section('header')
    <div>
        <h1 class="text-xl font-bold text-slate-800">สร้างการแข่งขัน</h1>
        <p class="mt-0.5 text-xs text-slate-500">เลือกการแข่งขันเดิมหรือสร้างการแข่งขันใหม่</p>
    </div>
@endsection

@section('content')
    <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">

        {{-- ปุ่มสร้าง --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <a href="{{ route('competition-admin.competitions.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                + สร้างการแข่งขันใหม่
            </a>
        </div>

        {{-- ค้นหา --}}
        <form action="{{ route('competition-admin.competitions.index') }}" method="GET"
            class="mt-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row">
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="ค้นหาชื่อการแข่งขัน..."
                    class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                >

                <button
                    type="submit"
                    class="rounded-lg bg-slate-800 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-700"
                >
                    ค้นหา
                </button>

                @if (request()->filled('q'))
                    <a
                        href="{{ route('competition-admin.competitions.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                        ล้างการค้นหา
                    </a>
                @endif
            </div>
        </form>

        {{-- รายการการแข่งขัน --}}
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

            @forelse ($competitions as $competition)

                @php
                    $status = $competition->display_status ?? 'draft';

                    $statusLabels = [
                        'draft' => 'ฉบับร่าง',
                        'published' => 'เผยแพร่แล้ว',
                        'open' => 'เปิดรับผลงาน',
                        'closed' => 'ปิดรับผลงาน',
                        'judging' => 'กำลังตัดสิน',
                        'completed' => 'เสร็จสิ้น',
                        'upcoming' => 'ยังไม่เปิดรับผลงาน',
                        'waiting_result' => 'รอประกาศผล',
                    ];

                    $statusClasses = [
                        'draft' => 'bg-slate-100 text-slate-600',
                        'published' => 'bg-blue-100 text-blue-700',
                        'open' => 'bg-emerald-100 text-emerald-700',
                        'closed' => 'bg-red-100 text-red-700',
                        'judging' => 'bg-violet-100 text-violet-700',
                        'completed' => 'bg-green-100 text-green-700',
                        'upcoming' => 'bg-amber-100 text-amber-700',
                        'waiting_result' => 'bg-slate-100 text-slate-700',
                    ];
                @endphp

                <article
                    class="flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >

                    {{-- รูปปก --}}
                    <div class="relative h-28 w-full shrink-0 overflow-hidden bg-slate-100 sm:h-32">

                        @if ($competition->cover_image)

                            <img
                                src="{{ asset('storage/' . $competition->cover_image) }}"
                                alt="ภาพปก {{ $competition->title }}"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >

                        @else

                            <div class="flex h-full w-full items-center justify-center px-4 text-center text-xs text-slate-400">
                                ไม่มีภาพปกการแข่งขัน
                            </div>

                        @endif

                        {{-- สถานะ --}}
                        <div class="absolute right-2 top-2 flex items-center gap-1">

                            <span
                                class="rounded-full px-2 py-0.5 text-[9px] font-bold shadow-sm {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-600' }}"
                            >
                                {{ $statusLabels[$status] ?? $status }}
                            </span>

                            @if ($competition->visibility === 'public')

                                <span
                                    class="rounded-full bg-blue-600 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm"
                                >
                                    🌐 Public
                                </span>

                            @else

                                <span
                                    class="rounded-full bg-red-600 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm"
                                >
                                    🔒 Private
                                </span>

                            @endif

                        </div>
                    </div>

                    {{-- เนื้อหาการ์ด --}}
                    <div class="flex flex-1 flex-col p-3">

                        <div class="flex-1">

                            <h3 class="line-clamp-2 text-sm font-bold leading-4 text-slate-800">
                                {{ $competition->title }}
                            </h3>

                            <p class="mt-1 line-clamp-2 min-h-8 text-[11px] leading-4 text-slate-500">
                                {{ $competition->description ?: 'ไม่มีรายละเอียดการแข่งขัน' }}
                            </p>

                            <div class="mt-2 space-y-1 border-t border-slate-100 pt-2 text-[11px]">

                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-slate-400">
                                        ประเภท
                                    </span>

                                    <span class="truncate text-right font-medium text-slate-700">
                                        {{ $competition->category?->category_name ?? '-' }}
                                    </span>
                                </div>

                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-slate-400">
                                        Template
                                    </span>

                                    <span class="truncate text-right font-medium text-slate-700">
                                        {{ $competition->template?->template_name ?? 'ไม่ใช้ Template' }}
                                    </span>
                                </div>

                            </div>
                        </div>

                        {{-- ปุ่ม --}}
                        <div class="mt-3 grid grid-cols-2 gap-1">

                            <a
                                href="{{ route('competition-admin.competitions.show', $competition) }}"
                                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-2 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700"
                            >
                                ดูรายละเอียด
                            </a>

                            <a
                                href="{{ route('competition-admin.competitions.edit', $competition) }}"
                                class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-white px-2 py-1.5 text-[11px] font-semibold text-blue-600 transition hover:bg-blue-50"
                            >
                                แก้ไข
                            </a>

                        </div>

                        <form
                            action="{{ route('competition-admin.competitions.destroy', $competition) }}"
                            method="POST"
                            class="mt-1"
                            onsubmit="return confirm('ต้องการลบการแข่งขันนี้หรือไม่?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="w-full rounded-lg bg-rose-50 px-2 py-1.5 text-[11px] font-semibold text-rose-600 transition hover:bg-rose-100"
                            >
                                ลบการแข่งขัน
                            </button>
                        </form>

                    </div>
                </article>

            @empty

                <div
                    class="flex min-h-64 flex-col items-center justify-center rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm sm:col-span-2 xl:col-span-2"
                >
                    <h3 class="text-sm font-bold text-slate-800">
                        {{ request()->filled('q') ? 'ไม่พบการแข่งขันที่ค้นหา' : 'ยังไม่มีการแข่งขัน' }}
                    </h3>

                    <p class="mt-1.5 max-w-md text-xs leading-5 text-slate-500">
                        {{ request()->filled('q')
                            ? 'ลองเปลี่ยนคำค้นหา หรือล้างการค้นหาเพื่อดูการแข่งขันทั้งหมด'
                            : 'เริ่มต้นสร้างการแข่งขันและเลือก Template สำหรับรับผลงานประกวด' }}
                    </p>
                </div>

            @endforelse

            {{-- สร้างการแข่งขันใหม่ --}}
            <a
                href="{{ route('competition-admin.competitions.create') }}"
                class="group flex min-h-64 flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-300 bg-blue-50/40 p-5 text-center transition hover:border-blue-500 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-100"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-blue-500 bg-white text-2xl font-light text-blue-600 transition group-hover:scale-105"
                >
                    +
                </span>

                <span class="mt-2.5 text-sm font-bold text-blue-700">
                    สร้างการแข่งขันใหม่
                </span>

                <span class="mt-1 text-xs leading-5 text-slate-500">
                    เลือกประเภทและ Template เพื่อเริ่มสร้างการแข่งขัน
                </span>
            </a>

        </div>

        {{-- Pagination --}}
        @if (method_exists($competitions, 'links') && $competitions->hasPages())
            <div class="mt-6">
                {{ $competitions->withQueryString()->links() }}
            </div>
        @endif

    </div>
@endsection