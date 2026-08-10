@extends('layouts.app')

@section('title', 'ห้องตัดสิน')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            ห้องตัดสิน
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            เลือกการแข่งขันเพื่อเปิดและควบคุมการตัดสินแบบ Live
        </p>
    </div>
@endsection

@section('content')
    <div class="space-y-5">

        {{-- Summary --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">
                        การแข่งขันที่คุณดูแล
                    </p>

                    <p class="mt-1 text-2xl font-bold text-slate-800">
                        {{ $competitions->total() }} รายการ
                    </p>
                </div>

                <div class="rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700">
                    เลือกการแข่งขันเพื่อเข้าสู่หน้าควบคุม
                </div>
            </div>
        </section>

        {{-- Competition rooms --}}
        <div class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
            @forelse ($competitions as $competition)
                @php
                    $session = $competition->judgingSession;

                    $coverImage = $competition->cover_image
                        ?: $competition->template?->cover_image;

                    $coverUrl = $coverImage
                        ? (\Illuminate\Support\Str::startsWith(
                            $coverImage,
                            ['http://', 'https://']
                        )
                            ? $coverImage
                            : \Illuminate\Support\Facades\Storage::disk('public')
                                ->url($coverImage))
                        : null;

                    $templateTitle = $competition->template?->template_name
                        ?? 'ไม่ได้ระบุแบบฟอร์ม';

                    $status = $session?->status ?? 'not_created';

                    $statusConfig = match ($status) {
                        'waiting' => [
                            'label' => 'รอเริ่ม',
                            'class' => 'bg-amber-50 text-amber-700 ring-amber-200',
                            'dot' => 'bg-amber-500',
                        ],
                        'live' => [
                            'label' => 'กำลัง Live',
                            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                            'dot' => 'bg-emerald-500 animate-pulse',
                        ],
                        'paused' => [
                            'label' => 'หยุดชั่วคราว',
                            'class' => 'bg-orange-50 text-orange-700 ring-orange-200',
                            'dot' => 'bg-orange-500',
                        ],
                        'ended' => [
                            'label' => 'จบการตัดสิน',
                            'class' => 'bg-violet-50 text-violet-700 ring-violet-200',
                            'dot' => 'bg-violet-500',
                        ],
                        'closed' => [
                            'label' => 'ปิดห้องแล้ว',
                            'class' => 'bg-slate-100 text-slate-600 ring-slate-200',
                            'dot' => 'bg-slate-400',
                        ],
                        default => [
                            'label' => 'ยังไม่เปิดห้อง',
                            'class' => 'bg-slate-50 text-slate-600 ring-slate-200',
                            'dot' => 'bg-slate-400',
                        ],
                    };
                @endphp

                <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    {{-- Competition header image --}}
                    <div class="h-44 overflow-hidden bg-slate-100">
                        @if ($coverUrl)
                            <img
                                src="{{ $coverUrl }}"
                                alt="รูปภาพรายการ {{ $competition->title }}"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                        @else
                            <div class="flex h-full flex-col items-center justify-center gap-2 text-slate-400">
                                <svg
                                    class="h-9 w-9"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    aria-hidden="true"
                                >
                                    <rect x="3" y="4" width="18" height="16" rx="2" />
                                    <circle cx="8.5" cy="9" r="1.5" />
                                    <path d="m4 17 5-5 4 4 2-2 5 4" />
                                </svg>

                                <span class="text-sm">ไม่มีรูปภาพรายการ</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                    การแข่งขัน
                                </p>

                                <h2 class="mt-1 line-clamp-2 text-lg font-bold text-slate-800">
                                    {{ $competition->title }}
                                </h2>

                                <p class="mt-1 line-clamp-1 text-sm text-slate-500">
                                    แบบฟอร์ม: {{ $templateTitle }}
                                </p>
                            </div>

                        <span
                            class="inline-flex shrink-0 items-center gap-2 rounded-full
                                   px-3 py-1 text-xs font-semibold ring-1
                                   {{ $statusConfig['class'] }}"
                        >
                            <span class="h-2 w-2 rounded-full {{ $statusConfig['dot'] }}"></span>

                            {{ $statusConfig['label'] }}
                        </span>
                    </div>

                    {{-- Counts --}}
                    <div class="mt-5 grid grid-cols-3 gap-2">
                        <div class="rounded-xl bg-slate-50 p-3 text-center">
                            <p class="text-xl font-bold text-slate-800">
                                {{ $competition->submissions_count }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                ผลงาน
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3 text-center">
                            <p class="text-xl font-bold text-slate-800">
                                {{ $competition->rubrics_count }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                เกณฑ์
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3 text-center">
                            <p class="text-xl font-bold text-slate-800">
                                {{ $competition->judge_assignments_count }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                กรรมการ
                            </p>
                        </div>
                    </div>

                    {{-- Readiness --}}
                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">
                                ผลงานพร้อมตัดสิน
                            </span>

                            <span class="{{ $competition->submissions_count > 0
                                ? 'text-emerald-600'
                                : 'text-red-600' }}">
                                {{ $competition->submissions_count > 0
                                    ? 'พร้อม'
                                    : 'ยังไม่มี' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">
                                เกณฑ์การให้คะแนน
                            </span>

                            <span class="{{ $competition->rubrics_count > 0
                                ? 'text-emerald-600'
                                : 'text-red-600' }}"
                            >
                                {{ $competition->rubrics_count > 0
                                    ? 'พร้อม'
                                    : 'ยังไม่มี' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">
                                กรรมการ
                            </span>

                            <span class="{{ $competition->judge_assignments_count > 0
                                ? 'text-emerald-600'
                                : 'text-red-600' }}"
                            >
                                {{ $competition->judge_assignments_count > 0
                                    ? 'พร้อม'
                                    : 'ยังไม่มี' }}
                            </span>
                        </div>
                    </div>

                        <div class="mt-auto pt-5">
                        <a
                            href="{{ route(
                                'competition-admin.competitions.judging-room.show',
                                $competition
                            ) }}"
                            class="flex w-full items-center justify-center gap-2 rounded-xl
                                   bg-blue-600 px-4 py-3 text-sm font-semibold text-white
                                   shadow-sm transition hover:bg-blue-700
                                   focus:outline-none focus:ring-4 focus:ring-blue-100">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 10l4.5-2.5A1 1 0 0 1 21 8.4v7.2a1 1 0 0 1-1.5.9L15 14"
                                />
                                <rect
                                    x="3"
                                    y="6"
                                    width="12"
                                    height="12"
                                    rx="2"
                                />
                            </svg>

                            {{ $session
                                ? 'เปิดหน้าควบคุม'
                                : 'เตรียมห้องตัดสิน' }}
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 bg-white py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 5h16v14H4zM8 9h8M8 13h5"
                            />
                        </svg>
                    </div>

                    <h2 class="mt-4 font-semibold text-slate-700">
                        ยังไม่มีการแข่งขัน
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        สร้างการแข่งขันก่อนเปิดห้องตัดสิน
                    </p>
                </div>
            @endforelse
        </div>

        @if ($competitions->hasPages())
            <div>
                {{ $competitions->links() }}
            </div>
        @endif
    </div>
@endsection
