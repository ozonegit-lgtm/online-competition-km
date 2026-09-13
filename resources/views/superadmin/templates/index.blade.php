@extends('layouts.app')

@section('title', 'จัดการ Templates')

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8M8 13h5"/>
                </svg>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">
                    จัดการ Templates
                </h1>
                <p class="mt-0.5 text-xs text-slate-500">
                    จัดการแม่แบบการแข่งขันและแบบฟอร์มที่ใช้สร้างการแข่งขัน
                </p>
            </div>
        </div>

        <a
            href="{{ route('superadmin.templates.create') }}"
            class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
            </svg>
            สร้าง Template
        </a>
    </div>
@endsection

@section('content')
    @php
        $templateTotal = method_exists($competitionTemplates, 'total')
            ? $competitionTemplates->total()
            : $competitionTemplates->count();

        $hasSearch = filled(request('q'));
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-8">

        {{-- Search --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <form
                method="GET"
                action="{{ url()->current() }}"
                class="p-3 sm:p-4"
            >
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
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
                            id="template-search"
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="ค้นหาชื่อ Template หรือ Slug..."
                            autocomplete="off"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pl-11 pr-4 text-sm font-medium text-slate-700 outline-none transition placeholder:font-normal placeholder:text-slate-400 hover:border-slate-300 hover:bg-white focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        >
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($hasSearch)
                            <a
                                href="{{ url()->current() }}"
                                title="ล้างการค้นหา"
                                aria-label="ล้างการค้นหา"
                                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-100"
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
                            </a>
                        @endif

                        <button
                            type="submit"
                            class="inline-flex h-11 flex-1 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 sm:flex-none"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <circle cx="11" cy="11" r="7"/>
                                <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                            </svg>

                            ค้นหา
                        </button>
                    </div>
                </div>

                <div class="mt-3 flex flex-col gap-2 border-t border-slate-100 pt-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        @if ($hasSearch)
                            <p class="truncate text-xs text-slate-500">
                                ผลการค้นหาสำหรับ
                                <span class="font-semibold text-slate-800">
                                    “{{ request('q') }}”
                                </span>
                            </p>
                        @else
                            <p class="text-xs text-slate-500">
                                ค้นหา Template จากชื่อหรือ Slug
                            </p>
                        @endif
                    </div>

                    <span class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                        <svg
                            class="h-3.5 w-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" d="M5 7h14M5 12h14M5 17h9"/>
                        </svg>

                        {{ number_format($templateTotal) }} รายการ
                    </span>
                </div>
            </form>
        </section>

        {{-- Section header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">
                    Template ทั้งหมด
                </h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    เลือก Template เพื่อดูรายละเอียดหรือจัดการ Form Builder
                </p>
            </div>

            <p class="text-xs font-medium text-slate-500">
                แสดง {{ number_format($competitionTemplates->count()) }} รายการในหน้านี้
            </p>
        </div>

        <div class="grid items-stretch gap-4 sm:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
            @forelse($competitionTemplates as $template)
                <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-blue-200 hover:shadow-md">

                    {{-- Cover --}}
                    <div class="relative h-40 shrink-0 overflow-hidden border-b border-slate-100 bg-slate-100">
                        @if($template->cover_image)
                            <img
                                src="{{ asset('storage/' . $template->cover_image) }}"
                                alt="รูปปก {{ $template->template_name }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                            >
                        @else
                            <div class="flex h-full flex-col items-center justify-center gap-2 bg-gradient-to-br from-blue-50 via-slate-50 to-white text-slate-400">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white ring-1 ring-slate-200">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8M8 13h5"/>
                                    </svg>
                                </div>
                                <span class="text-[11px] font-medium">
                                    ไม่มีรูปปก
                                </span>
                            </div>
                        @endif

                        <div class="absolute left-3 top-3">
                            @if($template->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50/95 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 shadow-sm backdrop-blur">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    เปิดใช้งาน
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50/95 px-2.5 py-1 text-[11px] font-semibold text-rose-700 shadow-sm backdrop-blur">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                    ปิดใช้งาน
                                </span>
                            @endif
                        </div>

                        <div class="absolute right-3 top-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/70 bg-white/90 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm backdrop-blur">
                                <svg class="h-3 w-3 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h7"/>
                                </svg>
                                {{ number_format($template->form_fields_count ?? 0) }} ฟิลด์
                            </span>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="flex flex-1 flex-col p-4">
                        <div class="min-w-0">
                            <h3 class="line-clamp-2 min-h-[48px] text-base font-bold leading-6 text-slate-900 transition group-hover:text-blue-700">
                                {{ $template->template_name }}
                            </h3>

                            <div class="mt-2">
                                <span class="inline-flex max-w-full items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 font-mono text-[11px] font-medium text-slate-600">
                                    <svg class="h-3 w-3 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 11a5 5 0 0 0-7.1-.1l-2 2A5 5 0 0 0 12 20l1.1-1.1"/>
                                    </svg>
                                    <span class="truncate">{{ $template->template_slug }}</span>
                                </span>
                            </div>

                            <p class="mt-3 line-clamp-3 min-h-[60px] text-xs leading-5 text-slate-500">
                                {{ $template->default_description ?: 'ไม่มีรายละเอียดสำหรับ Template นี้' }}
                            </p>
                        </div>

                        {{-- Metadata --}}
                        <div class="mt-3 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2.5">
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-1.5 text-[11px] font-medium text-slate-500">
                                    <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h7"/>
                                    </svg>
                                    Form Builder
                                </span>

                                <span class="text-xs font-semibold {{ ($template->form_fields_count ?? 0) > 0 ? 'text-emerald-700' : 'text-slate-500' }}">
                                    {{ ($template->form_fields_count ?? 0) > 0 ? 'พร้อมใช้งาน' : 'ยังไม่ได้สร้าง' }}
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="mt-auto space-y-2 border-t border-slate-100 pt-3">
                            @if (($template->form_fields_count ?? 0) > 0)
                                <a
                                    href="{{ route('superadmin.templates.form-fields.edit', $template) }}"
                                    class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                    </svg>
                                    แก้ไข Form
                                </a>
                            @else
                                <a
                                    href="{{ route('superadmin.templates.form-fields.create', $template) }}"
                                    class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                                    </svg>
                                    สร้าง Form
                                </a>
                            @endif

                            <div class="grid grid-cols-2 gap-2">
                                <a
                                    href="{{ route('superadmin.templates.show', $template) }}"
                                    class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-white px-3 text-xs font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-300"
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                    </svg>
                                    ดูรายละเอียด
                                </a>

                                <form
                                    action="{{ route('superadmin.templates.destroy', $template) }}"
                                    method="POST"
                                    class="w-full"
                                    onsubmit="return confirm('ต้องการลบ Template นี้หรือไม่?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-white px-3 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-300"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 14H6L5 6"/>
                                        </svg>
                                        ลบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center shadow-sm sm:py-14">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-500 ring-1 ring-blue-100">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8M8 13h5"/>
                        </svg>
                    </div>

                    <h3 class="mt-3 text-sm font-bold text-slate-800">
                        ยังไม่มี Template
                    </h3>

                    <p class="mx-auto mt-1 max-w-md text-xs leading-5 text-slate-500">
                        เริ่มต้นด้วยการสร้าง Template เพื่อใช้เป็นแม่แบบสำหรับการแข่งขันและกำหนด Form Builder
                    </p>

                    <a
                        href="{{ route('superadmin.templates.create') }}"
                        class="mt-4 inline-flex h-8 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
                        </svg>
                        สร้าง Template
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if(method_exists($competitionTemplates, 'links'))
            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-slate-500">
                    แสดง
                    <span class="font-semibold text-slate-700">
                        {{ number_format($competitionTemplates->count()) }}
                    </span>
                    จาก
                    <span class="font-semibold text-slate-700">
                        {{ number_format($templateTotal) }}
                    </span>
                    รายการ
                </div>

                <div>
                    {{ $competitionTemplates->onEachSide(1)->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
