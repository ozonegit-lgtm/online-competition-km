@extends('layouts.app')

@section('title', 'ควบคุมห้องตัดสิน')

@section('header')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                ห้องตัดสิน
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                {{ $competition->title }}
            </p>
        </div>

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

        <span
            class="inline-flex items-center gap-2 rounded-full px-4 py-2
                   text-sm font-semibold ring-1 {{ $statusConfig['class'] }}"
        >
            <span class="h-2.5 w-2.5 rounded-full {{ $statusConfig['dot'] }}"></span>

            {{ $statusConfig['label'] }}
        </span>
    </div>
@endsection

@section('content')
    <div class="space-y-6">

        {{-- Room controls --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-800">
                        ควบคุมสถานะห้อง
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Version {{ $session->state_version }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    @if ($session->isWaiting())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.start',
                                $competition
                            ) }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-xl bg-emerald-600 px-5 py-2.5
                                       text-sm font-semibold text-white shadow-sm
                                       transition hover:bg-emerald-700"
                            >
                                เริ่ม Live
                            </button>
                        </form>
                    @endif

                    @if ($session->isLive())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.pause',
                                $competition
                            ) }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-xl bg-amber-500 px-5 py-2.5
                                       text-sm font-semibold text-white transition
                                       hover:bg-amber-600"
                            >
                                หยุดชั่วคราว
                            </button>
                        </form>
                    @endif

                    @if ($session->isPaused())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.resume',
                                $competition
                            ) }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-xl bg-emerald-600 px-5 py-2.5
                                       text-sm font-semibold text-white transition
                                       hover:bg-emerald-700"
                            >
                                Live ต่อ
                            </button>
                        </form>
                    @endif

                    @if ($session->isLive() || $session->isPaused())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.end',
                                $competition
                            ) }}"
                            method="POST"
                            onsubmit="return confirm('ยืนยันจบการตัดสินหรือไม่?')"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-xl border border-red-200 bg-white
                                       px-5 py-2.5 text-sm font-semibold text-red-600
                                       transition hover:bg-red-50"
                            >
                                จบการตัดสิน
                            </button>
                        </form>
                    @endif

                    @if ($session->isEnded())
                        <form
                            action="{{ route(
                                'competition-admin.competitions.judging-room.close',
                                $competition
                            ) }}"
                            method="POST"
                            onsubmit="return confirm('ปิดห้องแล้วจะไม่สามารถตัดสินต่อได้ ยืนยันหรือไม่?')"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-xl bg-slate-800 px-5 py-2.5
                                       text-sm font-semibold text-white transition
                                       hover:bg-slate-900"
                            >
                                ปิดห้อง
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        {{-- Readiness --}}
        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    ผลงานพร้อมตัดสิน
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-800">
                    {{ $submissions->count() }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    เกณฑ์ที่เปิดใช้งาน
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-800">
                    {{ $rubrics->count() }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    กรรมการ
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-800">
                    {{ $assignments->count() }}
                </p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">

            {{-- Submission selector --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-1">
                <h2 class="font-semibold text-slate-800">
                    เลือกผลงาน
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    ผลงานที่เลือกจะแสดงให้กรรมการทุกคน
                </p>

                @if (!$session->isEnded() && !$session->isClosed())
                    <form
                        action="{{ route(
                            'competition-admin.competitions.judging-room.submission',
                            $competition
                        ) }}"
                        method="POST"
                        class="mt-4"
                    >
                        @csrf
                        @method('PUT')

                        <select
                            name="submission_id"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-slate-50
                                   px-4 py-3 text-sm text-slate-700 outline-none
                                   transition focus:border-blue-500 focus:bg-white
                                   focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">
                                เลือกผลงาน
                            </option>

                            @foreach ($submissions as $submission)
                                <option
                                    value="{{ $submission->id }}"
                                    @selected(
                                        $session->current_submission_id ===
                                        $submission->id
                                    )
                                >
                                    {{ $submission->submission_code }}
                                    — {{ $submission->project_title }}
                                </option>
                            @endforeach
                        </select>

                        @error('submission_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <button
                            type="submit"
                            class="mt-3 w-full rounded-xl bg-blue-600 px-4 py-3
                                   text-sm font-semibold text-white transition
                                   hover:bg-blue-700"
                        >
                            แสดงผลงานนี้
                        </button>
                    </form>
                @else
                    <div class="mt-4 rounded-xl bg-slate-100 p-4 text-sm text-slate-500">
                        ห้องนี้จบหรือปิดแล้ว ไม่สามารถเปลี่ยนผลงานได้
                    </div>
                @endif
            </section>

            {{-- Current submission --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                            ผลงานปัจจุบัน
                        </p>

                        @if ($session->currentSubmission)
                            <h2 class="mt-2 text-xl font-bold text-slate-800">
                                {{ $session->currentSubmission->project_title }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $session->currentSubmission->submission_code }}
                            </p>
                        @else
                            <h2 class="mt-2 text-lg font-semibold text-slate-500">
                                ยังไม่ได้เลือกผลงาน
                            </h2>
                        @endif
                    </div>

                    @if ($session->currentSubmission)
                        <span class="rounded-full bg-blue-50 px-3 py-1
                                     text-xs font-semibold text-blue-700">
                            {{ $session->currentSubmission->status }}
                        </span>
                    @endif
                </div>

                @if ($session->currentSubmission)
                    <div class="mt-5 rounded-xl bg-slate-50 p-4">
                        <p class="text-sm leading-6 text-slate-600">
                            {{ $session->currentSubmission->project_description
                                ?: 'ไม่มีรายละเอียดผลงาน' }}
                        </p>
                    </div>

                    <div class="mt-5">
                        <h3 class="text-sm font-semibold text-slate-700">
                            ไฟล์ผลงาน
                        </h3>

                        <div class="mt-3 space-y-2">
                            @forelse ($session->currentSubmission->files as $file)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-slate-700">
                                            {{ $file->original_name ?? $file->file_name ?? 'ไฟล์ผลงาน' }}
                                        </p>
                                    </div>

                                    @if ($session->current_file_id === $file->id)
                                        <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            กำลังแสดง
                                        </span>
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
        </div>

        {{-- Judges --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-800">
                    กรรมการในห้อง
                </h2>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($assignments as $assignment)
                    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <p class="font-medium text-slate-700">
                                {{ $assignment->judge->name
                                    ?? $assignment->judge->username
                                    ?? 'ไม่พบข้อมูลกรรมการ' }}
                            </p>

                            <p class="mt-1 text-sm text-slate-400">
                                {{ $assignment->judge->email ?? '-' }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-sm font-medium text-slate-600">
                                {{ $assignment->assignment_status }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $assignment->submitted_at
                                    ? 'ส่งคะแนนแล้ว'
                                    : 'ยังไม่ส่งคะแนน' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-slate-500">
                        ยังไม่มีกรรมการในห้องนี้
                    </div>
                @endforelse
            </div>
        </section>

        <div>
            <a
                href="{{ route('competition-admin.judging-rooms.index') }}"
                class="inline-flex items-center rounded-xl border border-slate-300
                       bg-white px-5 py-2.5 text-sm font-semibold text-slate-600
                       transition hover:bg-slate-50"
            >
                กลับหน้ารายการห้อง
            </a>
        </div>
    </div>
@endsection