@extends('layouts.app')

@section('title', 'สร้าง Template')

@section('header')
<div>
    <h1 class="text-slate-800 text-xl font-bold">
        สร้าง Template
    </h1>

    <p class="mt-2 text-slate-500 text-xs">
        สร้างแม่แบบการแข่งขันและกำหนดแบบฟอร์มรับผลงาน
    </p>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-7xl">

    {{-- แสดงข้อผิดพลาด --}}
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-4">
            <p class="font-semibold text-red-700">
                กรุณาตรวจสอบข้อมูลอีกครั้ง
            </p>

            <ul class="mt-2 list-inside list-disc text-sm text-red-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ขั้นตอนการสร้าง Template --}}
    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="grid sm:grid-cols-2">

            {{-- ขั้นตอนที่ 1 --}}
            <div class="flex items-center gap-4 border-b border-slate-200 bg-blue-50 px-4 py-4 sm:border-b-0 sm:border-r">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 font-bold text-white">
                    1
                </span>

                <div>
                    <p class="font-semibold text-blue-700">
                        ข้อมูล Template
                    </p>

                    <p class="mt-1 text-slate-500 text-xs">
                        กำลังกรอกข้อมูลพื้นฐาน
                    </p>
                </div>
            </div>

            {{-- ขั้นตอนที่ 2 --}}
            <div class="flex items-center gap-4 px-4 py-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 font-bold text-slate-500">
                    2
                </span>

                <div>
                    <p class="font-semibold text-slate-600">
                        สร้างแบบฟอร์ม
                    </p>

                    <p class="mt-1 text-slate-500 text-xs">
                        สร้างช่องกรอกข้อมูลในขั้นตอนถัดไป
                    </p>
                </div>
            </div>
        </div>
    </div>

    <form
        action="{{ route('superadmin.templates.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-4">

        @csrf

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            {{-- Header --}}
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-4">
                <h2 class="tracking-tight text-slate-800 text-base font-semibold">
                    ข้อมูล Template
                </h2>

                <p class="mt-1.5 text-slate-500 text-xs">
                    กรอกข้อมูลพื้นฐานของแม่แบบการแข่งขัน
                </p>
            </div>

            {{-- Body --}}
            <div class="space-y-4 p-4">

                {{-- Template Name --}}
                <div class="space-y-3">
                    <label
                        for="template_name"
                        class="block text-sm font-medium text-slate-700">

                        ชื่อ Template
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="template_name"
                        type="text"
                        name="template_name"
                        value="{{ old('template_name') }}"
                        placeholder="เช่น การแข่งขันออกแบบโปสเตอร์"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-white text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 @error('template_name') border-red-500 @enderror text-sm py-2 h-10 px-3">

                    <p class="text-slate-500 text-xs">
                        ชื่อสำหรับให้ Competition Admin เลือกใช้งาน
                    </p>

                    @error('template_name')
                        <p class="text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Slug --}}
                <div class="space-y-3">
                    <label
                        for="template_slug"
                        class="block text-sm font-medium text-slate-700">
                        Template URL
                    </label>

                    <input
                        id="template_slug"
                        type="text"
                        name="template_slug"
                        value="{{ old('template_slug') }}"
                        placeholder="เช่น poster-competition"
                        class="w-full rounded-xl border border-slate-200 bg-white text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 @error('template_slug') border-red-500 @enderror text-sm py-2 h-10 px-3">

                    <p class="text-slate-500 text-xs">
                        หากไม่กรอก ระบบจะสร้างจากชื่อ Template ให้อัตโนมัติ
                    </p>

                    @error('template_slug')
                        <p class="text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="space-y-3">
                    <label
                        for="default_description"
                        class="block text-sm font-medium text-slate-700">
                        คำอธิบาย Template
                    </label>

                    <textarea
                        id="default_description"
                        name="default_description"
                        rows="6"
                        placeholder="อธิบายว่า Template นี้ใช้สำหรับการแข่งขันประเภทใด"
                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 @error('default_description') border-red-500 @enderror text-sm">{{ old('default_description') }}</textarea>

                    @error('default_description')
                        <p class="text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Cover Image --}}
                <div class="space-y-3">
                    <label
                        for="cover_image"
                        class="block text-sm font-medium text-slate-700">
                        ภาพหน้าปก
                    </label>

                    <label
                        for="cover_image"
                        class="flex cursor-pointer flex-col gap-4 rounded-xl border-2 border-dashed border-slate-200 bg-white px-4 py-4 transition hover:border-blue-400 hover:bg-blue-50 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-8 w-8"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v8"/>
                                </svg>
                            </div>

                            <div>
                                <p
                                    id="file-name"
                                    class="font-medium text-slate-700">
                                    คลิกเพื่อเลือกภาพหน้าปก
                                </p>

                                <p
                                    id="file-info"
                                    class="mt-1 text-slate-500 text-xs">
                                    รองรับ JPG, JPEG, PNG และ WEBP ขนาดไม่เกิน 10 MB
                                </p>
                            </div>
                        </div>

                        <span class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                            เลือกไฟล์
                        </span>

                        <input
                            id="cover_image"
                            type="file"
                            name="cover_image"
                            accept=".jpg,.jpeg,.png,.webp"
                            class="hidden">
                    </label>

                    @error('cover_image')
                        <p class="text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="space-y-3">
                    <p class="block text-sm font-medium text-slate-700">
                        สถานะ Template
                        <span class="text-red-500">*</span>
                    </p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-green-300 hover:bg-green-50">
                            <input
                                type="radio"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', '1') == '1')
                                class="mt-1 h-5 w-5 accent-green-600">

                            <span>
                                <span class="block font-medium text-slate-700">
                                    เปิดใช้งาน
                                </span>

                                <span class="mt-1 block text-xs text-slate-500">
                                    Competition Admin สามารถเลือก Template นี้ได้
                                </span>
                            </span>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-slate-400 hover:bg-slate-50">
                            <input
                                type="radio"
                                name="is_active"
                                value="0"
                                @checked(old('is_active') === '0')
                                class="mt-1 h-5 w-5 accent-slate-600">

                            <span>
                                <span class="block font-medium text-slate-700">
                                    ปิดใช้งาน
                                </span>

                                <span class="mt-1 block text-xs text-slate-500">
                                    เก็บ Template ไว้แต่ยังไม่เปิดให้เลือกใช้งาน
                                </span>
                            </span>
                        </label>
                    </div>

                    @error('is_active')
                        <p class="text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Footer --}}
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-4">
                <div class="flex flex-wrap items-center justify-between gap-4">

                    <p class="text-slate-500 text-xs">
                        ขั้นตอนถัดไป: สร้างช่องกรอกข้อมูลพร้อมดูตัวอย่างแบบฟอร์ม
                    </p>

                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('superadmin.templates.index') }}"
                            class="inline-flex min-w-[110px] items-center justify-center rounded-lg border border-slate-300 bg-white text-sm font-medium text-slate-700 transition hover:bg-slate-100 h-9 px-3">
                            ยกเลิก
                        </a>

                        <button
                            type="submit"
                            class="inline-flex min-w-[215px] items-center justify-center gap-2 rounded-lg bg-blue-600 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 h-9 px-3">

                            บันทึกและสร้างแบบฟอร์ม

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('cover_image');
    const fileName = document.getElementById('file-name');
    const fileInfo = document.getElementById('file-info');

    input.addEventListener('change', function () {
        if (this.files.length === 0) {
            fileName.textContent = 'คลิกเพื่อเลือกภาพหน้าปก';
            fileInfo.textContent =
                'รองรับ JPG, JPEG, PNG และ WEBP ขนาดไม่เกิน 2 MB';

            return;
        }

        const file = this.files[0];
        const fileSize = (file.size / 1024 / 1024).toFixed(2);

        fileName.textContent = file.name;
        fileInfo.textContent = fileSize + ' MB';
    });
});
</script>
@endsection