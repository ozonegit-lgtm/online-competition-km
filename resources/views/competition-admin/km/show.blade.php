@extends('layouts.app')

@section('title', $knowledgeItem->title)

@section('header')
    <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
            </svg>
        </div>

        <div>
            <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">
                รายละเอียดองค์ความรู้
            </h1>
            <p class="mt-0.5 text-xs text-slate-500">
                ตรวจสอบเนื้อหา สถานะ และจัดการรายการ
            </p>
        </div>
    </div>
@endsection
@section('content')
    @php
        $isCompetition = $knowledgeItem->submission_id !== null;
        $attachmentMedia = ! $isCompetition
            ? $knowledgeItem->attachmentMedia()
            : ['exists' => false, 'is_image' => false, 'type' => 'FILE'];
        $submissionImage = $isCompetition
            ? $knowledgeItem->submission?->files
                ?->filter(fn ($file) => str_starts_with((string) $file->mime_type, 'image/'))
                ->sortBy([
                    ['is_primary', 'desc'],
                    ['id', 'asc'],
                ])
                ->first()
            : null;
        $coverUrl = $knowledgeItem->cover_image
            ? $knowledgeItem->cover_image_url
            : ($isCompetition
                ? $submissionImage?->file_url
                : ($attachmentMedia['is_image']
                    ? route('knowledge-items.cover', $knowledgeItem)
                    : null));
        $mediaKind = $knowledgeItem->cover_image
            ? 'cover'
            : ($isCompetition ? 'submission-image' : 'attachment-image');

        $statusLabel = match ($knowledgeItem->status) {
            'published' => 'เผยแพร่แล้ว',
            'hidden' => 'ซ่อนอยู่',
            'draft' => 'ฉบับร่าง',
            default => ucfirst($knowledgeItem->status),
        };

        $statusClass = match ($knowledgeItem->status) {
            'published' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'hidden' => 'border-slate-200 bg-slate-100 text-slate-600',
            default => 'border-amber-200 bg-amber-50 text-amber-700',
        };

        $statusDot = match ($knowledgeItem->status) {
            'published' => 'bg-emerald-500',
            'hidden' => 'bg-slate-400',
            default => 'bg-amber-500',
        };
    @endphp

    <div id="km-detail" class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="mb-4 flex min-w-0 items-center gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
            <a
                href="{{ route('competition-admin.km.index') }}"
                class="inline-flex shrink-0 items-center gap-1.5 font-medium text-slate-500 transition hover:text-emerald-700"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                จัดการองค์ความรู้
            </a>

            <span aria-hidden="true">/</span>

            <span class="min-w-0 truncate font-medium text-slate-700">
                {{ $knowledgeItem->title }}
            </span>
        </nav>

        {{-- Primary identity: title first, status second --}}
        <section class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold
                                {{ $isCompetition
                                    ? 'border-blue-200 bg-blue-50 text-blue-700'
                                    : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}"
                        >
                            @if ($isCompetition)
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M8 21h8"/>
                                    <path d="M12 17v4"/>
                                    <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                    <path d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4"/>
                                    <path d="M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                                </svg>
                                การแข่งขัน
                            @else
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                </svg>
                                Manual KM
                            @endif
                        </span>

                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $statusDot }}"></span>
                            {{ $statusLabel }}
                        </span>

                        @if ($knowledgeItem->is_featured)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>
                                </svg>
                                Featured
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-3 break-words text-2xl font-bold leading-tight tracking-tight text-slate-900 sm:text-3xl">
                        {{ $knowledgeItem->title }}
                    </h2>

                    @if ($knowledgeItem->summary)
                        <p class="mt-3 max-w-4xl whitespace-pre-line text-sm leading-7 text-slate-600">
                            {{ $knowledgeItem->summary }}
                        </p>
                    @else
                        <p class="mt-2 text-sm text-slate-400">
                            ไม่มีบทสรุปสำหรับรายการนี้
                        </p>
                    @endif
                </div>

                <a
                    href="{{ route('competition-admin.km.index') }}"
                    class="inline-flex h-8 shrink-0 items-center justify-center gap-1.5 self-start rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    กลับหน้ารายการ
                </a>
            </div>
        </section>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
            {{-- Sidebar comes first on mobile so management actions are easy to reach --}}
            <aside class="order-1 space-y-4 lg:order-2 lg:sticky lg:top-4">
                {{-- Actions --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3">
                        <h3 class="text-sm font-bold text-slate-900">จัดการรายการ</h3>
                        <p class="mt-0.5 text-xs text-slate-500">คำสั่งหลักสำหรับองค์ความรู้นี้</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 lg:grid-cols-1">
                        @can('update', $knowledgeItem)
                            <a
                                href="{{ route('competition-admin.km.edit', $knowledgeItem) }}"
                                class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                </svg>
                                แก้ไข
                            </a>
                        @endcan

                        @if ($knowledgeItem->status === 'published')
                            @can('unpublish', $knowledgeItem)
                                <x-ajax-form
                                    target="#km-detail"
                                    :action="route('competition-admin.km.unpublish', $knowledgeItem)"
                                    method="DELETE"
                                    confirm="ยืนยันถอนเผยแพร่?"
                                    success="ถอนเผยแพร่เรียบร้อย"
                                    class="w-full"
                                >
                                    <button
                                        type="submit"
                                        class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-semibold text-amber-700 shadow-sm transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-300"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M3 3l18 18"/>
                                            <path d="M10.6 10.6A2 2 0 0 0 12 14a2 2 0 0 0 1.4-.6"/>
                                            <path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c5 0 9 4 10 8a11.6 11.6 0 0 1-2.2 4.1"/>
                                            <path d="M6.6 6.6A11.6 11.6 0 0 0 2 12c1 4 5 8 10 8a10.8 10.8 0 0 0 3.1-.5"/>
                                        </svg>
                                        ถอนเผยแพร่
                                    </button>
                                </x-ajax-form>
                            @endcan
                        @else
                            @can('publish', $knowledgeItem)
                                <x-ajax-form
                                    target="#km-detail"
                                    :action="route('competition-admin.km.publish', $knowledgeItem)"
                                    method="POST"
                                    confirm="ยืนยันเผยแพร่?"
                                    success="เผยแพร่เรียบร้อย"
                                    class="w-full"
                                >
                                    <button
                                        type="submit"
                                        class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-300"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/>
                                            <circle cx="12" cy="12" r="2.5"/>
                                        </svg>
                                        เผยแพร่
                                    </button>
                                </x-ajax-form>
                            @endcan
                        @endif

                    </div>
                </section>

                {{-- Compact metadata instead of nested cards --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-1">
                        <h3 class="text-sm font-bold text-slate-900">ข้อมูลรายการ</h3>
                        <p class="mt-0.5 text-xs text-slate-500">รายละเอียดประกอบขององค์ความรู้</p>
                    </div>

                    <dl class="divide-y divide-slate-100">
                        <div class="flex items-start justify-between gap-4 py-3">
                            <dt class="text-xs font-medium text-slate-500">เจ้าของ</dt>
                            <dd class="min-w-0 break-words text-right text-xs font-semibold text-slate-800">
                                {{ $knowledgeItem->creator?->username ?? 'ไม่มีเจ้าของ' }}
                            </dd>
                        </div>

                        <div class="flex items-start justify-between gap-4 py-3">
                            <dt class="text-xs font-medium text-slate-500">หมวดหมู่</dt>
                            <dd class="min-w-0 break-words text-right text-xs font-semibold text-slate-800">
                                {{ $knowledgeItem->category?->category_name ?? '-' }}
                            </dd>
                        </div>

                        <div class="flex items-start justify-between gap-4 py-3">
                            <dt class="text-xs font-medium text-slate-500">เผยแพร่เมื่อ</dt>
                            <dd class="min-w-0 text-right text-xs font-semibold text-slate-800">
                                {{ $knowledgeItem->published_at?->format('d/m/Y H:i') ?? '-' }}
                            </dd>
                        </div>

                        <div class="flex items-start justify-between gap-4 py-3">
                            <dt class="text-xs font-medium text-slate-500">แหล่งที่มา</dt>
                            <dd class="min-w-0 text-right text-xs font-semibold text-slate-800">
                                {{ $isCompetition ? 'ผลงานการแข่งขัน' : 'Manual KM' }}
                            </dd>
                        </div>

                        @if ($isCompetition)
                            <div class="py-3">
                                <dt class="text-xs font-medium text-slate-500">การแข่งขัน</dt>
                                <dd class="mt-1 break-words text-xs font-semibold leading-5 text-slate-800">
                                    {{ $knowledgeItem->submission?->competition?->title ?? '-' }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>

                {{-- Destructive action intentionally separated --}}
                @can('delete', $knowledgeItem)
                    <section class="rounded-2xl border border-red-100 bg-red-50/40 p-4">
                        <h3 class="text-sm font-bold text-red-700">การดำเนินการที่มีผลต่อรายการ</h3>
                        <p class="mt-1 text-xs leading-5 text-red-600/80">
                            การลบเป็นคำสั่งแยกจากการจัดการสถานะทั่วไป กรุณาตรวจสอบก่อนดำเนินการ
                        </p>

                        <x-ajax-form
                            :redirect="route('competition-admin.km.index')"
                            :action="route('competition-admin.km.destroy', $knowledgeItem)"
                            method="DELETE"
                            confirm="ยืนยันลบองค์ความรู้นี้?"
                            success="ลบเรียบร้อย"
                            class="mt-3"
                        >
                            <button
                                type="submit"
                                class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 shadow-sm transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4h8v2"/>
                                    <path d="M19 6l-1 14H6L5 6"/>
                                    <path d="M10 11v5M14 11v5"/>
                                </svg>
                                ลบรายการ
                            </button>
                        </x-ajax-form>
                    </section>
                @endcan
            </aside>

            {{-- Main reading area --}}
            <main class="order-2 min-w-0 space-y-4 lg:order-1">
                {{-- Cover is supportive, not the dominant element --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">รูปปก</h3>
                            <p class="mt-0.5 text-xs text-slate-500">ภาพประกอบขององค์ความรู้</p>
                        </div>
                    </div>

                    @if ($coverUrl)
                        <div class="flex min-h-[180px] items-center justify-center bg-slate-50 p-4 sm:min-h-[240px] sm:p-5">
                            <img
                                data-km-media="{{ $mediaKind }}"
                                src="{{ $coverUrl }}"
                                alt="{{ $knowledgeItem->title }}"
                                class="max-h-[360px] w-auto max-w-full rounded-xl object-contain shadow-sm"
                            >
                        </div>
                        @if(! $knowledgeItem->cover_image)
                            <div class="border-t border-slate-100 bg-slate-50 px-4 py-2 text-xs text-slate-500 sm:px-5">
                                {{ $isCompetition ? 'ใช้รูปจากไฟล์ผลงาน' : 'ใช้รูปจากไฟล์แนบ' }}
                            </div>
                        @endif
                    @elseif(! $isCompetition && $attachmentMedia['exists'])
                        <div data-km-media="document" data-file-type="{{ $attachmentMedia['type'] }}" class="flex min-h-[180px] flex-col items-center justify-center gap-3 bg-slate-50 p-5 text-center sm:min-h-[240px]">
                            <svg class="h-11 w-11 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path d="M6 2h9l3 3v17H6z"/>
                                <path d="M14 2v4h4"/>
                                <path d="M9 13h6M9 17h4"/>
                            </svg>
                            <div>
                                <p class="text-base font-bold text-slate-700">{{ $attachmentMedia['type'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">ไม่มีรูปปก</p>
                                <p class="mt-0.5 text-xs text-slate-400">ไฟล์แนบเป็นเอกสาร {{ $attachmentMedia['type'] }}</p>
                            </div>
                        </div>
                    @else
                        <div data-km-media="empty" class="flex items-center gap-3 bg-slate-50 px-4 py-5 text-slate-400 sm:px-5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white ring-1 ring-slate-200">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                                    <circle cx="8.5" cy="9" r="1.5"/>
                                    <path d="m4 17 5-5 4 4 2-2 5 4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500">ไม่มีรูปปก</p>
                                <p class="mt-0.5 text-xs text-slate-400">รายการนี้ไม่ได้กำหนดภาพประกอบ</p>
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Content --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="border-b border-slate-100 pb-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">Content</p>
                        <h3 class="mt-1 text-base font-bold text-slate-900">เนื้อหา</h3>
                    </div>

                    @if ($knowledgeItem->content)
                        <div class="mt-4 max-w-4xl whitespace-pre-line break-words text-sm leading-7 text-slate-700">
                            {{ $knowledgeItem->content }}
                        </div>
                    @else
                        <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center">
                            <p class="text-xs font-medium text-slate-500">ยังไม่มีเนื้อหาเพิ่มเติม</p>
                        </div>
                    @endif
                </section>

                {{-- Attachment --}}
                @if ($knowledgeItem->attachment_path)
                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-3">
                            <h3 class="text-sm font-bold text-slate-900">ไฟล์แนบ</h3>
                            <p class="mt-0.5 text-xs text-slate-500">เอกสารหรือไฟล์ประกอบรายการ</p>
                        </div>

                        <div class="flex flex-col gap-3 rounded-xl border border-blue-100 bg-blue-50/50 p-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-blue-600 ring-1 ring-blue-100">
                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M6 2h9l3 3v17H6z"/>
                                        <path d="M14 2v4h4"/>
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-[11px] font-medium text-blue-500">ไฟล์แนบ</p>
                                    <p class="mt-0.5 truncate text-xs font-semibold text-slate-700">
                                        {{ $knowledgeItem->attachment_original_name ?: 'ไฟล์แนบ' }}
                                    </p>
                                </div>
                            </div>

                            <a
                                href="{{ route('knowledge-items.attachment', $knowledgeItem) }}"
                                class="inline-flex h-8 shrink-0 items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-white px-3 text-xs font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-300"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 3v12"/>
                                    <path d="m7 10 5 5 5-5"/>
                                    <path d="M5 21h14"/>
                                </svg>
                                เปิดไฟล์
                            </a>
                        </div>
                    </section>
                @endif
            </main>
        </div>
    </div>
@endsection
