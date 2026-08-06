@extends('layouts.app')

@section('title', 'แต่งตั้งกรรมการ')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-900">
            แต่งตั้งกรรมการ
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            เลือกกรรมการสำหรับการแข่งขัน {{ $competition->title }}
        </p>
    </div>
@endsection

@section('content')
    @php
        $selectedJudgeIds = collect(
            old('judge_ids', $assignedJudgeIds)
        )
            ->map(fn ($id) => (int) $id)
            ->all();
    @endphp

    <div class="mx-auto max-w-6xl">

        {{-- ข้อมูลการแข่งขัน --}}
        <section class="mb-6 rounded-2xl border border-slate-200
                        bg-white p-5 shadow-sm">

            <div class="flex flex-col gap-4 sm:flex-row
                        sm:items-center sm:justify-between">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide
                              text-blue-600">
                        การแข่งขัน
                    </p>

                    <h2 class="mt-1 text-xl font-bold text-slate-900">
                        {{ $competition->title }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        กรรมการที่เลือกแล้ว
                        {{ count($assignedJudgeIds) }} คน
                    </p>
                </div>

                <a
                    href="{{ route('superadmin.competitions.judges.list', $competition) }}"
                    class="inline-flex items-center justify-center rounded-xl
                           border border-slate-300 bg-white px-4 py-2.5
                           text-sm font-semibold text-slate-700 transition
                           hover:bg-slate-100">
                    กลับ
                </a>
            </div>
        </section>

        <form
            method="POST"
            action="{{ route(
                'superadmin.competitions.judges.sync',
                $competition
            ) }}"
        >
            @csrf
            @method('PUT')

            <section class="overflow-hidden rounded-2xl border
                            border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-bold text-slate-900">
                        รายชื่อผู้ใช้งาน Role Judge
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        เลือกผู้ใช้ที่ต้องการแต่งตั้งเป็นกรรมการ
                    </p>
                </div>

                @if ($judges->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <p class="font-semibold text-slate-700">
                            ยังไม่มีผู้ใช้งาน Role Judge
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            กรุณาสร้างบัญชี Judge ก่อนแต่งตั้งกรรมการ
                        </p>

                        <a
                            href="{{ route('superadmin.createUser') }}"
                            class="mt-5 inline-flex rounded-xl bg-blue-600
                                   px-5 py-3 text-sm font-semibold text-white
                                   transition hover:bg-blue-700"
                        >
                            สร้างบัญชี Judge
                        </a>
                    </div>
                @else
                    <div class="grid gap-4 p-6 md:grid-cols-2">

                        @foreach ($judges as $judge)
                            @php
                                $profile = $judge->adminProfile;

                                $displayName = trim(
                                    ($profile?->first_name ?? '') . ' ' .
                                    ($profile?->last_name ?? '')
                                );

                                $displayName = $displayName !== ''
                                    ? $displayName
                                    : $judge->username;

                                $avatarUrl = $profile?->avatar
                                    ? asset('storage/' . $profile->avatar)
                                    : null;

                                $isSelected = in_array(
                                    (int) $judge->id,
                                    $selectedJudgeIds,
                                    true
                                );

                                $assignment = $competition
                                    ->judgeAssignments
                                    ->firstWhere(
                                        'judge_id',
                                        $judge->id
                                    );
                            @endphp

                            <label
                                class="group relative cursor-pointer rounded-2xl
                                       border p-4 transition
                                       {{ $isSelected
                                            ? 'border-blue-400 bg-blue-50'
                                            : 'border-slate-200 bg-white hover:border-blue-300 hover:bg-slate-50' }}"
                            >
                                <div class="flex items-start gap-4">

                                    <input
                                        type="checkbox"
                                        name="judge_ids[]"
                                        value="{{ $judge->id }}"
                                        @checked($isSelected)
                                        class="mt-1 h-5 w-5 rounded border-slate-300
                                               text-blue-600 focus:ring-blue-500"
                                    >

                                    @if ($avatarUrl)
                                        <img
                                            src="{{ $avatarUrl }}"
                                            alt="{{ $displayName }}"
                                            class="h-12 w-12 shrink-0 rounded-full
                                                   object-cover"
                                        >
                                    @else
                                        <div class="flex h-12 w-12 shrink-0
                                                    items-center justify-center
                                                    rounded-full bg-blue-100
                                                    font-bold text-blue-700">
                                            {{ mb_strtoupper(
                                                mb_substr(
                                                    $displayName,
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center
                                                    justify-between gap-2">

                                            <p class="truncate font-bold
                                                      text-slate-900">
                                                {{ $displayName }}
                                            </p>

                                            @if ($assignment)
                                                <span class="rounded-full
                                                             bg-emerald-100
                                                             px-2.5 py-1
                                                             text-xs font-semibold
                                                             text-emerald-700">
                                                    แต่งตั้งแล้ว
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-1 truncate text-sm
                                                  text-slate-500">
                                            {{ $judge->email }}
                                        </p>

                                        @if ($profile?->position)
                                            <p class="mt-1 text-xs
                                                      text-slate-500">
                                                {{ $profile->position }}
                                            </p>
                                        @endif

                                        @if ($profile?->organization)
                                            <p class="mt-1 text-xs
                                                      text-slate-400">
                                                {{ $profile->organization }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200
                                bg-slate-50 px-6 py-5 sm:flex-row
                                sm:items-center sm:justify-between">

                        <p class="text-sm text-slate-500">
                            กรรมการที่เลือกจะได้รับสิทธิ์เข้าห้องตัดสินทันที
                        </p>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center
                                   rounded-xl bg-blue-600 px-6 py-3
                                   text-sm font-semibold text-white
                                   shadow-sm transition hover:bg-blue-700
                                   focus:outline-none focus:ring-4
                                   focus:ring-blue-200"
                        >
                            บันทึกรายชื่อกรรมการ
                        </button>
                    </div>
                @endif
            </section>
        </form>
    </div>
@endsection