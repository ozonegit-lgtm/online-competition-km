@extends('layouts.app')

@section('title', $ebook->title)

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <h1 class="truncate text-xl font-bold text-slate-900 sm:text-2xl">{{ $ebook->title }}</h1>
        <p class="mt-1 text-xs text-slate-500 sm:text-sm">รายละเอียด E-Book ในระบบ</p>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row">
        <a
            href="{{ route('superadmin.knowledge-page.books.index') }}"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            กลับไปรายการ
        </a>

        <a
            href="{{ route('superadmin.knowledge-page.books.edit', $ebook) }}"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z"/>
            </svg>
            แก้ไข
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="mx-auto w-full max-w-6xl">
    <div class="grid gap-5 lg:grid-cols-[260px_minmax(0,1fr)] lg:items-start" data-ebook-id="{{ $ebook->id }}">

        {{-- Sidebar --}}
        <aside class="space-y-4 lg:sticky lg:top-24">

            {{-- Cover --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-slate-50 p-4">
                    @if ($ebook->cover_image_url)
                        <img
                            src="{{ $ebook->cover_image_url }}"
                            alt="ปก {{ $ebook->title }}"
                            class="mx-auto aspect-[3/4] w-full max-w-[210px] rounded-lg border border-slate-200 bg-white object-cover shadow-sm"
                        >
                    @else
                        <div class="mx-auto flex aspect-[3/4] w-full max-w-[210px] items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white px-4 text-center">
                            <div>
                                <svg class="mx-auto h-9 w-9 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path d="m21 15-5-5L5 21"/>
                                </svg>
                                <p class="mt-2 text-xs text-slate-400">ไม่มีรูปปก</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="border-t border-slate-100 px-4 py-3">
                    <p class="text-xs font-medium text-slate-500">สถานะ</p>

                    @if ($ebook->status === 'published')
                        <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            กำลังแสดง
                        </div>
                    @else
                        <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                            ถูกซ่อน
                        </div>
                    @endif
                </div>
            </section>

            {{-- Reading options --}}
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">ช่องทางอ่าน</h2>
                    <p class="mt-0.5 text-xs text-slate-500">เปิดอ่านหรือดาวน์โหลดเอกสาร</p>
                </div>

                <div class="mt-3 space-y-2">
                    @if ($ebook->attachment_path)
                        <a
                            href="{{ route('knowledge-items.attachment.inline', $ebook) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                                <path d="M14 2v6h6"/>
                            </svg>
                            อ่าน PDF
                        </a>

                        <a
                            href="{{ route('knowledge-items.attachment', $ebook) }}"
                            class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 3v12"/>
                                <path d="m7 10 5 5 5-5"/>
                                <path d="M5 21h14"/>
                            </svg>
                            ดาวน์โหลด PDF
                        </a>
                    @endif

                    @if ($ebook->external_url)
                        <a
                            href="{{ $ebook->external_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 3h7v7"/>
                                <path d="M10 14 21 3"/>
                                <path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>
                            </svg>
                            อ่านออนไลน์
                        </a>
                    @endif

                    @if (! $ebook->attachment_path && ! $ebook->external_url)
                        <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center">
                            <p class="text-xs text-slate-500">ยังไม่มีช่องทางอ่าน</p>
                        </div>
                    @endif
                </div>
            </section>
        </aside>

        {{-- Main --}}
        <main class="min-w-0 space-y-4">

            {{-- Overview --}}
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold leading-7 text-slate-900 sm:text-xl">{{ $ebook->title }}</h2>

                            @if ($ebook->knowledgeCategory)
                                <div class="mt-2 inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                    {{ $ebook->knowledgeCategory->name }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <dl class="grid grid-cols-2 border-b border-slate-100 sm:grid-cols-4">
                    <div class="border-b border-r border-slate-100 px-4 py-3 sm:border-b-0">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">ปีเผยแพร่</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $ebook->publication_year ?: '-' }}</dd>
                    </div>

                    <div class="border-b border-slate-100 px-4 py-3 sm:border-b-0 sm:border-r">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">เล่ม</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $ebook->volume ?: '-' }}</dd>
                    </div>

                    <div class="border-r border-slate-100 px-4 py-3">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">ฉบับ</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $ebook->issue ?: '-' }}</dd>
                    </div>

                    <div class="px-4 py-3">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">ลำดับการแสดง</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $ebook->sort_order }}</dd>
                    </div>
                </dl>

                @if ($ebook->summary)
                    <div class="px-4 py-4 sm:px-5">
                        <h3 class="text-sm font-semibold text-slate-900">รายละเอียดสั้น</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $ebook->summary }}</p>
                    </div>
                @endif
            </section>

            {{-- Content --}}
            @if ($ebook->content)
                <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                        <h2 class="text-sm font-semibold text-slate-900">เนื้อหา</h2>
                    </div>

                    <div class="px-4 py-4 sm:px-5 sm:py-5">
                        <div class="whitespace-pre-line text-sm leading-7 text-slate-700">{{ $ebook->content }}</div>
                    </div>
                </section>
            @endif

            {{-- Source information --}}
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                    <h2 class="text-sm font-semibold text-slate-900">ข้อมูลไฟล์และแหล่งอ่าน</h2>
                </div>

                <div class="divide-y divide-slate-100">
                    <div class="grid gap-1 px-4 py-3 sm:grid-cols-[150px_minmax(0,1fr)] sm:px-5">
                        <span class="text-xs font-medium text-slate-500">ไฟล์ PDF</span>

                        @if ($ebook->attachment_path)
                            <div class="min-w-0">
                                <p class="truncate text-sm text-slate-700">{{ $ebook->attachment_original_name ?: 'ebook.pdf' }}</p>
                            </div>
                        @else
                            <span class="text-sm text-slate-400">ไม่มีไฟล์</span>
                        @endif
                    </div>

                    <div class="grid gap-1 px-4 py-3 sm:grid-cols-[150px_minmax(0,1fr)] sm:px-5">
                        <span class="text-xs font-medium text-slate-500">External URL</span>

                        @if ($ebook->external_url)
                            <a
                                href="{{ $ebook->external_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="min-w-0 truncate text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline"
                            >
                                {{ $ebook->external_url }}
                            </a>
                        @else
                            <span class="text-sm text-slate-400">ไม่มี URL</span>
                        @endif
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
@endsection
