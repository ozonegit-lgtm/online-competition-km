@extends('layouts.app')

@section('title', 'รายละเอียดการแข่งขัน')

@section('header')
    {{-- แถบเครื่องมือจัดการการแข่งขัน --}}
    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            {{-- ชื่อเครื่องมือ --}}
            <div class="flex min-w-0 items-center gap-2.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M10 13a5 5 0 007.5.5l2-2a5 5 0 00-7-7l-1 1"/>
                        <path d="M14 11a5 5 0 00-7.5-.5l-2 2a5 5 0 007 7l1-1"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-800">
                        เครื่องมือจัดการการแข่งขัน
                    </h2>

                    <p class="truncate text-xs text-slate-500">
                        {{ $competition->title }}
                    </p>
                </div>
            </div>

            {{-- ปุ่มเครื่องมือ --}}
            <div class="flex flex-wrap items-center gap-1.5">

                <a
                    href="{{ route('competition-admin.competitions.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                    กลับหน้ารายการ
                </a>

                <a
                    href="{{ route('competitions.submissions.create', $competition) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M14 3h7v7"/>
                        <path d="M10 14L21 3"/>
                        <path d="M21 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h5"/>
                    </svg>
                    ดูหน้าส่งผลงาน
                </a>

                <button
                    type="button"
                    id="copySubmissionLink"
                    data-copy-url="{{ route('competitions.submissions.create', $competition) }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="11" height="11" rx="2"/>
                        <path d="M15 9V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2h3"/>
                    </svg>

                    <span id="copySubmissionLinkText">
                        คัดลอกลิงก์ฟอร์ม
                    </span>
                </button>

                <a
                    href="{{ route('competition-admin.competitions.edit', $competition) }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-blue-600 bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:border-blue-700 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.1 2.1 0 013 3L8 18l-4 1 1-4z"/>
                    </svg>
                    แก้ไขการแข่งขัน
                </a>

                <a
                    href="{{ route('competition-admin.competitions.rubrics.index', $competition) }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-amber-500 bg-amber-500 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:border-amber-600 hover:bg-amber-600 focus:outline-none focus:ring-4 focus:ring-amber-200"
                >
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                    </svg>
                    จัดการเกณฑ์คะแนน
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @php
        $statusOptions = [
            'draft' => ['label' => 'ฉบับร่าง', 'class' => 'bg-slate-100 text-slate-700 ring-slate-200'],
            'published' => ['label' => 'เผยแพร่แล้ว', 'class' => 'bg-blue-50 text-blue-700 ring-blue-200'],
            'open' => ['label' => 'เปิดรับผลงาน', 'class' => 'bg-green-50 text-green-700 ring-green-200'],
            'closed' => ['label' => 'ปิดรับผลงาน', 'class' => 'bg-red-50 text-red-700 ring-red-200'],
            'judging' => ['label' => 'กำลังตัดสิน', 'class' => 'bg-violet-50 text-violet-700 ring-violet-200'],
            'completed' => ['label' => 'เสร็จสิ้น', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
            'archived' => ['label' => 'เก็บถาวร', 'class' => 'bg-slate-100 text-slate-600 ring-slate-200'],
            'upcoming' => ['label' => 'ยังไม่เปิดรับผลงาน','class' => 'bg-amber-50 text-amber-700 ring-amber-200',],
            'waiting_result' => ['label' => 'รอประกาศผล','class' => 'bg-slate-100 text-slate-700 ring-slate-200',],
        ];

        $statusKey = $competition->display_status;
        $status = $statusOptions[$statusKey] ?? [
            'label' => $statusKey ?: 'ไม่ระบุสถานะ',
            'class' => 'bg-slate-100 text-slate-700 ring-slate-200',
        ];

        $competitionTypeLabels = [
            'individual' => 'ประเภทบุคคล',
            'team' => 'ประเภททีม',
        ];

        $visibilityLabels = [
            'public' => 'สาธารณะ',
            'private' => 'เฉพาะผู้มีรหัสเข้าร่วม',
        ];

        $fieldTypeLabels = [
            'text' => 'ข้อความสั้น',
            'textarea' => 'ข้อความหลายบรรทัด',
            'number' => 'ตัวเลข',
            'email' => 'อีเมล',
            'phone' => 'เบอร์โทรศัพท์',
            'tel' => 'เบอร์โทรศัพท์',
            'date' => 'วันที่',
            'file' => 'แนบไฟล์',
            'url' => 'ลิงก์เว็บไซต์',
            'select' => 'รายการแบบเลือก',
            'radio' => 'ตัวเลือกเดียว',
            'checkbox' => 'หลายตัวเลือก',
        ];

        $coverImage = $competition->cover_image ?: $competition->template?->cover_image;
        $coverUrl = null;

        if ($coverImage) {
            $coverUrl = \Illuminate\Support\Str::startsWith($coverImage, ['http://', 'https://'])
                ? $coverImage
                : \Illuminate\Support\Facades\Storage::disk('public')->url($coverImage);
        }

        $displayFields = $competition->formFields->isNotEmpty()
            ? $competition->formFields
            : ($competition->template?->formFields ?? collect());

        $dateText = function ($date) {
            return $date ? $date->format('d/m/Y H:i น.') : 'ยังไม่กำหนด';
        };
    @endphp

    {{-- ลดความกว้างหลักจาก 7xl → 6xl --}}
    <div class="mx-auto w-full max-w-6xl space-y-4">

        {{-- ข้อมูลหลัก --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            {{-- ลดความสูง Banner --}}
            <div class="relative h-44 w-full overflow-hidden bg-slate-100 sm:h-56 lg:h-64">
                @if ($coverUrl)
                    <img
                        src="{{ $coverUrl }}"
                        alt="ภาพปก {{ $competition->title }}"
                        class="h-full w-full object-cover object-center"
                    >
                @else
                    <div class="flex h-full w-full items-center justify-center px-6 text-center">
                        <div>
                            <svg
                                class="mx-auto h-10 w-10 text-slate-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Z"
                                />
                            </svg>

                            <p class="mt-2 text-xs font-medium text-slate-500">
                                ยังไม่มีภาพปกการแข่งขัน
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="p-5 sm:p-6">

                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $status['class'] }}">
                        {{ $status['label'] }}
                    </span>

                    @if ($competition->category)
                        <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                            {{ $competition->category->category_name }}
                        </span>
                    @endif
                </div>

                <h2 class="mt-3 text-xl font-bold leading-tight text-slate-900 sm:text-2xl">
                    {{ $competition->title ?: 'ยังไม่ได้ระบุชื่อการแข่งขัน' }}
                </h2>

                <div class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                    {{ $competition->description ?: 'ยังไม่มีรายละเอียดการแข่งขัน' }}
                </div>

                <dl class="mt-5 grid gap-3 border-t border-slate-200 pt-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            รูปแบบการแข่งขัน
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $competitionTypeLabels[$competition->competition_type] ?? ($competition->competition_type ?: 'ยังไม่กำหนด') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            การเข้าถึง
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $visibilityLabels[$competition->visibility] ?? ($competition->visibility ?: 'ยังไม่กำหนด') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            แม่แบบ
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $competition->template?->template_name ?? 'ไม่ได้ใช้แม่แบบ' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            รหัสเข้าร่วม
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $competition->visibility === 'private' ? ($competition->access_code ?: 'ยังไม่กำหนด') : 'ไม่จำเป็นต้องใช้รหัส' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        {{-- ตัวเลขสรุป --}}
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-slate-500">ผลงานที่ส่ง</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">
                    {{ number_format($competition->submissions_count ?? 0) }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-slate-500">กรรมการที่มอบหมาย</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">
                    {{ number_format($competition->judge_assignments_count ?? 0) }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-slate-500">เกณฑ์ให้คะแนน</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">
                    {{ number_format($competition->rubrics_count ?? 0) }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-slate-500">รางวัล</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">
                    {{ number_format($competition->awards_count ?? 0) }}
                </p>
            </div>

        </section>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">

            <div class="space-y-4">

                {{-- กำหนดการ --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            กำหนดการแข่งขัน
                        </h3>

                        <p class="mt-0.5 text-xs text-slate-500">
                            วันและเวลาของแต่ละช่วงดำเนินงาน
                        </p>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">

                        @foreach ([
                            ['label' => 'เริ่มรับผลงาน', 'value' => $competition->registration_start],
                            ['label' => 'ปิดรับผลงาน', 'value' => $competition->registration_end],
                            ['label' => 'เริ่มตัดสิน', 'value' => $competition->judging_start],
                            ['label' => 'สิ้นสุดการตัดสิน', 'value' => $competition->judging_end],
                            ['label' => 'ประกาศผล', 'value' => $competition->result_announcement],
                        ] as $schedule)

                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 {{ $loop->last ? 'sm:col-span-2' : '' }}">
                                <p class="text-xs font-medium text-slate-500">
                                    {{ $schedule['label'] }}
                                </p>

                                <p class="mt-0.5 text-sm font-semibold text-slate-800">
                                    {{ $dateText($schedule['value']) }}
                                </p>
                            </div>

                        @endforeach

                    </div>
                </section>

                {{-- แบบฟอร์มรับผลงาน --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">
                                แบบฟอร์มรับผลงาน
                            </h3>

                            <p class="mt-0.5 text-xs text-slate-500">
                                ช่องข้อมูลที่ผู้เข้าร่วมต้องกรอกตอนส่งผลงาน
                            </p>
                        </div>

                        <span class="inline-flex w-fit shrink-0 items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                            {{ $displayFields->count() }} ช่อง
                        </span>
                    </div>

                    @if ($displayFields->isEmpty())

                        <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
                            <p class="text-sm font-semibold text-slate-700">
                                ยังไม่มีช่องกรอกข้อมูล
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                เพิ่มช่องรับข้อมูลให้การแข่งขันหรือกำหนดผ่านแม่แบบก่อน
                            </p>
                        </div>

                    @else

                        <div class="mt-4 grid gap-3 lg:grid-cols-2">

                            @foreach ($displayFields as $field)

                                @php
                                    $options = $field->options ?? [];

                                    if (is_string($options)) {
                                        $decodedOptions = json_decode($options, true);

                                        $options = is_array($decodedOptions)
                                            ? $decodedOptions
                                            : preg_split('/\r\n|\r|\n|,/', $options);
                                    }

                                    $options = collect($options)
                                        ->map(fn ($option) =>
                                            is_array($option)
                                                ? ($option['label'] ?? $option['value'] ?? '')
                                                : $option
                                        )
                                        ->filter(fn ($option) => filled($option))
                                        ->values();

                                    $isWideField = in_array(
                                        $field->field_type,
                                        ['textarea', 'file', 'radio', 'checkbox'],
                                        true
                                    );
                                @endphp

                                <article class="rounded-lg border border-slate-200 bg-slate-50/60 p-3 {{ $isWideField ? 'lg:col-span-2' : '' }}">

                                    <div class="flex items-start justify-between gap-3">

                                        <div class="flex min-w-0 items-start gap-2.5">

                                            <span class="inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded-md bg-slate-800 px-2 py-1 text-[10px] font-bold text-white">
                                                ช่องที่ {{ $loop->iteration }}
                                            </span>

                                            <div class="min-w-0">

                                                <h4 class="text-sm font-semibold leading-6 text-slate-900">
                                                    {{ $field->label }}

                                                    @if ($field->is_required)
                                                        <span class="text-red-500">*</span>
                                                    @endif
                                                </h4>

                                                @if ($field->help_text)
                                                    <p class="mt-0.5 text-[11px] leading-4 text-slate-500">
                                                        {{ $field->help_text }}
                                                    </p>
                                                @endif

                                            </div>
                                        </div>

                                        <div class="flex shrink-0 flex-wrap justify-end gap-1">
                                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                                {{ $fieldTypeLabels[$field->field_type] ?? $field->field_type }}
                                            </span>

                                            @unless ($field->is_active)
                                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 ring-1 ring-inset ring-slate-200">
                                                    ปิดใช้งาน
                                                </span>
                                            @endunless
                                        </div>
                                    </div>

                                    <div class="mt-2.5 border-t border-slate-200 pt-2.5">

                                        @switch($field->field_type)

                                            @case('textarea')
                                                <textarea
                                                    rows="2"
                                                    disabled
                                                    placeholder="{{ $field->placeholder }}"
                                                    class="w-full resize-none rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder:text-slate-400 disabled:cursor-not-allowed disabled:opacity-100"
                                                ></textarea>
                                            @break

                                            @case('select')
                                                <select
                                                    disabled
                                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 disabled:cursor-not-allowed disabled:opacity-100"
                                                >
                                                    <option>{{ $field->placeholder ?: '-- กรุณาเลือก --' }}</option>

                                                    @foreach ($options as $option)
                                                        <option>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            @break

                                            @case('radio')
                                            @case('checkbox')

                                                <div class="grid gap-1.5 sm:grid-cols-2">

                                                    @forelse ($options as $option)

                                                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700">
                                                            <input
                                                                type="{{ $field->field_type }}"
                                                                disabled
                                                                class="h-3.5 w-3.5 border-slate-300 text-blue-600"
                                                            >

                                                            <span>{{ $option }}</span>
                                                        </label>

                                                    @empty

                                                        <p class="text-xs text-slate-400">
                                                            ยังไม่มีตัวเลือก
                                                        </p>

                                                    @endforelse

                                                </div>

                                            @break

                                            @case('file')

                                                <input
                                                    type="file"
                                                    disabled
                                                    class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-500 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-slate-600 disabled:cursor-not-allowed disabled:opacity-100"
                                                >

                                            @break

                                            @default

                                                @php
                                                    $inputType = match ($field->field_type) {
                                                        'number', 'email', 'date', 'url' => $field->field_type,
                                                        'phone', 'tel' => 'tel',
                                                        default => 'text',
                                                    };
                                                @endphp

                                                <input
                                                    type="{{ $inputType }}"
                                                    disabled
                                                    placeholder="{{ $field->placeholder }}"
                                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-700 placeholder:text-slate-400 disabled:cursor-not-allowed disabled:opacity-100"
                                                >

                                        @endswitch

                                    </div>
                                </article>

                            @endforeach

                        </div>

                    @endif
                </section>

            </div>

            <aside class="space-y-4">

                {{-- การเผยแพร่ --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            การตั้งค่าการเผยแพร่
                        </h3>

                        <p class="mt-0.5 text-xs text-slate-500">
                            ตรวจสอบสิ่งที่อนุญาตให้แสดงหลังการแข่งขัน
                        </p>
                    </div>

                    <dl class="mt-4 space-y-2.5">

                        <div class="flex min-h-16 items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">

                            <div class="min-w-0">
                                <dt class="text-xs font-semibold text-slate-800">
                                    แสดงคะแนนต่อสาธารณะ
                                </dt>

                                <p class="mt-0.5 text-[11px] leading-4 text-slate-500">
                                    ผู้เข้าชมสามารถดูผลคะแนนได้
                                </p>
                            </div>

                            <dd class="inline-flex w-20 shrink-0 items-center justify-center gap-1 rounded-full border px-2 py-1 text-[10px] font-semibold {{ $competition->publish_scores ? 'border-green-200 bg-green-50 text-green-700' : 'border-slate-200 bg-white text-slate-600' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $competition->publish_scores ? 'bg-green-500' : 'bg-slate-400' }}"></span>

                                {{ $competition->publish_scores ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </dd>

                        </div>

                        <div class="flex min-h-16 items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">

                            <div class="min-w-0">
                                <dt class="text-xs font-semibold text-slate-800">
                                    นำผลงานเข้า KM
                                </dt>

                                <p class="mt-0.5 text-[11px] leading-4 text-slate-500">
                                    เผยแพร่ผลงานในคลังความรู้
                                </p>
                            </div>

                            <dd class="inline-flex w-20 shrink-0 items-center justify-center gap-1 rounded-full border px-2 py-1 text-[10px] font-semibold {{ $competition->publish_km ? 'border-green-200 bg-green-50 text-green-700' : 'border-slate-200 bg-white text-slate-600' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $competition->publish_km ? 'bg-green-500' : 'bg-slate-400' }}"></span>

                                {{ $competition->publish_km ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </dd>

                        </div>

                    </dl>
                </section>

                {{-- ข้อมูลระบบ --}}
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h3 class="text-base font-bold text-slate-900">
                        ข้อมูลระบบ
                    </h3>

                    <dl class="mt-4 space-y-3">

                        <div>
                            <dt class="text-[11px] font-medium text-slate-400">
                                ผู้สร้างการแข่งขัน
                            </dt>

                            <dd class="mt-0.5 text-xs font-semibold text-slate-800">
                                {{ $competition->creator?->name ?? $competition->creator?->username ?? 'ไม่พบข้อมูลผู้สร้าง' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-[11px] font-medium text-slate-400">
                                อีเมลผู้สร้าง
                            </dt>

                            <dd class="mt-0.5 break-all text-xs font-semibold text-slate-800">
                                {{ $competition->creator?->email ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-[11px] font-medium text-slate-400">
                                สร้างเมื่อ
                            </dt>

                            <dd class="mt-0.5 text-xs font-semibold text-slate-800">
                                {{ $dateText($competition->created_at) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-[11px] font-medium text-slate-400">
                                แก้ไขล่าสุด
                            </dt>

                            <dd class="mt-0.5 text-xs font-semibold text-slate-800">
                                {{ $dateText($competition->updated_at) }}
                            </dd>
                        </div>

                    </dl>
                </section>

                {{-- เกณฑ์การให้คะแนน --}}
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">

                        <div>
                            <h2 class="text-base font-bold text-slate-800">
                                เกณฑ์การให้คะแนน
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-500">
                                เกณฑ์ที่ใช้สำหรับตัดสินผลงาน
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-semibold text-blue-700">
                            {{ $competition->rubrics->count() }} เกณฑ์
                        </span>

                    </div>

                    @php
                        $activeRubrics = $competition->rubrics->where('is_active', true);
                        $totalMaxScore = $activeRubrics->sum('max_score');
                    @endphp

                    @if ($competition->rubrics->isEmpty())

                        <div class="px-5 py-8 text-center">

                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-lg">
                                📋
                            </div>

                            <p class="mt-3 text-sm font-semibold text-slate-700">
                                ยังไม่มีเกณฑ์การให้คะแนน
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                กรุณาสร้างเกณฑ์ก่อนเริ่มการตัดสิน
                            </p>

                        </div>

                    @else

                        <div class="divide-y divide-slate-100">

                            @foreach ($competition->rubrics as $index => $rubric)

                                <div class="px-5 py-3 {{ ! $rubric->is_active ? 'bg-slate-50 opacity-60' : '' }}">

                                    <div class="flex items-start justify-between gap-3">

                                        <div class="flex min-w-0 items-start gap-2.5">

                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-50 text-[10px] font-bold text-blue-700">
                                                {{ $index + 1 }}
                                            </span>

                                            <div class="min-w-0">

                                                <p class="break-words text-xs font-semibold text-slate-800">
                                                    {{ $rubric->criteria_name }}
                                                </p>

                                                @if ($rubric->description)
                                                    <p class="mt-0.5 line-clamp-2 text-[11px] leading-4 text-slate-500">
                                                        {{ $rubric->description }}
                                                    </p>
                                                @endif

                                                @if (! $rubric->is_active)
                                                    <span class="mt-1 inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                                        ปิดใช้งาน
                                                    </span>
                                                @endif

                                            </div>

                                        </div>

                                        <div class="shrink-0 text-right">

                                            <p class="text-base font-bold text-blue-600">
                                                {{ number_format($rubric->max_score, 2) }}
                                            </p>

                                            <p class="text-[10px] text-slate-400">
                                                คะแนน
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                        <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-5 py-3">

                            <span class="text-xs font-semibold text-slate-700">
                                คะแนนรวม
                            </span>

                            <div class="text-right">

                                <span class="text-lg font-bold {{ (float) $totalMaxScore === 100.0 ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ number_format($totalMaxScore, 2) }}
                                </span>

                                <span class="text-xs text-slate-500">
                                    / 100
                                </span>

                            </div>

                        </div>

                    @endif

                    <div class="border-t border-slate-200 p-4">

                        <a
                            href="{{ route('competition-admin.competitions.rubrics.index', $competition) }}"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200"
                        >
                            จัดการเกณฑ์การให้คะแนน
                        </a>

                    </div>

                </section>

            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const copyButton = document.getElementById('copySubmissionLink');
            const copyText = document.getElementById('copySubmissionLinkText');

            if (!copyButton || !copyText) {
                return;
            }

            copyButton.addEventListener('click', async function () {
                const url = copyButton.dataset.copyUrl;

                try {
                    await navigator.clipboard.writeText(url);

                    copyText.textContent = 'คัดลอกแล้ว ✓';

                    setTimeout(function () {
                        copyText.textContent = 'คัดลอกลิงก์ฟอร์ม';
                    }, 2000);
                } catch (error) {
                    window.prompt('กรุณาคัดลอกลิงก์นี้', url);
                }
            });
        });
    </script>
@endpush