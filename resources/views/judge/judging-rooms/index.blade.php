@extends('layouts.app')

@section('title', 'ห้องตัดสิน')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            ห้องตัดสิน
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            ห้องตัดสินที่คุณได้รับมอบหมาย
        </p>
    </div>
@endsection

@section('content')
    <div class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
        @forelse ($rooms as $room)
            @php
                $competition = $room->competition;
                $assignment = $competition->judgeAssignments->first();

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

                $templateTitle = $competition->template?->title
                    ?? $competition->template?->name
                    ?? 'ไม่ได้ระบุแบบฟอร์ม';
            @endphp

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
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

                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="line-clamp-2 font-semibold text-slate-800">
                                {{ $competition->title }}
                            </h2>

                            <p class="mt-1 line-clamp-1 text-sm text-slate-500">
                                แบบฟอร์ม: {{ $templateTitle }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                สถานะห้อง: {{ $room->status }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                            {{ $assignment?->assignment_status ?? 'ไม่ทราบสถานะ' }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-slate-400">ผลงาน</p>
                            <p class="mt-1 font-semibold text-slate-700">
                                {{ $competition->submissions_count }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-slate-400">เกณฑ์</p>
                            <p class="mt-1 font-semibold text-slate-700">
                                {{ $competition->rubrics_count }}
                            </p>
                        </div>
                    </div>

                    {{-- Assignment actions --}}
                    @if ($assignment?->assignment_status === 'pending')
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <form
                                action="{{ route('judge.assignments.accept', $assignment) }}"
                                method="POST"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                >
                                    รับงาน
                                </button>
                            </form>

                            <form
                                action="{{ route('judge.assignments.decline', $assignment) }}"
                                method="POST"
                                onsubmit="return confirm('ยืนยันปฏิเสธงานตัดสินนี้หรือไม่?')"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="w-full rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                >
                                    ปฏิเสธ
                                </button>
                            </form>
                        </div>
                    @elseif ($assignment?->assignment_status === 'accepted')
                        @if ($room->status === 'live')
                            <a
                                href="{{ route('judge.judging-rooms.show', $room) }}"
                                class="mt-5 flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                            >
                                เข้าห้องตัดสิน
                            </a>
                        @elseif ($room->status === 'ended')
                            <div class="mt-5 rounded-xl bg-slate-100 px-4 py-3 text-center text-sm text-slate-500">
                                ห้องตัดสินสิ้นสุดแล้ว
                            </div>
                        @else
                            <div class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-center text-sm text-amber-700">
                                ห้องตัดสินยังไม่เปิด
                            </div>
                        @endif
                    @elseif ($assignment?->assignment_status === 'declined')
                        <div class="mt-5 rounded-xl bg-slate-100 px-4 py-3 text-center text-sm text-slate-500">
                            คุณปฏิเสธงานตัดสินนี้แล้ว
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 bg-white py-16 text-center">
                <p class="font-medium text-slate-600">
                    ยังไม่มีห้องตัดสิน
                </p>

                <p class="mt-1 text-sm text-slate-400">
                    ห้องจะแสดงเมื่อผู้จัดเปิดห้องและมอบหมายคุณเป็นกรรมการ
                </p>
            </div>
        @endforelse
    </div>

    @if ($rooms->hasPages())
        <div class="mt-6">
            {{ $rooms->links() }}
        </div>
    @endif
@endsection
