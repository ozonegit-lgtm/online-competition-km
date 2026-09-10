@extends('layouts.app')

@section('title', $knowledgeItem->title)

@section('header')
    <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
            <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
            </svg>
        </div>

        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">
                รายละเอียดองค์ความรู้
            </h1>

            <p class="mt-1 line-clamp-1 text-xs text-slate-500">
                {{ $knowledgeItem->title }}
            </p>
        </div>
    </div>
@endsection

@section('content')
    @php
        $isCompetition = $knowledgeItem->submission_id !== null;

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

    <div class="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-slate-400">
            <a
                href="{{ route('superadmin.km.index') }}"
                class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-emerald-700"
            >
                <svg
                    class="h-3.5 w-3.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path d="m15 18-6-6 6-6"/>
                </svg>

                จัดการองค์ความรู้
            </a>

            <span>/</span>

            <span class="max-w-[280px] truncate font-medium text-slate-700 sm:max-w-lg">
                {{ $knowledgeItem->title }}
            </span>
        </div>

        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Cover --}}
            <div class="relative border-b border-slate-200 bg-slate-50">
                @if ($knowledgeItem->cover_image)
                    @php
                        $coverUrl = $knowledgeItem->cover_image_url;
                    @endphp

                    <div class="flex min-h-[220px] items-center justify-center p-4 sm:min-h-[300px]">
                        <img
                            src="{{ $coverUrl }}"
                            alt="{{ $knowledgeItem->title }}"
                            class="max-h-[460px] w-auto max-w-full rounded-xl object-contain"
                        >
                    </div>
                @else
                    <div class="flex h-48 flex-col items-center justify-center gap-2 text-slate-400 sm:h-56">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <circle cx="8.5" cy="9" r="1.5"/>
                                <path d="m4 17 5-5 4 4 2-2 5 4"/>
                            </svg>
                        </div>

                        <span class="text-xs font-medium">
                            ไม่มีรูปปก
                        </span>
                    </div>
                @endif
            </div>

            <div class="space-y-6 p-4 sm:p-6">

                {{-- Header --}}
                <div>
                    <div class="flex flex-wrap items-center gap-2">

                        {{-- Source --}}
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold
                                {{ $isCompetition
                                    ? 'border-blue-200 bg-blue-50 text-blue-700'
                                    : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}"
                        >
                            @if ($isCompetition)
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path d="M8 21h8"/>
                                    <path d="M12 17v4"/>
                                    <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                    <path d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4"/>
                                    <path d="M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                                </svg>

                                การแข่งขัน
                            @else
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                </svg>

                                Manual KM
                            @endif
                        </span>

                        {{-- Status --}}
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $statusDot }}"></span>
                            {{ $statusLabel }}
                        </span>

                        {{-- Featured --}}
                        @if ($knowledgeItem->is_featured)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>
                                </svg>

                                Featured
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-3 text-xl font-bold leading-8 text-slate-900 sm:text-2xl">
                        {{ $knowledgeItem->title }}
                    </h2>
                </div>

                {{-- Information --}}
                <section>
                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 10v6M12 7h.01"/>
                            </svg>
                        </div>

                        <h3 class="text-sm font-bold text-slate-800">
                            ข้อมูลรายการ
                        </h3>
                    </div>

                    <dl class="grid gap-3 sm:grid-cols-2">
                        {{-- Owner --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                            <dt class="flex items-center gap-2 text-xs font-medium text-slate-400">
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 21a8 8 0 0 1 16 0"/>
                                </svg>

                                เจ้าของ
                            </dt>

                            <dd class="mt-1.5 text-sm font-semibold text-slate-700">
                                {{ $knowledgeItem->creator?->username ?? 'ไม่มีเจ้าของ' }}
                            </dd>
                        </div>

                        {{-- Category --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                            <dt class="flex items-center gap-2 text-xs font-medium text-slate-400">
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                                </svg>

                                หมวดหมู่
                            </dt>

                            <dd class="mt-1.5 text-sm font-semibold text-slate-700">
                                {{ $knowledgeItem->category?->category_name ?? '-' }}
                            </dd>
                        </div>

                        {{-- Published At --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                            <dt class="flex items-center gap-2 text-xs font-medium text-slate-400">
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M16 3v4M8 3v4M3 10h18"/>
                                </svg>

                                วันเผยแพร่
                            </dt>

                            <dd class="mt-1.5 text-sm font-semibold text-slate-700">
                                {{ $knowledgeItem->published_at?->format('d/m/Y H:i') ?? '-' }}
                            </dd>
                        </div>

                        {{-- Competition --}}
                        @if ($isCompetition)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                                <dt class="flex items-center gap-2 text-xs font-medium text-slate-400">
                                    <svg
                                        class="h-3.5 w-3.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path d="M8 21h8"/>
                                        <path d="M12 17v4"/>
                                        <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                                    </svg>

                                    การแข่งขัน
                                </dt>

                                <dd class="mt-1.5 text-sm font-semibold text-slate-700">
                                    {{ $knowledgeItem->submission?->competition?->title ?? '-' }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>

                {{-- Summary --}}
                @if ($knowledgeItem->summary)
                    <section class="border-t border-slate-100 pt-5">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path d="M8 6h13M8 12h13M8 18h9"/>
                                    <path d="M3 6h.01M3 12h.01M3 18h.01"/>
                                </svg>
                            </div>

                            <h3 class="text-base font-bold text-slate-800">
                                บทสรุป
                            </h3>
                        </div>

                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">
                            {{ $knowledgeItem->summary }}
                        </p>
                    </section>
                @endif

                {{-- Content --}}
                @if ($knowledgeItem->content)
                    <section class="border-t border-slate-100 pt-5">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path d="M6 3h9l3 3v15H6z"/>
                                    <path d="M14 3v4h4M9 11h6M9 15h6"/>
                                </svg>
                            </div>

                            <h3 class="text-base font-bold text-slate-800">
                                เนื้อหา
                            </h3>
                        </div>

                        <div class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">
                            {{ $knowledgeItem->content }}
                        </div>
                    </section>
                @endif

                {{-- Attachment --}}
                @if ($knowledgeItem->attachment_path)
                    <section class="border-t border-slate-100 pt-5">
                        <div class="rounded-xl border border-blue-200 bg-blue-50/60 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm ring-1 ring-blue-100">
                                        <svg
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            aria-hidden="true"
                                        >
                                            <path d="M6 2h9l3 3v17H6z"/>
                                            <path d="M14 2v4h4"/>
                                        </svg>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-[11px] font-medium text-blue-500">
                                            ไฟล์แนบ
                                        </p>

                                        <p class="mt-0.5 truncate text-sm font-semibold text-slate-700">
                                            {{ $knowledgeItem->attachment_original_name ?: 'ไฟล์แนบ' }}
                                        </p>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('knowledge-items.attachment', $knowledgeItem) }}"
                                    class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white px-3 text-sm font-semibold text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path d="M12 3v12"/>
                                        <path d="m7 10 5 5 5-5"/>
                                        <path d="M5 21h14"/>
                                    </svg>

                                    เปิดไฟล์
                                </a>
                            </div>
                        </div>
                    </section>
                @endif

                {{-- Actions --}}
                <div class="flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:flex-wrap sm:items-center">
                    @can('update', $knowledgeItem)
                        <a
                            href="{{ route('superadmin.km.edit', $knowledgeItem) }}"
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-xl bg-blue-600 px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                            </svg>

                            แก้ไข
                        </a>
                    @endcan

                    @can($knowledgeItem->status === 'published' ? 'unpublish' : 'publish', $knowledgeItem)
                        <form
                            method="POST"
                            action="{{ route(
                                $knowledgeItem->status === 'published'
                                    ? 'superadmin.km.unpublish'
                                    : 'superadmin.km.publish',
                                $knowledgeItem
                            ) }}"
                        >
                            @csrf

                            @if ($knowledgeItem->status === 'published')
                                @method('DELETE')
                            @endif

                            <button
                                type="submit"
                                class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-xl border px-3 text-sm font-semibold shadow-sm transition sm:w-auto
                                    {{ $knowledgeItem->status === 'published'
                                        ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                        : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
                            >
                                @if ($knowledgeItem->status === 'published')
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path d="M3 3l18 18"/>
                                        <path d="M10.6 10.6A2 2 0 0 0 12 14a2 2 0 0 0 1.4-.6"/>
                                        <path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c5 0 9 4 10 8a11.6 11.6 0 0 1-2.2 4.1"/>
                                        <path d="M6.6 6.6A11.6 11.6 0 0 0 2 12c1 4 5 8 10 8a10.8 10.8 0 0 0 3.1-.5"/>
                                    </svg>

                                    ถอนเผยแพร่
                                @else
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                    </svg>

                                    เผยแพร่
                                @endif
                            </button>
                        </form>
                    @endcan

                    @can('feature', $knowledgeItem)
                        <form
                            method="POST"
                            action="{{ route(
                                $knowledgeItem->is_featured
                                    ? 'superadmin.km.unfeature'
                                    : 'superadmin.km.feature',
                                $knowledgeItem
                            ) }}"
                        >
                            @csrf

                            @if ($knowledgeItem->is_featured)
                                @method('DELETE')
                            @endif

                            <button
                                type="submit"
                                class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-xl border border-amber-200 bg-white px-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 sm:w-auto"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="{{ $knowledgeItem->is_featured ? 'currentColor' : 'none' }}"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>
                                </svg>

                                {{ $knowledgeItem->is_featured ? 'ถอน Featured' : 'ตั้ง Featured' }}
                            </button>
                        </form>
                    @endcan

                    @can('delete', $knowledgeItem)
                        <form
                            method="POST"
                            action="{{ route('superadmin.km.destroy', $knowledgeItem) }}"
                            onsubmit="return confirm('ยืนยันการลบรายการนี้?')"
                            class="sm:ml-auto"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-3 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50 sm:w-auto"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4h8v2"/>
                                    <path d="M19 6l-1 14H6L5 6"/>
                                    <path d="M10 11v5M14 11v5"/>
                                </svg>

                                ลบ
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </article>
    </div>
@endsection
