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

        @php
            $currentSubmission = isset($currentSubmission)
                ? $currentSubmission
                : $session->currentSubmission;
            $displayFile = $currentFile ?? $session->currentFile ?? null;
            $filePath = $displayFile?->file_path;
            $fileUrl = $filePath ? asset('storage/' . $filePath) : null;
            $extension = strtolower(pathinfo($filePath ?? '', PATHINFO_EXTENSION));
            $isImage = in_array(
                $extension,
                ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
                true
            );
        @endphp

        {{-- Submission control and current submission --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-sm font-bold text-white">
                                1
                            </span>
                            <div>
                                <h2 class="font-semibold text-slate-800">เลือกผลงานที่ต้องการแสดง</h2>
                                <p class="mt-0.5 text-sm text-slate-500">
                                    เมื่อยืนยัน ผลงานนี้จะแสดงให้กรรมการทุกคนทันที
                                </p>
                            </div>
                        </div>
                    </div>

                    @if (!$session->isEnded() && !$session->isClosed())
                    <form
                        action="{{ route(
                            'competition-admin.competitions.judging-room.submission',
                            $competition
                        ) }}"
                        method="POST"
                        class="flex w-full flex-col gap-3 sm:flex-row lg:max-w-2xl"
                    >
                        @csrf
                        @method('PUT')

                        <select
                            name="submission_id"
                            required
                            class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white
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

                        <button
                            type="submit"
                            class="shrink-0 rounded-xl bg-blue-600 px-5 py-3 text-sm
                                   font-semibold text-white shadow-sm transition hover:bg-blue-700
                                   focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                            แสดงผลงานนี้
                        </button>
                    </form>
                    @else
                        <div class="rounded-xl bg-slate-200 px-4 py-3 text-sm text-slate-600">
                            ห้องจบหรือปิดแล้ว ไม่สามารถเปลี่ยนผลงานได้
                        </div>
                    @endif
                </div>

                @error('submission_id')
                    <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if ($currentSubmission)
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                    ผลงานปัจจุบันที่กรรมการกำลังเห็น
                                </p>
                            </div>
                            <h2 class="mt-2 text-xl font-bold text-slate-800 sm:text-2xl">
                                {{ $currentSubmission->project_title }}
                            </h2>
                            <p class="mt-1 text-sm font-medium text-slate-500">
                                รหัส {{ $currentSubmission->submission_code }}
                            </p>
                        </div>
                        <span class="w-fit shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ $currentSubmission->status }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,2fr)_minmax(260px,1fr)]">
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            @if ($fileUrl && $isImage)
                                <div class="flex min-h-80 items-center justify-center p-4 sm:min-h-[28rem]">
                                    <img src="{{ $fileUrl }}"
                                         alt="ผลงาน {{ $currentSubmission->project_title }}"
                                         class="max-h-[70vh] w-auto max-w-full rounded-xl object-contain">
                                </div>
                            @elseif ($fileUrl)
                                <div class="flex min-h-80 flex-col items-center justify-center p-8 text-center">
                                    <p class="font-medium text-slate-700">ไฟล์นี้ไม่ใช่รูปภาพ</p>
                                    <p class="mt-1 text-sm text-slate-500">เปิดไฟล์ในหน้าต่างใหม่เพื่อดูผลงาน</p>
                                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener"
                                       class="mt-4 inline-flex rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                                        เปิดผลงาน
                                    </a>
                                </div>
                            @else
                                <div class="flex min-h-80 items-center justify-center p-8 text-center text-sm text-slate-400">
                                    ยังไม่ได้เลือกไฟล์สำหรับแสดง
                                </div>
                            @endif
                        </div>

                        <aside class="space-y-5">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <h3 class="text-sm font-semibold text-slate-700">ข้อมูลผู้ส่งผลงาน</h3>

                                <dl class="mt-3 space-y-3 text-sm">
                                    <div>
                                        <dt class="text-xs font-medium text-slate-400">ชื่อผู้ติดต่อ</dt>
                                        <dd class="mt-1 font-medium text-slate-700">
                                            {{ $currentSubmission->contact_name ?: 'ไม่ระบุชื่อ' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs font-medium text-slate-400">อีเมล</dt>
                                        <dd class="mt-1 break-all text-slate-700">
                                            {{ $currentSubmission->contact_email ?: 'ไม่ระบุอีเมล' }}
                                        </dd>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                        <div>
                                            <dt class="text-xs font-medium text-slate-400">ทีม</dt>
                                            <dd class="mt-1 text-slate-700">
                                                {{ $currentSubmission->team_name ?: '-' }}
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-xs font-medium text-slate-400">โทรศัพท์</dt>
                                            <dd class="mt-1 text-slate-700">
                                                {{ $currentSubmission->contact_phone ?: '-' }}
                                            </dd>
                                        </div>
                                    </div>
                                </dl>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <h3 class="text-sm font-semibold text-slate-700">รายละเอียดผลงาน</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ $currentSubmission->project_description ?: 'ไม่มีรายละเอียดผลงาน' }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-slate-700">ไฟล์ผลงาน</h3>
                                <div class="mt-3 space-y-2">
                                    @forelse ($currentSubmission->files as $file)
                                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3">
                                            <p class="min-w-0 truncate text-sm font-medium text-slate-700">
                                                {{ $file->original_name ?? $file->file_name ?? 'ไฟล์ผลงาน' }}
                                            </p>
                                            @if ($session->current_file_id === $file->id)
                                                <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                    กำลังแสดง
                                                </span>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-400">
                                            ไม่มีไฟล์แนบ
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            @else
                <div class="px-5 py-14 text-center sm:px-6">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-lg font-bold text-slate-400">
                        2
                    </div>
                    <h2 class="mt-4 text-lg font-semibold text-slate-700">ยังไม่ได้เลือกผลงาน</h2>
                    <p class="mt-1 text-sm text-slate-500">เลือกผลงานจากรายการด้านบนเพื่อเริ่มแสดงให้กรรมการ</p>
                </div>
            @endif
        </section>

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
