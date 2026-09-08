@extends('layouts.app')

@section('title', 'แต่งตั้งกรรมการ')

@section('header')
    <div>
        <h1 class="text-slate-900 text-xl font-bold">
            แต่งตั้งกรรมการ
        </h1>

        <p class="mt-1 text-slate-500 text-xs">
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

    <div class="mx-auto max-w-7xl">
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-bold text-red-700">
                        ไม่สามารถบันทึกรายชื่อกรรมการได้
                    </p>

                    <ul class="mt-2 list-disc space-y-1 text-sm text-red-600 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($judgesLocked)
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-bold text-amber-800">
                        รายชื่อกรรมการถูกล็อกแล้ว
                    </p>

                    <p class="mt-1 text-sm leading-6 text-amber-700">
                        การแข่งขันเริ่มตัดสินแล้ว
                        ไม่สามารถเพิ่มหรือถอดกรรมการได้
                    </p>
                </div>
            @endif

        {{-- ข้อมูลการแข่งขัน --}}
        <section class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                        การแข่งขัน
                    </p>

                    <h2 class="mt-1 text-slate-900 text-base font-semibold">
                        {{ $competition->title }}
                    </h2>

                    <p class="mt-1 text-slate-500 text-xs">
                        กรรมการที่เลือกแล้ว
                        {{ count($assignedJudgeIds) }} คน
                    </p>
                </div>

                <a
                    href="{{ route('superadmin.competitions.judges.list', $competition) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-700 transition hover:bg-slate-100 h-9 px-3">
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

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-4 py-4">
                    <h2 class="text-slate-900 text-base font-semibold">
                        รายชื่อผู้ใช้งาน Role Judge
                    </h2>

                    <p class="mt-1 text-slate-500 text-xs">
                        เลือกผู้ใช้ที่ต้องการแต่งตั้งเป็นกรรมการ
                    </p>
                </div>

                @if ($judges->isEmpty())
                    <div class="px-4 py-4 text-center">
                        <p class="font-semibold text-slate-700">
                            ยังไม่มีผู้ใช้งาน Role Judge
                        </p>

                        <p class="mt-2 text-slate-500 text-xs">
                            กรุณาสร้างบัญชี Judge ก่อนแต่งตั้งกรรมการ
                        </p>

                        <a
                            href="{{ route('superadmin.createUser') }}"
                            class="mt-4 inline-flex rounded-xl bg-blue-600 text-sm font-semibold text-white transition hover:bg-blue-700 items-center justify-center h-9 px-3"
                        >
                            สร้างบัญชี Judge
                        </a>
                    </div>
                @else
                    <div class="grid gap-4 p-4 md:grid-cols-2">

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
                                class="group relative rounded-xl border p-4 transition {{ $judgesLocked
                                            ? 'cursor-not-allowed opacity-75'
                                            : 'cursor-pointer' }} {{ $isSelected
                                            ? 'border-blue-400 bg-blue-50'
                                            : 'border-slate-200 bg-white hover:border-blue-300 hover:bg-slate-50' }}"
                            >
                                <div class="flex items-start gap-4">

                                    <input
                                        type="checkbox"
                                        name="judge_ids[]"
                                        value="{{ $judge->id }}"
                                        @checked($isSelected)
                                        @disabled($judgesLocked)
                                        class="mt-1 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                                    >

                                    @if ($avatarUrl)
                                        <img
                                            src="{{ $avatarUrl }}"
                                            alt="{{ $displayName }}"
                                            class="h-12 w-12 shrink-0 rounded-full object-cover"
                                        >
                                    @else
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">
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
                                        <div class="flex flex-wrap items-center justify-between gap-2">

                                            <p class="truncate font-bold text-slate-900">
                                                {{ $displayName }}
                                            </p>

                                            @if ($assignment)
                                                <span class="rounded-full bg-emerald-100 font-semibold text-emerald-700 text-xs px-2.5 py-1">
                                                    แต่งตั้งแล้ว
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-1 truncate text-slate-500 text-xs">
                                            {{ $judge->email }}
                                        </p>

                                        @if ($profile?->position)
                                            <p class="mt-1 text-slate-500 text-xs">
                                                {{ $profile->position }}
                                            </p>
                                        @endif

                                        @if ($profile?->organization)
                                            <p class="mt-1 text-slate-400 text-xs">
                                                {{ $profile->organization }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">

                        @if ($judgesLocked)
                            <p class="text-sm font-semibold text-amber-700">
                                ดูรายชื่อได้อย่างเดียว
                                เนื่องจากเริ่มการตัดสินแล้ว
                            </p>
                        @else
                            <p class="text-slate-500 text-xs">
                                กรรมการที่เลือกจะได้รับสิทธิ์เข้าห้องตัดสินทันที
                            </p>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 h-9 px-3"
                            >
                                บันทึกรายชื่อกรรมการ
                            </button>

                        @endif
                    </div>
                @endif
            </section>
        </form>
    </div>
@endsection