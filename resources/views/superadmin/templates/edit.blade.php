@extends('layouts.app')

@section('title', 'แก้ไข Template')

@section('header')
<div>
    <h1 class="text-3xl font-bold text-slate-800">แก้ไข Template</h1>
    <p class="mt-2 text-sm text-slate-500">แก้ไขข้อมูลแม่แบบการแข่งขัน</p>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-5xl px-6 py-8">
    <form action="{{ route('superadmin.templates.update', $template) }}"
          method="POST"
          enctype="multipart/form-data"
          class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        @csrf
        @method('PUT')

        {{-- Card header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 bg-gradient-to-r from-slate-50 to-white px-8 py-6">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-800">{{ $template->template_name }}</h1>
                    <p class="text-sm text-slate-500">แก้ไขรายละเอียดของ Template</p>
                </div>
            </div>

            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition has-[:not(:checked)]:border-slate-200 has-[:not(:checked)]:bg-slate-100 has-[:not(:checked)]:text-slate-500">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       class="peer sr-only"
                       {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                <span class="h-2 w-2 rounded-full bg-emerald-500 peer-[:not(:checked)]:bg-slate-400"></span>
                <span>เปิดใช้งาน</span>
            </label>
        </div>

        <div class="border-t border-slate-100"></div>

        {{-- Body --}}
        <div class="grid grid-cols-1 gap-8 px-8 py-8 lg:grid-cols-3">

            {{-- Left: text fields --}}
            <div class="space-y-6 lg:col-span-2">

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="template_name" class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            ชื่อ Template
                        </label>
                        <input
                            id="template_name"
                            type="text"
                            name="template_name"
                            value="{{ old('template_name', $template->template_name) }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-semibold text-slate-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 @error('template_name') border-red-400 @enderror">
                        @error('template_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="template_slug" class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5M10.172 13.828a4 4 0 010-5.656l3-3a4 4 0 015.656 5.656l-1.5 1.5" />
                            </svg>
                            Slug
                        </label>
                        <input
                            id="template_slug"
                            type="text"
                            name="template_slug"
                            value="{{ old('template_slug', $template->template_slug) }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm text-slate-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 @error('template_slug') border-red-400 @enderror">
                        @error('template_slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="default_description" class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h10M4 18h6" />
                        </svg>
                        รายละเอียด
                    </label>
                    <textarea
                        id="default_description"
                        name="default_description"
                        rows="7"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 @error('default_description') border-red-400 @enderror">{{ old('default_description', $template->default_description) }}</textarea>
                    @error('default_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Right: cover image --}}
            <div>
                <label class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    ภาพหน้าปก
                </label>

                <div class="flex aspect-square w-full flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-slate-200 bg-slate-50">

                {{-- รูปภาพเดิมหรือรูปภาพที่เพิ่งเลือก --}}
                <img
                    id="cover_image_preview"
                    src="{{ $template->cover_image ? asset('storage/'.$template->cover_image) : '' }}"
                    alt="ตัวอย่างภาพหน้าปก"
                    class="{{ $template->cover_image ? '' : 'hidden' }} h-full w-full object-cover">

                {{-- แสดงเมื่อยังไม่มีรูปภาพ --}}
                <div
                    id="cover_image_placeholder"
                    class="{{ $template->cover_image ? 'hidden' : '' }} flex flex-col items-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-10 w-10 text-slate-300"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>

                    <p class="mt-3 text-sm text-slate-400">
                        ไม่มีรูปภาพ
                    </p>
                </div>
            </div>

                <label for="cover_image"
                       class="mt-3 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-blue-300 hover:text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 12v6a2 2 0 002 2h12a2 2 0 002-2v-6M16 6l-4-4m0 0L8 6m4-4v13" />
                    </svg>
                    เปลี่ยนภาพหน้าปก
                    <input id="cover_image" type="file" name="cover_image" accept="image/*" class="hidden">
                </label>
                <p class="mt-2 text-center text-xs text-slate-400">JPG, PNG ไม่เกิน 2MB</p>
                @error('cover_image')
                    <p class="mt-1 text-center text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Existing competition template form fields --}}
        <div class="border-t border-slate-100 px-8 py-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">แก้ไขช่องกรอกข้อมูล</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        ข้อมูลจากตาราง competition_template_form_fields
                    </p>
                </div>

                <a
                    href="{{ route('superadmin.templates.form-fields.create', ['template' => $template->id]) }}"
                    class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-50 px-4 text-sm font-semibold text-blue-700 ring-1 ring-blue-200 transition hover:bg-blue-100">
                    จัดการ Form Builder
                </a>
            </div>

            @error('form_fields')
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $message }}
                </div>
            @enderror

            <div class="space-y-5">
                @forelse($template->formFields as $index => $field)
                    @php
                        $fieldOptions = is_array($field->options)
                            ? implode(PHP_EOL, $field->options)
                            : $field->options;
                    @endphp

                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <h3 class="font-semibold text-slate-800">{{ $field->label }}</h3>
                                    <p class="text-xs text-slate-400">รหัสฟิลด์ #{{ $field->id }}</p>
                                </div>
                            </div>

                            <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                                {{ ucfirst($field->field_type) }}
                            </span>
                        </div>

                        <input
                            type="hidden"
                            name="form_fields[{{ $field->id }}][id]"
                            value="{{ $field->id }}">

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="field_label_{{ $field->id }}" class="mb-2 block text-sm font-medium text-slate-700">
                                    ชื่อช่องกรอก <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="field_label_{{ $field->id }}"
                                    type="text"
                                    name="form_fields[{{ $field->id }}][label]"
                                    value="{{ old("form_fields.{$field->id}.label", $field->label) }}"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @error("form_fields.{$field->id}.label")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="field_type_{{ $field->id }}" class="mb-2 block text-sm font-medium text-slate-700">
                                    ประเภทข้อมูล <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="field_type_{{ $field->id }}"
                                    name="form_fields[{{ $field->id }}][field_type]"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    @foreach([
                                        'text' => 'ข้อความสั้น',
                                        'textarea' => 'ข้อความยาว',
                                        'number' => 'ตัวเลข',
                                        'email' => 'อีเมล',
                                        'phone' => 'เบอร์โทรศัพท์',
                                        'date' => 'วันที่',
                                        'file' => 'อัปโหลดไฟล์',
                                        'select' => 'Dropdown',
                                        'radio' => 'Radio',
                                        'checkbox' => 'Checkbox',
                                    ] as $typeValue => $typeLabel)
                                        <option
                                            value="{{ $typeValue }}"
                                            {{ old("form_fields.{$field->id}.field_type", $field->field_type) === $typeValue ? 'selected' : '' }}>
                                            {{ $typeLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("form_fields.{$field->id}.field_type")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="field_system_field_{{ $field->id }}" class="mb-2 block text-sm font-medium text-slate-700">
                                    ฟิลด์ระบบ
                                    <span class="text-xs font-normal text-slate-400">(ใช้จับคู่ข้อมูลผู้ติดต่อ/ผลงาน)</span>
                                </label>
                                <select
                                    id="field_system_field_{{ $field->id }}"
                                    name="form_fields[{{ $field->id }}][system_field]"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <option value="">-- ไม่ใช่ฟิลด์ระบบ --</option>
                                    @foreach([
                                        'contact_name' => 'contact_name (ชื่อผู้ติดต่อ) *จำเป็น',
                                        'contact_email' => 'contact_email (อีเมล) *จำเป็น',
                                        'contact_phone' => 'contact_phone (เบอร์โทร) *จำเป็น',
                                        'project_title' => 'project_title (ชื่อผลงาน)',
                                        'project_description' => 'project_description (รายละเอียดผลงาน)',
                                        'project_file' => 'project_file (ไฟล์ผลงาน)',
                                    ] as $sysValue => $sysLabel)
                                        <option
                                            value="{{ $sysValue }}"
                                            {{ old("form_fields.{$field->id}.system_field", $field->system_field) === $sysValue ? 'selected' : '' }}>
                                            {{ $sysLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("form_fields.{$field->id}.system_field")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="field_placeholder_{{ $field->id }}" class="mb-2 block text-sm font-medium text-slate-700">
                                    Placeholder
                                </label>
                                <input
                                    id="field_placeholder_{{ $field->id }}"
                                    type="text"
                                    name="form_fields[{{ $field->id }}][placeholder]"
                                    value="{{ old("form_fields.{$field->id}.placeholder", $field->placeholder) }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>

                            <div>
                                <label for="field_help_{{ $field->id }}" class="mb-2 block text-sm font-medium text-slate-700">
                                    คำอธิบาย
                                </label>
                                <input
                                    id="field_help_{{ $field->id }}"
                                    type="text"
                                    name="form_fields[{{ $field->id }}][help_text]"
                                    value="{{ old("form_fields.{$field->id}.help_text", $field->help_text) }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>

                            <div class="md:col-span-2">
                                <label for="field_options_{{ $field->id }}" class="mb-2 block text-sm font-medium text-slate-700">
                                    ตัวเลือก
                                </label>
                                <textarea
                                    id="field_options_{{ $field->id }}"
                                    name="form_fields[{{ $field->id }}][options]"
                                    rows="3"
                                    placeholder="หนึ่งตัวเลือกต่อหนึ่งบรรทัด ใช้กับ Dropdown, Radio และ Checkbox"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old("form_fields.{$field->id}.options", $fieldOptions) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-6 border-t border-slate-200 pt-4">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="hidden" name="form_fields[{{ $field->id }}][is_required]" value="0">
                                <input
                                    type="checkbox"
                                    name="form_fields[{{ $field->id }}][is_required]"
                                    value="1"
                                    {{ old("form_fields.{$field->id}.is_required", $field->is_required) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                จำเป็นต้องกรอก
                            </label>

                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="hidden" name="form_fields[{{ $field->id }}][is_active]" value="0">
                                <input
                                    type="checkbox"
                                    name="form_fields[{{ $field->id }}][is_active]"
                                    value="1"
                                    {{ old("form_fields.{$field->id}.is_active", $field->is_active) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                เปิดใช้งาน
                            </label>
                        </div>
                    </section>
                @empty
                    <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 py-12 text-center">
                        <p class="font-medium text-slate-600">ยังไม่มีช่องกรอกข้อมูล</p>
                        <p class="mt-1 text-sm text-slate-400">เพิ่มช่องกรอกข้อมูลผ่าน Form Builder ก่อน</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Footer / actions --}}
        <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-8 py-5">
            <a href="{{ route('superadmin.templates.index') }}"
               class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 font-medium text-slate-700 transition hover:bg-slate-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                กลับ
            </a>

            <button
                type="submit"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                บันทึกข้อมูล                                            
            </button>
        </div>

    </form>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('cover_image');
            const preview = document.getElementById('cover_image_preview');
            const placeholder = document.getElementById('cover_image_placeholder');

            input.addEventListener('change', function () {
                const file = this.files[0];

                if (!file) {
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    alert('กรุณาเลือกไฟล์รูปภาพเท่านั้น');
                    this.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('รูปภาพต้องมีขนาดไม่เกิน 2MB');
                    this.value = '';
                    return;
                }

                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');

                preview.onload = function () {
                    URL.revokeObjectURL(preview.src);
                };
            });
        });
    </script>

@endsection