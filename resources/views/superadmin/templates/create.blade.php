@extends('layouts.app')

@section('title', 'สร้าง Template')

@section('header')
<div>
    <h1 class="text-3xl font-bold text-slate-800">
        สร้าง Template
    </h1>

    <p class="mt-2 text-sm text-slate-500">
        เพิ่มแม่แบบการแข่งขันใหม่เข้าสู่ระบบ
    </p>
</div>
@endsection

@section('content')
    <div class="mx-auto max-w-6xl">
        <form
            action="{{ route('superadmin.templates.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-8">

            @csrf

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                {{-- Header --}}
                <div class="border-b border-slate-200 bg-slate-50 pl-12 pr-8 py-6">
                        <h2 class="ml-4 text-xl font-semibold tracking-tight text-slate-800">
                            ข้อมูล Template
                        </h2>

                        <p class="ml-4 mt-1.5 text-sm text-slate-500">
                            กรอกข้อมูลพื้นฐานสำหรับสร้าง Template
                        </p>
                </div>
                {{-- Body --}}
                <div class="p-8" style="display:flex; flex-direction:column; row-gap:40px;">

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
                            class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-slate-700 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">

                        @error('template_name')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                    </div>
                    {{-- Slug --}}
                    <div class="space-y-3">
                        <label
                            for="template_slug"
                            class="block text-sm font-medium text-slate-700">

                            Template URL (Slug)

                        </label>
                        <input
                            id="template_slug"
                            type="text"
                            name="template_slug"
                            value="{{ old('template_slug') }}"
                            placeholder="poster-competition"
                            class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-slate-700 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <p class="text-xs text-slate-500">
                            เช่น poster-competition
                        </p>
                    </div>
                    {{-- Description --}}
                   <div class="space-y-3">
                        <label
                            for="default_description"
                            class="block text-sm font-medium text-slate-700">
                            คำอธิบาย
                        </label>
                        <textarea
                            id="default_description"
                            name="default_description"
                            rows="6"
                            placeholder="อธิบายรายละเอียดของ Template"
                            class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-700 placeholder:text-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('default_description') }}</textarea>
                        @error('default_description')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Cover Image --}}
                    <div class="space-y-3">

                        <label
                            class="mb-2 block text-sm font-medium text-slate-700">

                            ภาพหน้าปก

                        </label>

                        <label
                            for="cover_image"
                            class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white px-6 py-6 transition hover:border-blue-400 hover:bg-slate-50">
                            <div class="flex items-center gap-5">
                                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg"
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
                                    <p id="file-name"  class="font-medium text-slate-700">
                                        ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์
                                    </p>
                                    <p id="file-info" class="mt-1 text-sm text-slate-500">
                                        รองรับไฟล์ .jpg .jpeg .png .webp ขนาดไม่เกิน 2 MB
                                    </p>
                                </div>
                            </div>
                            <span
                                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">

                                เลือกไฟล์

                            </span>

                            <input
                                id="cover_image"
                                type="file"
                                name="cover_image"
                                accept="image/*"
                                class="hidden">

                        </label>

                        @error('cover_image')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                    </div>

                    {{-- Status --}}
                    <div class="space-y-3">

                        <label
                            class="mb-3 block text-sm font-medium text-slate-700">

                            สถานะ

                        </label>

                        <div class="flex gap-8">

                            <label class="flex cursor-pointer items-center gap-3">

                                <input
                                    type="radio"
                                    name="is_active"
                                    value="1"
                                    @checked(old('is_active',1))
                                    class="h-5 w-5 accent-blue-600">

                                <span class="text-slate-700">
                                    ใช้งาน
                                </span>

                            </label>

                            <label class="flex cursor-pointer items-center gap-3">

                                <input
                                    type="radio"
                                    name="is_active"
                                    value="0"
                                    @checked(old('is_active')==='0')
                                    class="h-5 w-5 accent-blue-600">

                                <span class="text-slate-700">
                                    ปิดใช้งาน
                                </span>

                            </label>

                        </div>

                        @error('is_active')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                    </div>

                </div>

                {{-- Footer --}}
                <div class="border-t border-slate-200 bg-slate-50 px-8 py-4">

                    <div class="flex justify-end">

                        <div class="mr-6 flex items-center gap-3">

                            <a
                                href="{{ route('superadmin.templates.index') }}"
                                class="inline-flex h-10 min-w-[110px] items-center justify-center rounded-lg border border-slate-300 bg-white px-5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                                ยกเลิก
                            </a>

                            <button
                                type="submit"
                                class="inline-flex h-10 min-w-[150px] items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"/>

                                </svg>

                                บันทึก Template

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

        if (this.files.length > 0) {

            fileName.textContent = this.files[0].name;

            fileInfo.textContent =
                (this.files[0].size / 1024 / 1024).toFixed(2) + ' MB';

        }

    });

});
</script>
@endsection
