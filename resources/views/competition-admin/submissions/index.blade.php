@extends('layouts.app')

@section('title', 'ผลงานที่ส่งเข้าประกวด')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m7 15 3.2-3.2a1.5 1.5 0 0 1 2.1 0L14 13.5l1.2-1.2a1.5 1.5 0 0 1 2.1 0L20 15"/>
                <circle cx="9" cy="9" r="1.3"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900">ผลงานที่ส่งเข้าประกวด</h1>
            <p class="mt-0.5 text-xs text-slate-500">ตรวจสอบผลงานที่ผู้เข้าร่วมส่งเข้าประกวดในระบบ</p>
        </div>
    </div>

    @php
        $submissionTotal = method_exists($submissions, 'total')
            ? $submissions->total()
            : $submissions->count();
    @endphp

    <div class="inline-flex w-fit items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-sm">
        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/>
            </svg>
        </div>
        <div>
            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">ผลงานทั้งหมด</p>
            <p class="text-sm font-bold text-slate-800">{{ number_format($submissionTotal) }} รายการ</p>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-4">

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Submissions</span>
                <span class="h-px w-8 bg-blue-200"></span>
            </div>
            <h2 class="mt-1 text-base font-bold text-slate-900">รายการผลงาน</h2>
            <p class="mt-0.5 text-xs text-slate-500">ข้อมูลผลงาน รหัสการแข่งขัน และเวลาที่ส่งเข้าระบบ</p>
        </div>

        <div class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
            <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9"/>
                <path stroke-linecap="round" d="M12 8v4l3 2"/>
            </svg>
            แสดง {{ number_format($submissions->count()) }} รายการในหน้านี้
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($submissions as $submission)
            @php
                $file = $submission->files->first(
                    fn ($file) => str_starts_with((string) $file->mime_type, 'image/')
                );
            @endphp

            <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-blue-200 hover:shadow-md">

                <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
                    @if($file)
                        <img
                            src="{{ $file->file_url }}"
                            alt="{{ $submission->project_title }}"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                            loading="lazy">
                    @else
                        <div class="flex h-full flex-col items-center justify-center bg-gradient-to-br from-slate-50 via-blue-50/40 to-white text-center">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m7 15 3.2-3.2a1.5 1.5 0 0 1 2.1 0L14 13.5l1.2-1.2a1.5 1.5 0 0 1 2.1 0L20 15"/>
                                    <circle cx="9" cy="9" r="1.3"/>
                                </svg>
                            </div>
                            <p class="mt-2 text-[11px] font-medium text-slate-400">ไม่มีรูปภาพประกอบ</p>
                        </div>
                    @endif

                    <div class="absolute left-3 top-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50/95 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 shadow-sm backdrop-blur">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            ส่งผลงานแล้ว
                        </span>
                    </div>

                    <div class="absolute right-3 top-3">
                        <span class="inline-flex max-w-[180px] items-center gap-1.5 rounded-full border border-white/70 bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-slate-700 shadow-sm backdrop-blur">
                            <svg class="h-3 w-3 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7V4h3M17 4h3v3M20 17v3h-3M7 20H4v-3"/>
                                <path d="M8 8h8v8H8z"/>
                            </svg>
                            <span class="truncate">{{ $submission->submission_code }}</span>
                        </span>
                    </div>
                </div>

                <div class="flex flex-1 flex-col p-4">
                    <div class="flex items-center justify-between gap-3">
                        <span class="inline-flex min-w-0 items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="3" width="7" height="7" rx="1"/>
                                <rect x="14" y="3" width="7" height="7" rx="1"/>
                                <rect x="3" y="14" width="7" height="7" rx="1"/>
                                <rect x="14" y="14" width="7" height="7" rx="1"/>
                            </svg>
                            <span class="truncate">{{ $submission->competition?->category?->category_name ?? 'ไม่ระบุหมวดหมู่' }}</span>
                        </span>

                        <div class="flex shrink-0 items-center gap-1 text-[11px] font-medium text-slate-400">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                            </svg>
                            {{ $submission->submitted_at?->format('d/m/Y') ?? '-' }}
                        </div>
                    </div>

                    <h3 class="mt-3 line-clamp-2 min-h-[48px] text-base font-bold leading-6 text-slate-900 transition group-hover:text-blue-700">
                        {{ $submission->project_title }}
                    </h3>

                    <div class="mt-3 space-y-2 rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                        <div class="flex items-start gap-2.5">
                            <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white text-violet-500 ring-1 ring-slate-200">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4ZM5 6H3v2a4 4 0 0 0 4 4M19 6h2v2a4 4 0 0 1-4 4"/>
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[10px] font-medium text-slate-400">การแข่งขัน</p>
                                <p class="mt-0.5 truncate text-xs font-semibold text-slate-700">{{ $submission->competition?->title ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 border-t border-slate-200/70 pt-2">
                            <div class="min-w-0">
                                <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 7V4h3M17 4h3v3M20 17v3h-3M7 20H4v-3"/>
                                        <path d="M8 8h8v8H8z"/>
                                    </svg>
                                    รหัสผลงาน
                                </p>
                                <p class="mt-1 truncate font-mono text-[11px] font-semibold text-slate-700">{{ $submission->submission_code }}</p>
                            </div>

                            <div class="min-w-0">
                                <p class="flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path stroke-linecap="round" d="M12 8v4l3 2"/>
                                    </svg>
                                    เวลาที่ส่ง
                                </p>
                                <p class="mt-1 text-[11px] font-semibold text-slate-700">{{ $submission->submitted_at?->format('H:i') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                        <div class="inline-flex items-center gap-1.5 text-[11px] font-medium text-slate-500">
                            <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                            </svg>
                            บันทึกในระบบแล้ว
                        </div>

                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600">
                            Submission
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-14 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m7 15 3.2-3.2a1.5 1.5 0 0 1 2.1 0L14 13.5l1.2-1.2a1.5 1.5 0 0 1 2.1 0L20 15"/>
                        <circle cx="9" cy="9" r="1.3"/>
                    </svg>
                </div>
                <h3 class="mt-3 text-sm font-bold text-slate-800">ยังไม่มีผลงานที่ส่งเข้าประกวด</h3>
                <p class="mx-auto mt-1 max-w-md text-xs leading-5 text-slate-500">
                    เมื่อผู้เข้าร่วมส่งผลงาน รายการจะปรากฏในหน้านี้เพื่อให้ตรวจสอบข้อมูลได้ทันที
                </p>
            </div>
        @endforelse
    </div>

    @if(method_exists($submissions, 'links') && $submissions->hasPages())
        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">
                แสดง
                <span class="font-semibold text-slate-700">{{ number_format($submissions->count()) }}</span>
                จาก
                <span class="font-semibold text-slate-700">{{ number_format($submissionTotal) }}</span>
                รายการ
            </p>

            <div>{{ $submissions->links() }}</div>
        </div>
    @endif
</div>
@endsection
