@extends('layouts.app')

@section('title', 'ผลงานสำหรับ Knowledge Management')

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                ผลงานสำหรับ Knowledge Management
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                เลือกผลงานจากการแข่งขันที่ตัดสินเสร็จแล้วเพื่อเผยแพร่สู่ KM
            </p>
        </div>

        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
            {{ number_format($submissions->total()) }} ผลงาน
        </div>
    </div>
@endsection

@section('content')
    <div class="mx-auto w-full max-w-7xl space-y-6">

        {{-- Filter --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                        <svg
                            class="h-4.5 w-4.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            ค้นหาและกรองผลงาน
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            ค้นหาจากชื่อผลงาน รหัสผลงาน หรือเลือกการแข่งขันและสถานะ KM
                        </p>
                    </div>
                </div>
            </div>

            <form
                action="{{ route('competition-admin.km.submissions.index') }}"
                method="GET"
                class="grid gap-4 p-5 sm:p-6 lg:grid-cols-12"
            >
                {{-- Search --}}
                <div class="lg:col-span-5">
                    <label
                        for="search"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        ค้นหาผลงาน
                    </label>

                    <div class="relative mt-2">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg
                                class="h-4.5 w-4.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <circle cx="11" cy="11" r="7"></circle>
                                <path stroke-linecap="round" d="m20 20-3.5-3.5"></path>
                            </svg>
                        </div>

                        <input
                            id="search"
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="ชื่อผลงาน รหัสผลงาน หรือชื่อการแข่งขัน"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                    </div>
                </div>

                {{-- Competition --}}
                <div class="lg:col-span-3">
                    <label
                        for="competition_id"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        การแข่งขัน
                    </label>

                    <select
                        id="competition_id"
                        name="competition_id"
                        class="mt-2 h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 outline-none transition hover:border-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">
                            ทุกการแข่งขัน
                        </option>

                        @foreach ($competitions as $competition)
                            <option
                                value="{{ $competition->id }}"
                                @selected(
                                    (string) request('competition_id')
                                    === (string) $competition->id
                                )
                            >
                                {{ $competition->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- KM Status --}}
                <div class="lg:col-span-2">
                    <label
                        for="km_status"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        สถานะ KM
                    </label>

                    <select
                        id="km_status"
                        name="km_status"
                        class="mt-2 h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 outline-none transition hover:border-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                        <option value="">
                            ทั้งหมด
                        </option>

                        <option
                            value="published"
                            @selected(request('km_status') === 'published')
                        >
                            เผยแพร่แล้ว
                        </option>

                        <option
                            value="unpublished"
                            @selected(request('km_status') === 'unpublished')
                        >
                            ยังไม่เผยแพร่
                        </option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-end gap-2 lg:col-span-2">
                    <button
                        type="submit"
                        class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <circle cx="11" cy="11" r="7"></circle>
                            <path stroke-linecap="round" d="m20 20-3.5-3.5"></path>
                        </svg>

                        ค้นหา
                    </button>

                    @if (
                        request()->filled('search')
                        || request()->filled('competition_id')
                        || request()->filled('km_status')
                    )
                        <a
                            href="{{ route('competition-admin.km.submissions.index') }}"
                            class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                            title="ล้างตัวกรอง"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Result Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl">
                        ผลงานที่ผ่านการแข่งขัน
                    </h2>

                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                        {{ number_format($submissions->total()) }} รายการ
                    </span>
                </div>

                <p class="mt-1 text-sm text-slate-500">
                    จัดการสถานะการเผยแพร่ผลงานเข้าสู่ Knowledge Management
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                <svg
                    class="h-3.5 w-3.5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                </svg>

                เฉพาะการแข่งขันที่ตัดสินเสร็จแล้ว
            </div>
        </div>

        {{-- Submission Cards --}}
        <div class="grid gap-5 xl:grid-cols-2">
            @forelse ($submissions as $submission)
                @php
                    $knowledgeItem = $submission->knowledgeItem;

                    $isPublished =
                        $knowledgeItem
                        && $knowledgeItem->status === 'published';

                    $primaryFile =
                        $submission->files->firstWhere('is_primary', true)
                        ?? $submission->files->first();

                    $hasImage =
                        $primaryFile
                        && filled($primaryFile->mime_type)
                        && str_starts_with(
                            (string) $primaryFile->mime_type,
                            'image/'
                        );
                @endphp

                <article
                    data-km-card
                    class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md"
                >
                    <div class="flex h-full flex-col sm:flex-row">

                        {{-- Preview --}}
                        <div class="relative h-52 shrink-0 overflow-hidden bg-slate-100 sm:h-auto sm:w-52">
                            @if ($hasImage)
                                <img
                                    src="{{ asset('storage/' . $primaryFile->file_path) }}"
                                    alt="{{ $submission->project_title }}"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                                >
                            @else
                                <div class="flex h-full min-h-52 items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 p-6 text-center">
                                    <div>
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm">
                                            <svg
                                                class="h-7 w-7"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                aria-hidden="true"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5v-16Z" />
                                            </svg>
                                        </div>

                                        <p class="mt-3 text-xs font-medium text-slate-400">
                                            ไม่มีรูปตัวอย่าง
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="absolute left-3 top-3">
                                <span
                                    data-km-overlay
                                    class="{{ $isPublished
                                        ? 'inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm'
                                        : 'inline-flex items-center gap-1.5 rounded-full bg-slate-900/85 px-2.5 py-1 text-xs font-semibold text-white shadow-sm backdrop-blur-sm' }}"
                                >
                                    <span
                                        data-km-overlay-dot
                                        class="{{ $isPublished
                                            ? 'h-1.5 w-1.5 rounded-full bg-white'
                                            : 'h-1.5 w-1.5 rounded-full bg-slate-300' }}"
                                    ></span>

                                    <span data-km-overlay-text>
                                        {{ $isPublished ? 'เผยแพร่ใน KM' : 'ยังไม่เผยแพร่' }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex min-w-0 flex-1 flex-col p-5 sm:p-6">

                            <div class="flex min-w-0 items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold uppercase tracking-wide text-blue-600">
                                        {{ $submission->competition->title }}
                                    </p>

                                    <h3 class="mt-2 line-clamp-2 text-lg font-bold leading-snug text-slate-900">
                                        {{ $submission->project_title }}
                                    </h3>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg
                                                class="h-3.5 w-3.5 text-slate-400"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                aria-hidden="true"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10v10H7z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h3M17 4h3v3M20 17v3h-3M7 20H4v-3" />
                                            </svg>

                                            {{ $submission->submission_code }}
                                        </span>
                                    </div>
                                </div>

                                <div class="shrink-0 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-right">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-blue-500">
                                        คะแนน
                                    </p>

                                    <p class="mt-0.5 text-xl font-bold leading-none text-blue-700">
                                        {{ number_format((float) $submission->final_score, 2) }}
                                    </p>
                                </div>
                            </div>

                            @if ($submission->project_description)
                                <p class="mt-4 line-clamp-2 text-sm leading-6 text-slate-500">
                                    {{ $submission->project_description }}
                                </p>
                            @endif

                            <div class="mt-5 flex flex-wrap items-center gap-2">
                                <span
                                    data-km-state-pill
                                    class="{{ $isPublished
                                        ? 'inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100'
                                        : 'inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600' }}"
                                >
                                    <span
                                        data-km-state-dot
                                        class="{{ $isPublished
                                            ? 'h-1.5 w-1.5 rounded-full bg-emerald-500'
                                            : 'h-1.5 w-1.5 rounded-full bg-slate-400' }}"
                                    ></span>

                                    <span data-km-state-text>
                                        @if ($isPublished)
                                            เผยแพร่แล้ว
                                        @elseif ($knowledgeItem?->status === 'hidden')
                                            ซ่อน
                                        @elseif ($knowledgeItem)
                                            ฉบับร่าง
                                        @else
                                            ยังไม่มีรายการ KM
                                        @endif
                                    </span>
                                </span>

                                <span
                                    data-km-published-time
                                    class="text-xs text-slate-400 {{ $isPublished && $knowledgeItem?->published_at ? '' : 'hidden' }}"
                                >
                                    @if ($isPublished && $knowledgeItem?->published_at)
                                        เผยแพร่ {{ $knowledgeItem->published_at->format('d/m/Y H:i') }}
                                    @endif
                                </span>
                            </div>

                            {{-- Action --}}
                            <div class="mt-auto pt-5">
                                <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                    <p
                                        data-km-help
                                        class="max-w-sm text-xs leading-5 text-slate-400"
                                    >
                                        {{ $isPublished
                                            ? 'ผลงานนี้กำลังแสดงอยู่ใน Knowledge Management'
                                            : 'ตรวจสอบข้อมูลก่อนเผยแพร่สู่ Knowledge Management' }}
                                    </p>

                                    <form
                                        data-km-toggle-form
                                        data-mode="{{ $isPublished ? 'unpublish' : 'publish' }}"
                                        data-publish-url="{{ route('competition-admin.submissions.km.publish', $submission) }}"
                                        data-unpublish-url="{{ route('competition-admin.submissions.km.unpublish', $submission) }}"
                                        action="{{ $isPublished
                                            ? route('competition-admin.submissions.km.unpublish', $submission)
                                            : route('competition-admin.submissions.km.publish', $submission) }}"
                                        method="POST"
                                        class="shrink-0"
                                    >
                                        @csrf

                                        @if ($isPublished)
                                            @method('DELETE')
                                        @endif

                                        <button
                                            data-km-toggle-button
                                            type="submit"
                                            class="{{ $isPublished
                                                ? 'inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100'
                                                : 'inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100' }}"
                                        >
                                            @if ($isPublished)
                                                <svg
                                                    class="h-3.5 w-3.5"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    aria-hidden="true"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>

                                                <span data-km-button-text>
                                                    ถอนออกจาก KM
                                                </span>
                                            @else
                                                <svg
                                                    class="h-3.5 w-3.5"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    aria-hidden="true"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" />
                                                </svg>

                                                <span data-km-button-text>
                                                    เผยแพร่สู่ KM
                                                </span>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm xl:col-span-2">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg
                            class="h-8 w-8"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5v-16Z" />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-base font-bold text-slate-800">
                        ไม่พบผลงาน
                    </h3>

                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">
                        ยังไม่มีผลงานที่ตรงกับเงื่อนไขที่เลือก ลองเปลี่ยนคำค้นหา ตัวกรอง หรือรอให้การแข่งขันสิ้นสุดการตัดสินก่อน
                    </p>

                    @if (
                        request()->filled('search')
                        || request()->filled('competition_id')
                        || request()->filled('km_status')
                    )
                        <a
                            href="{{ route('competition-admin.km.submissions.index') }}"
                            class="mt-5 inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            ล้างตัวกรองทั้งหมด
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($submissions->hasPages())
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                {{ $submissions->links() }}
            </div>
        @endif

    </div>
        {{-- AJAX Feedback --}}
        <div
            id="km-action-toast"
            class="pointer-events-none fixed bottom-6 right-6 z-[100] hidden max-w-sm rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-xl"
            role="status"
            aria-live="polite"
        >
            <div class="flex items-center gap-3">
                <div
                    id="km-action-toast-icon"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.2"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </div>

                <p
                    id="km-action-toast-text"
                    class="text-sm font-semibold text-slate-800"
                ></p>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toast = document.getElementById('km-action-toast');
                const toastText = document.getElementById('km-action-toast-text');
                const toastIcon = document.getElementById('km-action-toast-icon');

                let toastTimer = null;

                const publishButtonClass =
                    'inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100';

                const unpublishButtonClass =
                    'inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100';

                const publishIcon = `
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 16V4m0 0L8 8m4-4 4 4" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" />
                    </svg>
                    <span data-km-button-text>เผยแพร่สู่ KM</span>
                `;

                const unpublishIcon = `
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    <span data-km-button-text>ถอนออกจาก KM</span>
                `;

                function showToast(message, type = 'success') {
                    if (!toast || !toastText || !toastIcon) {
                        return;
                    }

                    clearTimeout(toastTimer);

                    toastText.textContent = message;
                    toast.classList.remove('hidden');

                    if (type === 'error') {
                        toastIcon.className =
                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700';

                        toastIcon.innerHTML = `
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        `;
                    } else {
                        toastIcon.className =
                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700';

                        toastIcon.innerHTML = `
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m5 12 4 4L19 6" />
                            </svg>
                        `;
                    }

                    toastTimer = setTimeout(() => {
                        toast.classList.add('hidden');
                    }, 2600);
                }

                function setCardState(card, form, isPublished) {
                    const overlay = card.querySelector('[data-km-overlay]');
                    const overlayDot = card.querySelector('[data-km-overlay-dot]');
                    const overlayText = card.querySelector('[data-km-overlay-text]');
                    const statePill = card.querySelector('[data-km-state-pill]');
                    const stateDot = card.querySelector('[data-km-state-dot]');
                    const stateText = card.querySelector('[data-km-state-text]');
                    const publishedTime = card.querySelector('[data-km-published-time]');
                    const help = card.querySelector('[data-km-help]');
                    const button = form.querySelector('[data-km-toggle-button]');

                    let methodInput = form.querySelector('input[name="_method"]');

                    if (isPublished) {
                        overlay.className =
                            'inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm';
                        overlayDot.className =
                            'h-1.5 w-1.5 rounded-full bg-white';
                        overlayText.textContent = 'เผยแพร่ใน KM';

                        statePill.className =
                            'inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100';
                        stateDot.className =
                            'h-1.5 w-1.5 rounded-full bg-emerald-500';
                        stateText.textContent = 'เผยแพร่แล้ว';

                        publishedTime.textContent = 'เผยแพร่เมื่อสักครู่';
                        publishedTime.classList.remove('hidden');

                        help.textContent =
                            'ผลงานนี้กำลังแสดงอยู่ใน Knowledge Management';

                        form.dataset.mode = 'unpublish';
                        form.action = form.dataset.unpublishUrl;

                        if (!methodInput) {
                            methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            form.appendChild(methodInput);
                        }

                        methodInput.value = 'DELETE';

                        button.className = unpublishButtonClass;
                        button.innerHTML = unpublishIcon;
                    } else {
                        overlay.className =
                            'inline-flex items-center gap-1.5 rounded-full bg-slate-900/85 px-2.5 py-1 text-xs font-semibold text-white shadow-sm backdrop-blur-sm';
                        overlayDot.className =
                            'h-1.5 w-1.5 rounded-full bg-slate-300';
                        overlayText.textContent = 'ยังไม่เผยแพร่';

                        statePill.className =
                            'inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600';
                        stateDot.className =
                            'h-1.5 w-1.5 rounded-full bg-slate-400';
                        stateText.textContent = 'ฉบับร่าง';

                        publishedTime.textContent = '';
                        publishedTime.classList.add('hidden');

                        help.textContent =
                            'ตรวจสอบข้อมูลก่อนเผยแพร่สู่ Knowledge Management';

                        form.dataset.mode = 'publish';
                        form.action = form.dataset.publishUrl;

                        if (methodInput) {
                            methodInput.remove();
                        }

                        button.className = publishButtonClass;
                        button.innerHTML = publishIcon;
                    }
                }

                document.querySelectorAll('[data-km-toggle-form]').forEach((form) => {
                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        const mode = form.dataset.mode;
                        const card = form.closest('[data-km-card]');
                        const button = form.querySelector('[data-km-toggle-button]');

                        if (!card || !button) {
                            return;
                        }

                        if (
                            mode === 'unpublish'
                            && !window.confirm('ยืนยันการถอนผลงานนี้ออกจาก KM หรือไม่?')
                        ) {
                            return;
                        }

                        const previousHtml = button.innerHTML;
                        button.disabled = true;
                        button.classList.add('cursor-wait', 'opacity-70');
                        button.innerHTML = `
                            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9" class="opacity-25"></circle>
                                <path stroke-linecap="round" d="M21 12a9 9 0 0 0-9-9"></path>
                            </svg>
                            <span>กำลังบันทึก...</span>
                        `;

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html,application/xhtml+xml'
                                },
                                credentials: 'same-origin'
                            });

                            if (!response.ok) {
                                throw new Error('ไม่สามารถบันทึกข้อมูลได้');
                            }

                            const willPublish = mode === 'publish';

                            setCardState(
                                card,
                                form,
                                willPublish
                            );

                            showToast(
                                willPublish
                                    ? 'เผยแพร่ผลงานสู่ KM เรียบร้อยแล้ว'
                                    : 'ถอนผลงานออกจาก KM เรียบร้อยแล้ว'
                            );
                        } catch (error) {
                            button.innerHTML = previousHtml;

                            showToast(
                                error?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่',
                                'error'
                            );
                        } finally {
                            button.disabled = false;
                            button.classList.remove('cursor-wait', 'opacity-70');
                        }
                    });
                });
            });
        </script>

@endsection
