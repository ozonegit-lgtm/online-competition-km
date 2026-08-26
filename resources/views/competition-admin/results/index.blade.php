@extends('layouts.app')

@section('title', 'ศูนย์ผลการแข่งขัน')

@section('header')
    <div>
        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">
            ศูนย์ผลการแข่งขัน
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            เลือกการแข่งขันเพื่อตรวจสอบคะแนน อันดับ และการประกาศผล
        </p>
    </div>
@endsection

@section('content')
    <div class="mx-auto w-full max-w-7xl space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    การแข่งขันของฉัน
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    แสดงเฉพาะการแข่งขันที่คุณเป็นผู้สร้าง
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                {{ number_format($competitions->total()) }} การแข่งขัน
            </span>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($competitions as $competition)
                @php
                    $sessionStatus =
                        $competition->judgingSession?->status
                        ?? 'waiting';

                    $totalSubmissions = (int)
                        $competition->results_total_submissions;

                    $completedSubmissions = (int)
                        $competition->results_completed_submissions;

                    $completionPercent = $totalSubmissions > 0
                        ? min(
                            100,
                            (int) round(
                                ($completedSubmissions / $totalSubmissions)
                                * 100
                            )
                        )
                        : 0;

                    $sessionConfig = match ($sessionStatus) {
                        'live' => [
                            'label' => 'กำลังตัดสิน',
                            'class' => 'bg-red-50 text-red-700 ring-red-200',
                            'dot' => 'bg-red-500',
                        ],
                        'paused' => [
                            'label' => 'พักการตัดสิน',
                            'class' => 'bg-amber-50 text-amber-700 ring-amber-200',
                            'dot' => 'bg-amber-500',
                        ],
                        'ended' => [
                            'label' => 'จบการตัดสินแล้ว',
                            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                            'dot' => 'bg-emerald-500',
                        ],
                        'closed' => [
                            'label' => 'ปิดห้องแล้ว',
                            'class' => 'bg-slate-100 text-slate-700 ring-slate-200',
                            'dot' => 'bg-slate-500',
                        ],
                        default => [
                            'label' => 'รอเริ่มตัดสิน',
                            'class' => 'bg-blue-50 text-blue-700 ring-blue-200',
                            'dot' => 'bg-blue-500',
                        ],
                    };
                @endphp

                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md">
                    <div class="border-b border-slate-100 px-5 py-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $sessionConfig['class'] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $sessionConfig['dot'] }}"></span>
                                {{ $sessionConfig['label'] }}
                            </span>

                            @if ($competition->publish_scores)
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    ประกาศผลแล้ว
                                </span>
                            @elseif ($competition->results_ready)
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 ring-1 ring-blue-200">
                                    พร้อมประกาศ
                                </span>
                            @endif
                        </div>

                        <h3 class="mt-4 line-clamp-2 text-lg font-bold leading-6 text-slate-900">
                            {{ $competition->title }}
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $competition->category?->category_name ?? 'ไม่ระบุหมวดหมู่' }}
                        </p>
                    </div>

                    <div class="space-y-4 px-5 py-4">
                        <div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-500">
                                    ผลงานที่คะแนนครบ
                                </span>

                                <span class="font-bold text-slate-800">
                                    {{ $completedSubmissions }} / {{ $totalSubmissions }}
                                </span>
                            </div>

                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full transition-all {{ $competition->results_ready ? 'bg-emerald-500' : 'bg-blue-600' }}"
                                    style="width: {{ $completionPercent }}%"
                                ></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3">
                            <div>
                                <p class="text-[11px] font-medium text-slate-500">
                                    Rubric active
                                </p>

                                <p class="mt-1 text-lg font-black text-slate-900">
                                    {{ $competition->results_active_rubric_count }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[11px] font-medium text-slate-500">
                                    กรรมการ accepted
                                </p>

                                <p class="mt-1 text-lg font-black text-slate-900">
                                    {{ $competition->results_accepted_judge_count }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50/60 p-4">
                        <a
                            href="{{ route('competition-admin.competitions.results.index', $competition) }}"
                            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                            ตรวจสอบอันดับ

                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center md:col-span-2 xl:col-span-3">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 10.5h7.5m-7.5 3.75h4.5M6 21h12a2.25 2.25 0 0 0 2.25-2.25V5.25A2.25 2.25 0 0 0 18 3H6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21Z" />
                        </svg>
                    </div>

                    <h3 class="mt-4 font-bold text-slate-800">
                        ยังไม่มีการแข่งขัน
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        สร้างการแข่งขันและดำเนินการตัดสินก่อนตรวจสอบผล
                    </p>
                </div>
            @endforelse
        </div>

        @if ($competitions->hasPages())
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                {{ $competitions->links() }}
            </div>
        @endif
    </div>
@endsection

