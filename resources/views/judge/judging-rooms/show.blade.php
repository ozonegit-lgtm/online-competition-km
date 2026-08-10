@extends('layouts.app')

@section('title', 'ห้องตัดสิน')

@section('header')
    @php
        $statusConfig = match ($session->status) {
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
                'label' => $session->status,
                'class' => 'bg-slate-100 text-slate-600 ring-slate-200',
                'dot' => 'bg-slate-400',
            ],
        };
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                {{ $competition->title }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                ห้องตัดสินสำหรับกรรมการ
            </p>
        </div>

        <span
            id="room-status"
            class="inline-flex items-center gap-2 rounded-full px-4 py-2
                   text-sm font-semibold ring-1 {{ $statusConfig['class'] }}"
        >
            <span class="h-2.5 w-2.5 rounded-full {{ $statusConfig['dot'] }}"></span>
            {{ $statusConfig['label'] }}
        </span>
    </div>
@endsection

@section('content')
    @php
        $isLive = $session->isLive();

        $isSubmitted =
            $rubrics->isNotEmpty() &&
            $scores->count() === $rubrics->count() &&
            $scores->every(
                fn ($score) => $score->submitted_at !== null
            );
    @endphp

    <div class="space-y-6">

        {{-- Waiting/Pause notice --}}
        @if ($session->isWaiting())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-700">
                <p class="font-semibold">
                    กำลังรอผู้จัดเริ่มการตัดสิน
                </p>

                <p class="mt-1 text-sm">
                    หน้านี้จะอัปเดตอัตโนมัติเมื่อห้องเริ่ม Live
                </p>
            </div>
        @elseif ($session->isPaused())
            <div class="rounded-2xl border border-orange-200 bg-orange-50 p-5 text-orange-700">
                <p class="font-semibold">
                    การตัดสินถูกหยุดชั่วคราว
                </p>

                <p class="mt-1 text-sm">
                    กรุณารอผู้จัดดำเนินการ Live ต่อ
                </p>
            </div>
        @elseif ($session->isEnded() || $session->isClosed())
            <div class="rounded-2xl border border-slate-200 bg-slate-100 p-5 text-slate-600">
                ห้องนี้จบการตัดสินแล้ว
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-5">

            {{-- Current submission --}}
            <section class="space-y-5 xl:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                        ผลงานปัจจุบัน
                    </p>

                    @if ($submission)
                        <h2 class="mt-2 text-2xl font-bold text-slate-800">
                            {{ $submission->project_title }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            รหัสผลงาน {{ $submission->submission_code }}
                        </p>

                        <div class="mt-5 rounded-xl bg-slate-50 p-4">
                            <p class="whitespace-pre-line text-sm leading-7 text-slate-600">
                                {{ $submission->project_description
                                    ?: 'ไม่มีรายละเอียดผลงาน' }}
                            </p>
                        </div>

                        @if ($submission->team_name)
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm text-slate-500">
                                    ทีม
                                </span>

                                <span class="text-sm font-semibold text-slate-700">
                                    {{ $submission->team_name }}
                                </span>
                            </div>
                        @endif
                    @else
                        <div class="mt-5 rounded-xl border-2 border-dashed border-slate-200 py-16 text-center">
                            <p class="font-medium text-slate-600">
                                ยังไม่ได้เลือกผลงาน
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                กรุณารอผู้จัดเลือกผลงาน
                            </p>
                        </div>
                    @endif
                </div>

                @if ($submission)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-semibold text-slate-800">
                            ไฟล์ผลงาน
                        </h2>

                        <div class="mt-4 space-y-3">
                            @forelse ($submission->files as $file)
                                <div
                                    class="flex items-center justify-between gap-4 rounded-xl border p-4
                                           {{ $currentFile?->id === $file->id
                                                ? 'border-blue-300 bg-blue-50'
                                                : 'border-slate-200' }}"
                                >
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-700">
                                            {{ $file->original_name
                                                ?? $file->file_name
                                                ?? 'ไฟล์ผลงาน' }}
                                        </p>

                                        @if ($currentFile?->id === $file->id)
                                            <p class="mt-1 text-xs text-blue-600">
                                                ไฟล์ที่ผู้จัดกำลังนำเสนอ
                                            </p>
                                        @endif
                                    </div>

                                    @if ($file->file_path)
                                        <a
                                            href="{{ asset('storage/' . $file->file_path) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="shrink-0 rounded-lg bg-white px-3 py-2
                                                   text-xs font-semibold text-blue-600 ring-1
                                                   ring-blue-200 transition hover:bg-blue-50"
                                        >
                                            เปิดไฟล์
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">
                                    ไม่มีไฟล์แนบ
                                </p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </section>

            {{-- Scoring --}}
            <section class="xl:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 p-5">
                        <h2 class="font-semibold text-slate-800">
                            แบบให้คะแนน
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            กรอกคะแนนให้ครบทุกเกณฑ์
                        </p>
                    </div>

                    @if (!$submission)
                        <div class="p-8 text-center text-sm text-slate-500">
                            รอผู้จัดเลือกผลงาน
                        </div>
                    @elseif ($rubrics->isEmpty())
                        <div class="p-8 text-center text-sm text-red-600">
                            การแข่งขันนี้ยังไม่มีเกณฑ์การให้คะแนน
                        </div>
                    @else
                        <form
                            action="{{ route(
                                'judge.judging-rooms.scores.draft',
                                $session
                            ) }}"
                            method="POST"
                            class="divide-y divide-slate-100"
                        >
                            @csrf

                            @foreach ($rubrics as $rubric)
                                @php
                                    $existingScore = $scores->get($rubric->id);
                                @endphp

                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <label
                                                for="score_{{ $rubric->id }}"
                                                class="font-semibold text-slate-700"
                                            >
                                                {{ $rubric->criteria_name }}
                                            </label>

                                            @if ($rubric->description)
                                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                                    {{ $rubric->description }}
                                                </p>
                                            @endif
                                        </div>

                                        <span class="shrink-0 rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            เต็ม {{ number_format($rubric->max_score, 2) }}
                                        </span>
                                    </div>

                                    <input
                                        id="score_{{ $rubric->id }}"
                                        type="number"
                                        name="scores[{{ $rubric->id }}][score]"
                                        value="{{ old(
                                            "scores.{$rubric->id}.score",
                                            $existingScore?->score
                                        ) }}"
                                        min="0"
                                        max="{{ $rubric->max_score }}"
                                        step="0.01"
                                        required
                                        @disabled(!$isLive || $isSubmitted)
                                        class="mt-4 w-full rounded-xl border border-slate-300
                                               bg-slate-50 px-4 py-3 text-lg font-semibold
                                               text-slate-800 outline-none transition
                                               focus:border-blue-500 focus:bg-white
                                               focus:ring-4 focus:ring-blue-100
                                               disabled:cursor-not-allowed disabled:opacity-60"
                                    >

                                    <textarea
                                        name="scores[{{ $rubric->id }}][comment]"
                                        rows="2"
                                        placeholder="ความคิดเห็นเพิ่มเติม (ไม่บังคับ)"
                                        @disabled(!$isLive || $isSubmitted)
                                        class="mt-3 w-full rounded-xl border border-slate-300
                                               bg-slate-50 px-4 py-3 text-sm text-slate-700
                                               outline-none transition focus:border-blue-500
                                               focus:bg-white focus:ring-4 focus:ring-blue-100
                                               disabled:cursor-not-allowed disabled:opacity-60"
                                    >{{ old(
                                        "scores.{$rubric->id}.comment",
                                        $existingScore?->comment
                                    ) }}</textarea>

                                    @error("scores.{$rubric->id}.score")
                                        <p class="mt-2 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            @endforeach

                            <div class="space-y-3 bg-slate-50 p-5">
                                @if ($isSubmitted)
                                    <div class="rounded-xl bg-emerald-100 px-4 py-3 text-center text-sm font-semibold text-emerald-700">
                                        ยืนยันคะแนนของผลงานนี้แล้ว
                                    </div>
                                @elseif ($isLive)
                                    <button
                                        type="submit"
                                        class="w-full rounded-xl border border-blue-200
                                               bg-white px-4 py-3 text-sm font-semibold
                                               text-blue-600 transition hover:bg-blue-50"
                                    >
                                        บันทึกร่างคะแนน
                                    </button>
                                @else
                                    <div class="rounded-xl bg-slate-200 px-4 py-3 text-center text-sm text-slate-500">
                                        บันทึกคะแนนได้เมื่อห้องกำลัง Live
                                    </div>
                                @endif
                            </div>
                        </form>

                        @if ($isLive && !$isSubmitted)
                            <form
                                action="{{ route(
                                    'judge.judging-rooms.scores.submit',
                                    $session
                                ) }}"
                                method="POST"
                                class="px-5 pb-5"
                                onsubmit="return confirm('ยืนยันคะแนนหรือไม่? หลังยืนยันแล้วจะแก้ไขไม่ได้')"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-3
                                           text-sm font-semibold text-white shadow-sm
                                           transition hover:bg-emerald-700"
                                >
                                    ยืนยันส่งคะแนน
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </section>
        </div>

        <a
            href="{{ route('judge.judging-rooms.index') }}"
            class="inline-flex rounded-xl border border-slate-300 bg-white
                   px-5 py-2.5 text-sm font-semibold text-slate-600
                   transition hover:bg-slate-50"
        >
            กลับหน้ารายการห้อง
        </a>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stateUrl = @json(
                route('judge.judging-rooms.state', $session)
            );

            let currentVersion = Number(
                @json($session->state_version)
            );

            let polling = false;

            const checkRoomState = async () => {
                if (polling || document.hidden) {
                    return;
                }

                polling = true;

                try {
                    const response = await fetch(stateUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        return;
                    }

                    const state = await response.json();

                    if (
                        Number(state.state_version) !== currentVersion
                    ) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error(
                        'ไม่สามารถตรวจสถานะห้องได้',
                        error
                    );
                } finally {
                    polling = false;
                }
            };

            setInterval(checkRoomState, 2000);
        });
    </script>
@endpush