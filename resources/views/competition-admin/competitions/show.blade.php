@extends('layouts.app')

@section('title', 'รายละเอียดการแข่งขัน')

@section('header')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">รายละเอียดการแข่งขัน</h1>
            <p class="mt-1 text-sm text-slate-500">
                ตรวจสอบข้อมูลและแบบฟอร์มรับผลงานของการแข่งขัน
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('competition-admin.competitions.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200">
                กลับหน้ารายการ
            </a>
            <a href="{{ route('competitions.submissions.create', $competition) }}"target="_blank"
                class="inline-flex items-center justify-center rounded-xl border border-blue-200
                    bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100">
                ดูหน้าส่งผลงาน
            </a>

            <a href="{{ route('competition-admin.competitions.edit', $competition) }}"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                แก้ไขการแข่งขัน
            </a>
        </div>
    </div>
@endsection

@section('content')
    @php
        $statusOptions = [
            'draft' => ['label' => 'ฉบับร่าง', 'class' => 'bg-slate-100 text-slate-700 ring-slate-200'],
            'published' => ['label' => 'เผยแพร่แล้ว', 'class' => 'bg-blue-50 text-blue-700 ring-blue-200'],
            'open' => ['label' => 'เปิดรับผลงาน', 'class' => 'bg-green-50 text-green-700 ring-green-200'],
            'closed' => ['label' => 'ปิดรับผลงาน', 'class' => 'bg-amber-50 text-amber-700 ring-amber-200'],
            'judging' => ['label' => 'กำลังตัดสิน', 'class' => 'bg-violet-50 text-violet-700 ring-violet-200'],
            'completed' => ['label' => 'เสร็จสิ้น', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
            'archived' => ['label' => 'เก็บถาวร', 'class' => 'bg-slate-100 text-slate-600 ring-slate-200'],
        ];

        $status = $statusOptions[$competition->status] ?? [
            'label' => $competition->status ?: 'ไม่ระบุสถานะ',
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

        // ใช้ฟิลด์ของการแข่งขันก่อน หากยังไม่ได้คัดลอกฟิลด์มาให้ใช้ฟิลด์จากแม่แบบ
        $displayFields = $competition->formFields->isNotEmpty()
            ? $competition->formFields
            : ($competition->template?->formFields ?? collect());

        $dateText = function ($date) {
            return $date ? $date->format('d/m/Y H:i น.') : 'ยังไม่กำหนด';
        };
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-6">
        {{-- ข้อมูลหลัก --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            {{-- ล็อกภาพปกเป็นแบนเนอร์แนวนอน ทุกการแข่งขันจะแสดงในขนาดเดียวกัน --}}
            <div class="relative h-56 w-full overflow-hidden bg-slate-100 sm:h-72 lg:h-80">
                @if ($coverUrl)
                    <img src="{{ $coverUrl }}" alt="ภาพปก {{ $competition->title }}"
                        class="h-full w-full object-cover object-center">
                @else
                    <div class="flex h-full w-full items-center justify-center px-6 text-center">
                        <div>
                            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Z" />
                            </svg>
                            <p class="mt-3 text-sm font-medium text-slate-500">ยังไม่มีภาพปกการแข่งขัน</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $status['class'] }}">
                            {{ $status['label'] }}
                        </span>

                        @if ($competition->category)
                            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                {{ $competition->category->category_name }}
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-4 text-2xl font-bold leading-tight text-slate-900 sm:text-3xl">
                        {{ $competition->title ?: 'ยังไม่ได้ระบุชื่อการแข่งขัน' }}
                    </h2>

                    <div class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">
                        {{ $competition->description ?: 'ยังไม่มีรายละเอียดการแข่งขัน' }}
                    </div>

                    <dl class="mt-6 grid gap-4 border-t border-slate-200 pt-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">รูปแบบการแข่งขัน</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $competitionTypeLabels[$competition->competition_type] ?? ($competition->competition_type ?: 'ยังไม่กำหนด') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">การเข้าถึง</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $visibilityLabels[$competition->visibility] ?? ($competition->visibility ?: 'ยังไม่กำหนด') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">แม่แบบ</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $competition->template?->template_name ?? 'ไม่ได้ใช้แม่แบบ' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">รหัสเข้าร่วม</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $competition->visibility === 'private' ? ($competition->access_code ?: 'ยังไม่กำหนด') : 'ไม่จำเป็นต้องใช้รหัส' }}
                            </dd>
                        </div>
                    </dl>
            </div>
        </section>

        {{-- ตัวเลขสรุป --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">ผลงานที่ส่ง</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($competition->submissions_count ?? 0) }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">กรรมการที่มอบหมาย</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($competition->judge_assignments_count ?? 0) }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">เกณฑ์ให้คะแนน</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($competition->rubrics_count ?? 0) }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">รางวัล</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($competition->awards_count ?? 0) }}</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-6">
                {{-- กำหนดการ --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">กำหนดการแข่งขัน</h3>
                        <p class="mt-1 text-sm text-slate-500">วันและเวลาของแต่ละช่วงดำเนินงาน</p>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-medium text-slate-500">เริ่มรับผลงาน</p>
                            <p class="mt-1 font-semibold text-slate-800">{{ $dateText($competition->registration_start) }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-medium text-slate-500">ปิดรับผลงาน</p>
                            <p class="mt-1 font-semibold text-slate-800">{{ $dateText($competition->registration_end) }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-medium text-slate-500">เริ่มตัดสิน</p>
                            <p class="mt-1 font-semibold text-slate-800">{{ $dateText($competition->judging_start) }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-medium text-slate-500">สิ้นสุดการตัดสิน</p>
                            <p class="mt-1 font-semibold text-slate-800">{{ $dateText($competition->judging_end) }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2">
                            <p class="text-sm font-medium text-slate-500">ประกาศผล</p>
                            <p class="mt-1 font-semibold text-slate-800">{{ $dateText($competition->result_announcement) }}</p>
                        </div>
                    </div>
                </section>

                {{-- แบบฟอร์มรับผลงาน --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">แบบฟอร์มรับผลงาน</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                ช่องข้อมูลที่ผู้เข้าร่วมต้องกรอกตอนส่งผลงาน
                            </p>
                        </div>

                        <span class="inline-flex w-fit shrink-0 items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                            {{ $displayFields->count() }} ช่อง
                        </span>
                    </div>

                    @if ($displayFields->isEmpty())
                        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                            <p class="text-sm font-semibold text-slate-700">ยังไม่มีช่องกรอกข้อมูล</p>
                            <p class="mt-1 text-sm text-slate-500">เพิ่มช่องรับข้อมูลให้การแข่งขันหรือกำหนดผ่านแม่แบบก่อน</p>
                        </div>
                    @else
                        <div class="mt-6 grid gap-4 lg:grid-cols-2">
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
                                        ->map(fn ($option) => is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : $option)
                                        ->filter(fn ($option) => filled($option))
                                        ->values();

                                    $isWideField = in_array(
                                        $field->field_type,
                                        ['textarea', 'file', 'radio', 'checkbox'],
                                        true
                                    );
                                @endphp

                                <article
                                    class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 {{ $isWideField ? 'lg:col-span-2' : '' }}">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <span class="inline-flex shrink-0 items-center justify-center whitespace-nowrap rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs font-bold text-white">
                                                ช่องที่ {{ $loop->iteration }}
                                            </span>

                                            <div class="min-w-0">
                                                <h4 class="font-semibold leading-7 text-slate-900">
                                                    {{ $field->label }}
                                                    @if ($field->is_required)
                                                        <span class="text-red-500">*</span>
                                                    @endif
                                                </h4>

                                                @if ($field->help_text)
                                                    <p class="mt-0.5 text-xs leading-5 text-slate-500">
                                                        {{ $field->help_text }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex shrink-0 flex-wrap justify-end gap-1.5">
                                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                                {{ $fieldTypeLabels[$field->field_type] ?? $field->field_type }}
                                            </span>

                                            @unless ($field->is_active)
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 ring-1 ring-inset ring-slate-200">
                                                    ปิดใช้งาน
                                                </span>
                                            @endunless
                                        </div>
                                    </div>

                                    <div class="mt-3 border-t border-slate-200 pt-3">
                                        @switch($field->field_type)
                                            @case('textarea')
                                                <textarea rows="2" disabled placeholder="{{ $field->placeholder }}"
                                                    class="w-full resize-none rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 disabled:cursor-not-allowed disabled:opacity-100"></textarea>
                                            @break

                                            @case('select')
                                                <select disabled
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 disabled:cursor-not-allowed disabled:opacity-100">
                                                    <option>{{ $field->placeholder ?: '-- กรุณาเลือก --' }}</option>
                                                    @foreach ($options as $option)
                                                        <option>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            @break

                                            @case('radio')
                                            @case('checkbox')
                                                <div class="grid gap-2 sm:grid-cols-2">
                                                    @forelse ($options as $option)
                                                        <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                                            <input type="{{ $field->field_type }}" disabled
                                                                class="h-4 w-4 border-slate-300 text-blue-600">
                                                            <span>{{ $option }}</span>
                                                        </label>
                                                    @empty
                                                        <p class="text-sm text-slate-400">ยังไม่มีตัวเลือก</p>
                                                    @endforelse
                                                </div>
                                            @break

                                            @case('file')
                                                <input type="file" disabled
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-600 disabled:cursor-not-allowed disabled:opacity-100">
                                            @break

                                            @default
                                                @php
                                                    $inputType = match ($field->field_type) {
                                                        'number', 'email', 'date', 'url' => $field->field_type,
                                                        'phone', 'tel' => 'tel',
                                                        default => 'text',
                                                    };
                                                @endphp

                                                <input type="{{ $inputType }}" disabled placeholder="{{ $field->placeholder }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 disabled:cursor-not-allowed disabled:opacity-100">
                                        @endswitch
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                {{-- การเผยแพร่ --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">การตั้งค่าการเผยแพร่</h3>
                        <p class="mt-1 text-sm text-slate-500">ตรวจสอบสิ่งที่อนุญาตให้แสดงหลังการแข่งขัน</p>
                    </div>

                    <dl class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-1">
                        <div class="flex min-h-20 items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="min-w-0">
                                <dt class="text-sm font-semibold text-slate-800">แสดงคะแนนต่อสาธารณะ</dt>
                                <p class="mt-1 text-xs leading-5 text-slate-500">ผู้เข้าชมสามารถดูผลคะแนนได้</p>
                            </div>

                            <dd class="inline-flex w-24 shrink-0 items-center justify-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $competition->publish_scores ? 'border-green-200 bg-green-50 text-green-700' : 'border-slate-200 bg-white text-slate-600' }}">
                                <span class="h-2 w-2 rounded-full {{ $competition->publish_scores ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                                {{ $competition->publish_scores ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </dd>
                        </div>

                        <div class="flex min-h-20 items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="min-w-0">
                                <dt class="text-sm font-semibold text-slate-800">นำผลงานเข้า KM</dt>
                                <p class="mt-1 text-xs leading-5 text-slate-500">เผยแพร่ผลงานในคลังความรู้</p>
                            </div>

                            <dd class="inline-flex w-24 shrink-0 items-center justify-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $competition->publish_km ? 'border-green-200 bg-green-50 text-green-700' : 'border-slate-200 bg-white text-slate-600' }}">
                                <span class="h-2 w-2 rounded-full {{ $competition->publish_km ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                                {{ $competition->publish_km ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                {{-- ข้อมูลระบบ --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900">ข้อมูลระบบ</h3>

                    <dl class="mt-5 space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-400">ผู้สร้างการแข่งขัน</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $competition->creator?->name ?? $competition->creator?->username ?? 'ไม่พบข้อมูลผู้สร้าง' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-slate-400">อีเมลผู้สร้าง</dt>
                            <dd class="mt-1 break-all text-sm font-semibold text-slate-800">
                                {{ $competition->creator?->email ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-slate-400">สร้างเมื่อ</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $dateText($competition->created_at) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-slate-400">แก้ไขล่าสุด</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                {{ $dateText($competition->updated_at) }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
@endsection
