@extends('layouts.app')

@section('title', 'รายละเอียด Template')

@section('header')
<div>
    <h1 class="text-3xl font-bold tracking-tight text-slate-800">รายละเอียด Template</h1>
    <p class="mt-2 text-sm text-slate-500">แสดงรายละเอียดและช่องกรอกข้อมูลของ Template การแข่งขัน</p>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <div class="flex min-w-0 items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v18M4 7h16M4 7a2 2 0 012-2h12a2 2 0 012 2m-16 0v10a2 2 0 002 2h12a2 2 0 002-2V7"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-xl font-bold text-slate-800">{{ $template->template_name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">รายละเอียดทั้งหมดของ Template</p>
                </div>
            </div>

            @if($template->is_active)
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-green-50 px-4 py-2 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-200">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    เปิดใช้งาน
                </span>
            @else
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 ring-1 ring-inset ring-rose-200">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    ปิดใช้งาน
                </span>
            @endif
        </div>

        <div class="space-y-10 p-6 lg:p-10">
            {{-- Cover image on the left, information on the right --}}
            <div class="grid gap-8 lg:grid-cols-2 lg:items-stretch">
                <section class="flex flex-col">
                    <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        ภาพหน้าปก
                    </h3>

                    <div
                        class="mt-3 w-full overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm"
                        style="height: 280px; min-height: 280px; max-height: 280px;">
                        @if($template->cover_image)
                            <img
                                src="{{ asset('storage/' . $template->cover_image) }}"
                                alt="ภาพหน้าปก {{ $template->template_name }}"
                                class="block w-full"
                                width="600"
                                height="280"
                                style="width: 100%; height: 280px; min-height: 280px; max-height: 280px; object-fit: cover; object-position: center;">
                        @else
                            <div class="flex h-full flex-col items-center justify-center gap-3 text-center text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm">ไม่มีรูปภาพ</span>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="flex flex-col">
                    <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        ข้อมูล Template
                    </h3>

                    <div class="mt-3 flex h-full flex-col rounded-2xl border border-slate-200 bg-slate-50 p-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">ชื่อ Template</p>
                                <p class="mt-2 break-words text-lg font-semibold text-slate-800">{{ $template->template_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Slug</p>
                                <p class="mt-2 inline-flex max-w-full break-all rounded-lg bg-white px-3 py-1.5 font-mono text-sm text-slate-700 ring-1 ring-slate-200">
                                    {{ $template->template_slug ?: '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-slate-200 pt-6">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">รายละเอียด</p>
                            <div class="mt-3 min-h-32 whitespace-pre-line rounded-xl border border-slate-200 bg-white p-5 text-sm leading-7 text-slate-700">
                                {{ $template->default_description ?: 'ไม่มีรายละเอียด' }}
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Form fields --}}
            <section>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">ฟิลด์ของ Template</h3>
                        <p class="mt-1 text-sm text-slate-500">ตัวอย่างช่องกรอกข้อมูลที่ผู้สมัครจะเห็น</p>
                    </div>
                    <span class="w-fit rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                        {{ $template->formFields->count() }} ฟิลด์
                    </span>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-6">
                    <div class="grid items-stretch gap-5 lg:grid-cols-2">
                        @forelse($template->formFields as $field)
                            @php
                                $fieldOptions = is_array($field->options)
                                    ? $field->options
                                    : (json_decode($field->options ?? '[]', true) ?: []);
                            @endphp

                            <article class="flex min-h-56 h-full flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm {{ $field->is_active ? '' : 'opacity-60' }}">
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="break-words font-semibold text-slate-800">
                                            {{ $field->label }}
                                            @if($field->is_required)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </h4>
                                        @unless($field->is_active)
                                            <p class="mt-1 text-xs text-slate-400">ปิดใช้งาน</p>
                                        @endunless
                                    </div>
                                    <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                        {{ ucfirst($field->field_type) }}
                                    </span>
                                </div>

                                <div class="mt-auto">
                                    @switch($field->field_type)
                                        @case('textarea')
                                            <textarea rows="3" disabled placeholder="{{ $field->placeholder ?: 'กรอกรายละเอียด...' }}"
                                                class="w-full resize-none rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm"></textarea>
                                        @break

                                        @case('email')
                                            <input type="email" disabled placeholder="{{ $field->placeholder ?: 'example@email.com' }}"
                                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm">
                                        @break

                                        @case('phone')
                                            <input type="tel" disabled placeholder="{{ $field->placeholder ?: 'กรอกเบอร์โทรศัพท์' }}"
                                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm">
                                        @break

                                        @case('number')
                                            <input type="number" disabled placeholder="{{ $field->placeholder ?: '0' }}"
                                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm">
                                        @break

                                        @case('date')
                                            <input type="date" disabled
                                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm">
                                        @break

                                        @case('file')
                                            <div class="rounded-xl border-2 border-dashed border-blue-200 bg-blue-50/50 px-5 py-6 text-center">
                                                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5.002 5.002 0 0115.9 6H16a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"/>
                                                    </svg>
                                                </div>
                                                <p class="mt-3 text-sm font-semibold text-slate-700">
                                                    {{ $field->placeholder ?: 'เลือกไฟล์เพื่ออัปโหลด' }}
                                                </p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ $field->accepted_file_types ?: 'รองรับไฟล์ทุกประเภท' }}
                                                    • สูงสุด {{ $field->max_file_size ? $field->max_file_size / 1024 : 10 }} MB
                                                </p>
                                                @if($field->allow_multiple)
                                                    <span class="mt-3 inline-flex rounded-full bg-white px-3 py-1 text-xs font-medium text-blue-700 ring-1 ring-blue-200">
                                                        เลือกได้หลายไฟล์
                                                    </span>
                                                @endif
                                            </div>
                                        @break

                                        @case('select')
                                            <select disabled class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm">
                                                <option>{{ $field->placeholder ?: 'กรุณาเลือก' }}</option>
                                                @foreach($fieldOptions as $option)
                                                    <option>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        @break

                                        @case('radio')
                                            <div class="space-y-2">
                                                @forelse($fieldOptions as $option)
                                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                                        <input type="radio" disabled>
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @empty
                                                    <p class="text-sm text-slate-400">ยังไม่มีตัวเลือก</p>
                                                @endforelse
                                            </div>
                                        @break

                                        @case('checkbox')
                                            <div class="space-y-2">
                                                @forelse($fieldOptions as $option)
                                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                                        <input type="checkbox" disabled>
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @empty
                                                    <p class="text-sm text-slate-400">ยังไม่มีตัวเลือก</p>
                                                @endforelse
                                            </div>
                                        @break

                                        @default
                                            <input type="text" disabled placeholder="{{ $field->placeholder ?: 'กรอกข้อมูล...' }}"
                                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 text-sm">
                                    @endswitch
                                </div>

                                @if($field->help_text)
                                    <p class="mt-3 text-xs leading-5 text-slate-500">{{ $field->help_text }}</p>
                                @endif
                            </article>
                        @empty
                            <div class="col-span-full rounded-xl border-2 border-dashed border-slate-300 bg-white py-14 text-center text-slate-400">
                                ยังไม่มีฟิลด์ใน Template นี้
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        {{-- Footer --}}
        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:justify-end lg:px-10">
            <a href="{{ route('superadmin.templates.index') }}"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-6 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                กลับ
            </a>
            <a href="{{ route('superadmin.templates.form-fields.create', ['template' => $template->id]) }}"
                class="inline-flex h-11 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-6 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                จัดการ Form
            </a>
            <a href="{{ route('superadmin.templates.edit', $template) }}"
                class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                แก้ไขข้อมูล
            </a>
        </div>
    </div>
</div>
@endsection
