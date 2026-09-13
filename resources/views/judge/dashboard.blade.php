@extends('layouts.app')

@section('title', 'แดชบอร์ดกรรมการ')

@section('header')
    <div>
        <h1 class="text-slate-800 text-xl font-bold">
            แดชบอร์ดกรรมการ
        </h1>

        <p class="mt-1 text-slate-500 text-xs">
            ตรวจสอบผลงานและให้คะแนนการแข่งขันที่ได้รับมอบหมาย
        </p>
    </div>
@endsection

@section('content')
    <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">

        <p class="text-slate-700">
            ยินดีต้อนรับ
            <span class="font-semibold">
                {{ auth()->user()->username }}
            </span>
        </p>

        <p class="mt-2 text-slate-500 text-xs">
            สิทธิ์การใช้งาน:
            {{ auth()->user()->role->display_name }}
        </p>


    </div>

    <section class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">งานตัดสินของฉัน</h2>
            <p class="mt-1 text-xs text-slate-500">แสดงเฉพาะการแข่งขันที่มอบหมายให้บัญชีนี้</p>
        </div>
        <div class="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($assignments as $assignment)
                @php
                    $session = $assignment->competition?->judgingSession;
                    $sessionStatus = $session?->status;
                    $statusLabel = match ($assignment->assignment_status) {
                        'pending' => 'รอตอบรับ',
                        'accepted' => 'รับงานแล้ว',
                        'declined' => 'ปฏิเสธแล้ว',
                        default => $assignment->assignment_status,
                    };
                @endphp
                <article data-assignment-id="{{ $assignment->id }}" class="rounded-xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-semibold text-slate-800">{{ $assignment->competition?->title ?? 'ไม่พบการแข่งขัน' }}</h3>
                        <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $statusLabel }}</span>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">มอบหมาย {{ $assignment->assigned_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    @if ($sessionStatus)
                        <p class="mt-1 text-xs font-medium text-blue-600">สถานะห้อง: {{ $sessionStatus }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($assignment->assignment_status === 'pending')
                            <form method="POST" action="{{ route('judge.assignments.accept', $assignment) }}">
                                @csrf
                                <button type="submit" class="h-9 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-700">รับงาน</button>
                            </form>
                            <form method="POST" action="{{ route('judge.assignments.decline', $assignment) }}">
                                @csrf
                                <button type="submit" class="h-9 rounded-lg border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50">ปฏิเสธ</button>
                            </form>
                        @elseif ($assignment->assignment_status === 'accepted' && $session && in_array($sessionStatus, ['live', 'paused'], true))
                            <a href="{{ route('judge.judging-rooms.show', $session) }}" class="inline-flex h-9 items-center rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white hover:bg-blue-700">เข้าห้องตัดสิน</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="py-10 text-center text-sm text-slate-500 md:col-span-2 xl:col-span-3">ยังไม่มีงานตัดสินที่ได้รับมอบหมาย</div>
            @endforelse
        </div>
        @if ($assignments->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $assignments->links() }}</div>
        @endif
    </section>
@endsection
