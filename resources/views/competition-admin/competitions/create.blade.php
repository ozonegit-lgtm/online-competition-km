@extends('layouts.app')

@section('title', 'สร้างการแข่งขัน')

@section('header')
<div>
    <h1 class="text-2xl font-bold text-slate-800">สร้างการแข่งขัน</h1>
    <p class="mt-1 text-sm text-slate-500">เลือกประเภทและแม่แบบสำหรับสร้างการแข่งขัน</p>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <p class="font-semibold text-red-700">กรุณาตรวจสอบข้อมูลอีกครั้ง</p>
            <ul class="mt-2 list-inside list-disc text-sm text-red-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('competition-admin.competitions.store') }}"
        class="grid items-start gap-6 lg:grid-cols-2">
        @csrf

        {{-- Competition information --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-xl font-bold text-slate-800">ข้อมูลการแข่งขัน</h2>
                <p class="mt-1 text-sm text-slate-500">กรอกข้อมูลและเลือกแม่แบบที่ต้องการใช้งาน</p>
            </div>

            <div class="space-y-6 p-6">
                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-700">
                        ชื่อการแข่งขัน <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="title"
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        required
                        placeholder="เช่น การประกวดโปสเตอร์ด้วย AI"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-semibold text-slate-700">
                        ประเภทการแข่งขัน <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        <option value="">-- เลือกประเภทการแข่งขัน --</option>
                        @forelse($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @empty
                            <option value="" disabled>ไม่มีประเภทการแข่งขันที่เปิดใช้งาน</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label for="template_id" class="block text-sm font-semibold text-slate-700">
                        แม่แบบฟอร์มรับผลงาน
                    </label>
                    <select
                        id="template_id"
                        name="template_id"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                        <option value="">-- ไม่ใช้แม่แบบ --</option>
                        @forelse($templates as $template)
                            <option
                                value="{{ $template->id }}"
                                {{ (string) old('template_id') === (string) $template->id ? 'selected' : '' }}>
                                {{ $template->template_name }} ({{ $template->formFields->count() }} ช่อง)
                            </option>
                        @empty
                            <option value="" disabled>ไม่มีแม่แบบที่เปิดใช้งาน</option>
                        @endforelse
                    </select>
                    <p class="mt-2 text-xs text-slate-500">
                        ช่องจากแม่แบบจะถูกคัดลอกมาเป็นฟอร์มรับผลงานของการแข่งขันนี้
                    </p>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700">รายละเอียด</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="รายละเอียดและเงื่อนไขการแข่งขัน"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">{{ old('description') }}</textarea>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="competition_type" class="block text-sm font-semibold text-slate-700">รูปแบบผู้สมัคร</label>
                        <select
                            id="competition_type"
                            name="competition_type"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="individual" {{ old('competition_type', 'individual') === 'individual' ? 'selected' : '' }}>บุคคล</option>
                            <option value="team" {{ old('competition_type') === 'team' ? 'selected' : '' }}>ทีม</option>
                        </select>
                    </div>

                    <div>
                        <label for="visibility" class="block text-sm font-semibold text-slate-700">การเข้าถึง</label>
                        <select
                            id="visibility"
                            name="visibility"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            <option value="public" {{ old('visibility', 'public') === 'public' ? 'selected' : '' }}>สาธารณะ</option>
                            <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>ใช้รหัสเข้าร่วม</option>
                        </select>
                    </div>
                </div>

                {{-- Competition schedule --}}
                <div class="border-t border-slate-200 pt-6">
                    <h3 class="text-lg font-bold text-slate-800">กำหนดการแข่งขัน</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        กำหนดช่วงรับผลงาน ช่วงตัดสิน และวันประกาศผล
                    </p>

                    @php
                        $scheduleFields = [
                            'registration_start' => 'วันเริ่มรับผลงาน',
                            'registration_end' => 'วันสิ้นสุดรับผลงาน',
                            'judging_start' => 'วันเริ่มตัดสิน',
                            'judging_end' => 'วันสิ้นสุดการตัดสิน',
                            'result_announcement' => 'วันประกาศผล',
                        ];
                    @endphp

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        @foreach($scheduleFields as $fieldName => $fieldLabel)
                            <div class="{{ $fieldName === 'result_announcement' ? 'sm:col-span-2' : '' }}">
                                <label for="{{ $fieldName }}" class="block text-sm font-semibold text-slate-700">
                                    {{ $fieldLabel }} <span class="text-red-500">*</span>
                                </label>

                                <div class="relative mt-2">
                                    <input
                                        id="{{ $fieldName }}"
                                        type="datetime-local"
                                        name="{{ $fieldName }}"
                                        value="{{ old($fieldName) }}"
                                        required
                                        data-datetime-input
                                        data-display-id="{{ $fieldName }}_display"
                                        aria-describedby="{{ $fieldName }}_hint"
                                        style="opacity: 0; color: transparent; -webkit-text-fill-color: transparent;"
                                        class="peer absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0">

                                    <div class="pointer-events-none flex min-h-[66px] items-center justify-between gap-3 rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 transition peer-focus:border-blue-600 peer-focus:bg-white peer-focus:ring-4 peer-focus:ring-blue-100">
                                        <div class="min-w-0">
                                            <p id="{{ $fieldName }}_display"
                                                class="truncate text-sm font-semibold text-slate-700">
                                                เลือกวันและเวลา
                                            </p>
                                            <p id="{{ $fieldName }}_hint" class="mt-1 text-xs text-slate-400">
                                                วัน / เดือน / ปี • ชั่วโมง : นาที
                                            </p>
                                        </div>

                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                @error($fieldName)
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:grid-cols-2">
                <a
                    href="{{ route('competition-admin.competitions.index') }}"
                    class="flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:bg-slate-100">
                    ย้อนกลับ
                </a>
                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700">
                    สร้างการแข่งขัน
                </button>
            </div>
        </section>

        {{-- Selected template preview --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm lg:sticky lg:top-24">
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-xl font-bold text-slate-800">ตัวอย่างฟอร์มรับผลงาน</h2>
                <p class="mt-1 text-sm text-slate-500">เปลี่ยนตามแม่แบบที่เลือกทางด้านซ้าย</p>
            </div>

            <div id="templateEmptyState" class="px-6 py-16 text-center">
                <h3 class="font-semibold text-slate-700">ยังไม่ได้เลือกแม่แบบ</h3>
                <p class="mt-2 text-sm text-slate-500">เลือกแม่แบบเพื่อดูช่องสำหรับส่งผลงาน</p>
            </div>

            @foreach($templates as $template)
                <div
                    data-template-preview="{{ $template->id }}"
                    class="hidden max-h-[70vh] overflow-y-auto p-4 sm:p-5">
                    {{-- Template cover --}}
                    <div
                        class="mb-3 w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
                        style="height: 140px; min-height: 140px; max-height: 140px;">
                        @if($template->cover_image)
                            <img
                                src="{{ asset('storage/' . $template->cover_image) }}"
                                alt="ภาพหน้าปก {{ $template->template_name }}"
                                width="800"
                                height="140"
                                class="block w-full"
                                style="width: 100%; height: 140px; min-height: 140px; max-height: 140px; object-fit: cover; object-position: center;">
                        @else
                            <div class="flex h-full items-center justify-center text-sm text-slate-400">
                                ไม่มีรูปภาพ Template
                            </div>
                        @endif
                    </div>

                    {{-- Template information --}}
                    <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                        <h3 class="font-bold text-slate-800">{{ $template->template_name }}</h3>
                        <p class="mt-1 line-clamp-2 text-sm leading-5 text-slate-600">
                            {{ $template->default_description ?: 'ไม่มีรายละเอียดแม่แบบ' }}
                        </p>
                        <p class="mt-2 text-xs font-medium text-blue-700">
                            ฟอร์มรับผลงานทั้งหมด {{ $template->formFields->count() }} ช่อง
                        </p>
                    </div>

                    <div class="grid items-start gap-3 sm:grid-cols-2">
                        @forelse($template->formFields as $field)
                            @continue(!$field->is_active)
                            @php
                                $options = is_array($field->options)
                                    ? $field->options
                                    : (json_decode($field->options ?? '[]', true) ?: []);

                                $fullWidthField = in_array(
                                    $field->field_type,
                                    ['textarea', 'file', 'select', 'radio', 'checkbox']
                                );
                            @endphp

                            <div class="h-full rounded-xl border border-slate-200 bg-white p-4
                                {{ $fullWidthField ? 'sm:col-span-2' : '' }}">
                                <label class="block text-sm font-semibold text-slate-700">
                                    {{ $field->label }}
                                    @if($field->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                                <div class="mt-2">
                                    @switch($field->field_type)
                                        @case('textarea')
                                            <textarea disabled rows="3" placeholder="{{ $field->placeholder }}" class="w-full resize-none rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm"></textarea>
                                        @break
                                        @case('select')
                                            <select disabled class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                                                <option>{{ $field->placeholder ?: 'กรุณาเลือก' }}</option>
                                                @foreach($options as $option)<option>{{ $option }}</option>@endforeach
                                            </select>
                                        @break
                                        @case('radio')
                                        @case('checkbox')
                                            <div class="space-y-2">
                                                @forelse($options as $option)
                                                    <label class="flex items-center gap-2 text-sm text-slate-600">
                                                        <input type="{{ $field->field_type }}" disabled>
                                                        {{ $option }}
                                                    </label>
                                                @empty
                                                    <p class="text-sm text-slate-400">ยังไม่มีตัวเลือก</p>
                                                @endforelse
                                            </div>
                                        @break
                                        @case('file')
                                            <div class="rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">เลือกไฟล์เพื่อส่งผลงาน</div>
                                        @break
                                        @default
                                            <input
                                                type="{{ $field->field_type === 'phone' ? 'tel' : $field->field_type }}"
                                                disabled
                                                placeholder="{{ $field->placeholder }}"
                                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                                    @endswitch
                                </div>

                                @if($field->help_text)
                                    <p class="mt-1.5 text-xs leading-5 text-slate-500">{{ $field->help_text }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-xl border-2 border-dashed border-slate-300 py-10 text-center text-slate-500">แม่แบบนี้ยังไม่มีช่องกรอกข้อมูล</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </section>
    </form>
</div>

<style>
    [data-datetime-input] {
        opacity: 0 !important;
        color: transparent !important;
        -webkit-text-fill-color: transparent !important;
    }

    [data-datetime-input]::-webkit-datetime-edit,
    [data-datetime-input]::-webkit-datetime-edit-fields-wrapper,
    [data-datetime-input]::-webkit-datetime-edit-text,
    [data-datetime-input]::-webkit-datetime-edit-month-field,
    [data-datetime-input]::-webkit-datetime-edit-day-field,
    [data-datetime-input]::-webkit-datetime-edit-year-field,
    [data-datetime-input]::-webkit-datetime-edit-hour-field,
    [data-datetime-input]::-webkit-datetime-edit-minute-field,
    [data-datetime-input]::-webkit-datetime-edit-ampm-field {
        color: transparent !important;
        -webkit-text-fill-color: transparent !important;
    }

    [data-datetime-input]::-webkit-calendar-picker-indicator {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        cursor: pointer;
        opacity: 0;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const templateSelect = document.getElementById('template_id');
    const emptyState = document.getElementById('templateEmptyState');
    const previews = document.querySelectorAll('[data-template-preview]');
    const dateTimeInputs = document.querySelectorAll('[data-datetime-input]');

    function formatThaiDateTime(value) {
        if (!value) {
            return 'เลือกวันและเวลา';
        }

        const [dateValue, timeValue = ''] = value.split('T');
        const [year, month, day] = dateValue.split('-').map(Number);
        const date = new Date(year, month - 1, day);

        if (Number.isNaN(date.getTime())) {
            return 'เลือกวันและเวลา';
        }

        const formattedDate = new Intl.DateTimeFormat('th-TH', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        }).format(date);

        const formattedTime = timeValue.slice(0, 5);

        return formattedTime
            ? `${formattedDate} เวลา ${formattedTime} น.`
            : formattedDate;
    }

    function updateDateTimeDisplay(input) {
        const display = document.getElementById(input.dataset.displayId);

        if (!display) {
            return;
        }

        display.textContent = formatThaiDateTime(input.value);
        display.classList.toggle('text-blue-700', input.value !== '');
        display.classList.toggle('text-slate-700', input.value === '');
    }

    function showSelectedTemplate() {
        const selectedId = templateSelect.value;

        previews.forEach(function (preview) {
            preview.classList.toggle(
                'hidden',
                preview.dataset.templatePreview !== selectedId
            );
        });

        emptyState.classList.toggle('hidden', selectedId !== '');
    }

    templateSelect.addEventListener('change', showSelectedTemplate);

    dateTimeInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            updateDateTimeDisplay(input);
        });

        updateDateTimeDisplay(input);
    });

    showSelectedTemplate();
});
</script>
@endsection
