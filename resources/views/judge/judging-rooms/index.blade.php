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
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($rooms as $room)
            @php
                $assignment = $room->competition
                    ->judgeAssignments
                    ->first();
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-slate-800">
                            {{ $room->competition->title }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            สถานะห้อง: {{ $room->status }}
                        </p>
                    </div>

                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                        {{ $assignment?->assignment_status ?? 'ไม่ทราบสถานะ' }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-slate-400">ผลงาน</p>
                        <p class="mt-1 font-semibold text-slate-700">
                            {{ $room->competition->submissions_count }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-slate-400">เกณฑ์</p>
                        <p class="mt-1 font-semibold text-slate-700">
                            {{ $room->competition->rubrics_count }}
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('judge.judging-rooms.show', $room) }}"
                    class="mt-5 flex w-full items-center justify-center rounded-xl
                           bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white
                           transition hover:bg-blue-700"
                >
                    เข้าห้องตัดสิน
                </a>
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

    <div class="mt-6">
        {{ $rooms->links() }}
    </div>
@endsection