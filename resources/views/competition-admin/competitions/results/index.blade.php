@extends('layouts.app')

@section('title', 'ผลการแข่งขัน')

@section('header')
    <div>
        <h1 class="text-xl font-bold text-slate-800">
            ผลการแข่งขัน
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            {{ $competition->title }}
        </p>
    </div>
@endsection

@section('content')
    <div class="mx-auto w-full max-w-7xl space-y-6">

        

        {{-- Summary --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    เกณฑ์การให้คะแนน
                </p>

                <p class="mt-2 text-2xl font-bold text-slate-900">
                    {{ $activeRubricCount }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    กรรมการที่ตอบรับ
                </p>

                <p class="mt-2 text-2xl font-bold text-slate-900">
                    {{ $acceptedJudgeCount }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    ผลงานพร้อมจัดอันดับ
                </p>

                <p class="mt-2 text-2xl font-bold text-emerald-600">
                    {{ $rankedSubmissions->count() }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    ยังตัดสินไม่ครบ
                </p>

                <p class="mt-2 text-2xl font-bold text-amber-600">
                    {{ $pendingSubmissions->count() }}
                </p>
            </div>
        </div>

        {{-- Result Readiness --}}
        @if ($isReadyForResults)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                <p class="font-semibold text-emerald-800">
                    ผลการแข่งขันพร้อมแสดงอันดับแล้ว
                </p>

                <p class="mt-1 text-sm text-emerald-700">
                    คุณสามารถเลือกเผยแพร่ผลงานที่ต้องการเข้าสู่ KM ได้จากตารางด้านล่าง
                </p>
            </div>
        @else
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                <p class="font-semibold text-amber-800">
                    ผลการแข่งขันยังไม่พร้อมสมบูรณ์
                </p>

                <p class="mt-1 text-sm text-amber-700">
                    ต้องจบการตัดสินและกรรมการต้องส่งคะแนนให้ครบทุกผลงาน
                </p>
            </div>
        @endif

        {{-- Ranking --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="font-bold text-slate-900">
                    ตารางคะแนนและอันดับ
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    แสดงเฉพาะผลงานที่กรรมการส่งคะแนนครบแล้ว
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                อันดับ
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                รหัส
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                ชื่อผลงาน
                            </th>

                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">
                                คะแนน
                            </th>

                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase text-slate-500">
                                การเผยแพร่ KM
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($rankedSubmissions as $submission)
                            @php
                                $knowledgeItem = $submission->knowledgeItem;

                                $isPublishedToKm =
                                    $knowledgeItem &&
                                    $knowledgeItem->status === 'published';
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl bg-blue-50 px-3 font-bold text-blue-700 ring-1 ring-blue-200">
                                        {{ $submission->rank }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $submission->submission_code }}
                                </td>

                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ $submission->project_title }}
                                    </p>

                                    @if ($submission->team_name)
                                        <p class="mt-1 text-xs text-slate-500">
                                            ทีม {{ $submission->team_name }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <span class="text-lg font-bold text-blue-700">
                                        {{ number_format(
                                            (float) $submission->final_score,
                                            2
                                        ) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center gap-2">
                                        @if ($isPublishedToKm)
                                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                                เผยแพร่ใน KM แล้ว
                                            </span>

                                            <form
                                                action="{{ route(
                                                    'competition-admin.submissions.km.unpublish',
                                                    $submission
                                                ) }}"
                                                method="POST"
                                                onsubmit="return confirm('ยืนยันการถอนผลงานนี้ออกจากหน้า KM หรือไม่? ผลงานต้นฉบับจะไม่ถูกลบ')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="text-xs font-semibold text-red-600 transition hover:text-red-700"
                                                >
                                                    ถอนออกจาก KM
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                                ยังไม่เผยแพร่
                                            </span>

                                            <form
                                                action="{{ route(
                                                    'competition-admin.submissions.km.publish',
                                                    $submission
                                                ) }}"
                                                method="POST"
                                                onsubmit="return confirm('ยืนยันเผยแพร่ผลงานนี้เข้าสู่ KM หรือไม่?')"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700"
                                                >
                                                    เผยแพร่สู่ KM
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="px-6 py-12 text-center text-sm text-slate-500"
                                >
                                    @if (!$sessionFinished)
                                        ต้องจบการตัดสินก่อนจึงจะแสดงอันดับ
                                    @else
                                        ยังไม่มีผลงานที่กรรมการให้คะแนนครบ
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pending --}}
        @if ($pendingSubmissions->isNotEmpty())
            <div class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
                <div class="border-b border-amber-200 bg-amber-50 px-6 py-5">
                    <h2 class="font-bold text-amber-900">
                        ผลงานที่ยังตัดสินไม่ครบ
                    </h2>

                    <p class="mt-1 text-sm text-amber-700">
                        ผลงานส่วนนี้ยังไม่สามารถจัดอันดับหรือเผยแพร่เข้าสู่ KM ได้
                    </p>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach ($pendingSubmissions as $submission)
                        <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">
                                    {{ $submission->project_title }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $submission->submission_code }}
                                </p>
                            </div>

                            <div class="text-sm font-medium text-amber-700">
                                คะแนนที่ส่งแล้ว
                                {{ $submission->submitted_scores_count }}
                                /
                                {{ $expectedScoreCount }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection