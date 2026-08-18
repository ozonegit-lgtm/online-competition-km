@extends('layouts.app')

@section('title', 'แก้ไข Template')

@section('header')
<div>
    <h1 class="text-2xl font-bold text-slate-800">
        แก้ไข Template
    </h2>
    <p class="mt-2 text-sm text-slate-500">
        แก้ไขข้อมูลแม่แบบการแข่งขัน
    </p>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-5xl px-6 py-8">
            <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid sm:grid-cols-2">
            {{-- ขั้นตอนที่ 1 --}}
                <a
                    href="{{ route('superadmin.templates.edit', $template) }}"
                    class="flex items-center gap-4 border-b border-slate-200 bg-blue-50 px-6 py-5 transition hover:bg-blue-100 sm:border-b-0 sm:border-r"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 font-bold text-white">
                        1
                    </span>

                    <div>
                        <p class="font-semibold text-blue-700">
                            ข้อมูล Template
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            แก้ไขข้อมูลพื้นฐานแล้ว
                        </p>
                    </div>
                </a>

                {{-- ขั้นตอนที่ 2 --}}
                <a
                    href="{{ route('superadmin.templates.form-fields.edit', $template) }}"
                    class="flex items-center gap-4 px-6 py-5 transition hover:bg-slate-50"
                >
                    
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 font-bold text-slate-500">
                        2
                    </span>

                    <div>
                        <p class="font-semibold text-slate-600">
                            แก้ไขแบบฟอร์ม
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            แก้ไขช่องกรอกข้อมูลที่มีอยู่ หรือเพิ่มใหม่
                        </p>
                    </div>
                </a>
            </div>
        </div>
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
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <div>
                    <h1 class="text-xl font-bold text-slate-800">
                        {{ $template->template_name }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        แก้ไขรายละเอียดของ Template
                    </p>
                </div>
            </div>

            {{-- Active status --}}
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition has-[:not(:checked)]:border-slate-200 has-[:not(:checked)]:bg-slate-100 has-[:not(:checked)]:text-slate-500">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="peer sr-only"
                    {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                >

                <span class="h-2 w-2 rounded-full bg-emerald-500 peer-[:not(:checked)]:bg-slate-400"></span>

                <span>เปิดใช้งาน</span>
            </label>
        </div>

        <div class="border-t border-slate-100"></div>

        {{-- Main body --}}
        <div class="grid grid-cols-1 gap-8 px-8 py-8 lg:grid-cols-3">

            {{-- Left: Template information --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- Template name + slug --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    {{-- Template name --}}
                    <div>
                        <label
                            for="template_name"
                            class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M4 6h16M4 12h16M4 18h7" />
                            </svg>

                            ชื่อ Template
                        </label>

                        <input
                            id="template_name"
                            type="text"
                            name="template_name"
                            value="{{ old('template_name', $template->template_name) }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-semibold text-slate-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 @error('template_name') border-red-400 @enderror"
                        >

                        @error('template_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Slug --}}
                    <div>
                        <label
                            for="template_slug"
                            class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5M10.172 13.828a4 4 0 010-5.656l3-3a4 4 0 015.656 5.656l-1.5 1.5" />
                            </svg>

                            Slug
                        </label>

                        <input
                            id="template_slug"
                            type="text"
                            name="template_slug"
                            value="{{ old('template_slug', $template->template_slug) }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm text-slate-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 @error('template_slug') border-red-400 @enderror"
                        >

                        @error('template_slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Description --}}
                <div>
                    <label
                        for="default_description"
                        class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M4 6h16M4 10h16M4 14h10M4 18h6" />
                        </svg>

                        รายละเอียด
                    </label>

                    <textarea
                        id="default_description"
                        name="default_description"
                        rows="7"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100 @error('default_description') border-red-400 @enderror"
                    >{{ old('default_description', $template->default_description) }}</textarea>

                    @error('default_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Right: Cover image --}}
            <div>
                <label class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2-2v12a2 2 0 002 2z" />
                    </svg>

                    ภาพหน้าปก
                </label>

                <div class="flex aspect-square w-full flex-col items-center justify-center overflow-hidden rounded-xl border border-dashed border-slate-200 bg-slate-50">

                    {{-- Existing image / selected image preview --}}
                    <img
                        id="cover_image_preview"
                        src="{{ $template->cover_image ? asset('storage/' . $template->cover_image) : '' }}"
                        alt="ตัวอย่างภาพหน้าปก"
                        class="{{ $template->cover_image ? '' : 'hidden' }} h-full w-full object-cover"
                    >

                    {{-- Placeholder --}}
                    <div
                        id="cover_image_placeholder"
                        class="{{ $template->cover_image ? 'hidden' : '' }} flex flex-col items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-10 w-10 text-slate-300"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.5">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>

                        <p class="mt-3 text-sm text-slate-400">
                            ไม่มีรูปภาพ
                        </p>
                    </div>
                </div>

                {{-- Upload button --}}
                <label
                    for="cover_image"
                    class="mt-3 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-blue-300 hover:text-blue-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M4 12v6a2 2 0 002 2h12a2 2 0 002-2v-6M16 6l-4-4m0 0L8 6m4-4v13" />
                    </svg>

                    เปลี่ยนภาพหน้าปก

                    <input
                        id="cover_image"
                        type="file"
                        name="cover_image"
                        accept="image/*"
                        class="hidden"
                    >
                </label>

                <p class="mt-2 text-center text-xs text-slate-400">
                    JPG, PNG ไม่เกิน 10MB
                </p>

                @error('cover_image')
                    <p class="mt-1 text-center text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Form Builder --}}
        <div class="border-t border-slate-100 px-8 py-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        แบบฟอร์มการแข่งขัน
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        จัดการช่องกรอกข้อมูลของ Template นี้ผ่าน Form Builder
                    </p>
                </div>

                <a
                    href="{{ route('superadmin.templates.form-fields.edit', $template) }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 text-sm font-semibold text-blue-700 ring-1 ring-blue-200 transition hover:bg-blue-100"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>

                    แก้ไข Form Builder
                </a>

            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-8 py-5">

            <a
                href="{{ route('superadmin.templates.index') }}"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 font-medium text-slate-700 transition hover:bg-slate-100"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-4 w-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>

                กลับ
            </a>

            <button
                type="submit"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 font-semibold text-white shadow-sm transition hover:bg-blue-700"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-4 w-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="2">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M5 13l4 4L19 7" />
                </svg>

                บันทึกข้อมูล
            </button>

        </div>

    </form>
</div>

{{-- Cover image preview --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('cover_image');
        const preview = document.getElementById('cover_image_preview');
        const placeholder = document.getElementById('cover_image_placeholder');

        if (!input || !preview || !placeholder) {
            return;
        }

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

            if (file.size > 10 * 1024 * 1024) {
                alert('รูปภาพต้องมีขนาดไม่เกิน 10MB');
                this.value = '';
                return;
            }

            const objectUrl = URL.createObjectURL(file);

            preview.src = objectUrl;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');

            preview.onload = function () {
                URL.revokeObjectURL(objectUrl);
            };
        });
    });
</script>

@endsection