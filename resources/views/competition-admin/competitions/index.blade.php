@extends('layouts.app')

@section('title', 'สร้างการแข่งขัน')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">สร้างการแข่งขัน</h1>
        <p class="mt-1 text-sm text-slate-500">เลือกการแข่งขันเดิมหรือสร้างการแข่งขันใหม่</p>
    </div>
@endsection

@section('content')
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">การแข่งขันของฉัน</h2>
                <p class="mt-1 text-sm text-slate-500">
                    จัดการการแข่งขัน ดูรายละเอียด หรือสร้างการแข่งขันรายการใหม่
                </p>
            </div>

            <a href="{{ route('competition-admin.competitions.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                + สร้างการแข่งขันใหม่
            </a>
        </div>

        <form action="{{ route('competition-admin.competitions.index') }}" method="GET"
            class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row">
                <input type="search" name="q" value="{{ request('q') }}"
                    placeholder="ค้นหาชื่อการแข่งขัน..."
                    class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">

                <button type="submit"
                    class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                    ค้นหา
                </button>

                @if (request()->filled('q'))
                    <a href="{{ route('competition-admin.competitions.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        ล้างการค้นหา
                    </a>
                @endif
            </div>
        </form>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($competitions as $competition)
                @php
                    $status = $competition->status ?? 'draft';
                    $statusLabels = [
                        'draft' => 'ฉบับร่าง',
                        'published' => 'เผยแพร่แล้ว',
                        'open' => 'เปิดรับผลงาน',
                        'closed' => 'ปิดรับผลงาน',
                        'judging' => 'กำลังตัดสิน',
                        'completed' => 'เสร็จสิ้น',
                    ];
                    $statusClasses = [
                        'draft' => 'bg-slate-100 text-slate-600',
                        'published' => 'bg-blue-100 text-blue-700',
                        'open' => 'bg-emerald-100 text-emerald-700',
                        'closed' => 'bg-amber-100 text-amber-700',
                        'judging' => 'bg-violet-100 text-violet-700',
                        'completed' => 'bg-green-100 text-green-700',
                    ];
                @endphp

                <article
                    class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">
                    <div class="relative h-44 w-full shrink-0 overflow-hidden bg-slate-100">
                        @if ($competition->cover_image)
                            <img src="{{ asset('storage/' . $competition->cover_image) }}"
                                alt="ภาพปก {{ $competition->title }}"
                                class="h-full w-full object-cover"
                                loading="lazy">
                        @else
                            <div class="flex h-full w-full items-center justify-center px-6 text-center text-sm text-slate-400">
                                ไม่มีภาพปกการแข่งขัน
                            </div>
                        @endif

                        <span
                            class="absolute right-3 top-3 rounded-full px-3 py-1 text-xs font-semibold shadow-sm {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $statusLabels[$status] ?? $status }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex-1">
                            <h3 class="line-clamp-2 text-lg font-bold text-slate-800">
                                {{ $competition->title }}
                            </h3>

                            <p class="mt-2 line-clamp-2 min-h-10 text-sm leading-5 text-slate-500">
                                {{ $competition->description ?: 'ไม่มีรายละเอียดการแข่งขัน' }}
                            </p>

                            <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 text-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-slate-400">ประเภท</span>
                                    <span class="text-right font-medium text-slate-700">
                                        {{ $competition->category?->category_name ?? '-' }}
                                    </span>
                                </div>

                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-slate-400">Template</span>
                                    <span class="text-right font-medium text-slate-700">
                                        {{ $competition->template?->template_name ?? 'ไม่ใช้ Template' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-2">
                            <a href="{{ route('competition-admin.competitions.show', $competition) }}"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                                ดูรายละเอียด
                            </a>

                            <a href="{{ route('competition-admin.competitions.edit', $competition) }}"
                                class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-600 transition hover:bg-blue-50">
                                แก้ไข
                            </a>
                        </div>

                        <form action="{{ route('competition-admin.competitions.destroy', $competition) }}"
                            method="POST" class="mt-2"
                            onsubmit="return confirm('ต้องการลบการแข่งขันนี้หรือไม่?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="w-full rounded-xl bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-100">
                                ลบการแข่งขัน
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div
                    class="flex min-h-96 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:col-span-2 xl:col-span-2">
                    <h3 class="text-lg font-bold text-slate-800">
                        {{ request()->filled('q') ? 'ไม่พบการแข่งขันที่ค้นหา' : 'ยังไม่มีการแข่งขัน' }}
                    </h3>
                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                        {{ request()->filled('q') ? 'ลองเปลี่ยนคำค้นหา หรือล้างการค้นหาเพื่อดูการแข่งขันทั้งหมด' : 'เริ่มต้นสร้างการแข่งขันและเลือก Template สำหรับรับผลงานประกวด' }}
                    </p>
                </div>
            @endforelse

            <a href="{{ route('competition-admin.competitions.create') }}"
                class="group flex min-h-96 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-blue-300 bg-blue-50/40 p-8 text-center transition hover:border-blue-500 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-100">
                <span
                    class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-blue-500 bg-white text-4xl font-light text-blue-600 transition group-hover:scale-105">
                    +
                </span>
                <span class="mt-5 text-lg font-bold text-blue-700">สร้างการแข่งขันใหม่</span>
                <span class="mt-2 text-sm leading-6 text-slate-500">
                    เลือกประเภทและ Template เพื่อเริ่มสร้างการแข่งขัน
                </span>
            </a>
        </div>

        @if (method_exists($competitions, 'links') && $competitions->hasPages())
            <div class="mt-8">
                {{ $competitions->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
