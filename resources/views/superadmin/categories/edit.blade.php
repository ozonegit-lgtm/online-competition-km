@extends('layouts.app')

@section('title', 'แก้ไขประเภทการแข่งขัน')

@section('header')
<div>
    <h1 class="text-slate-800 text-xl font-bold">
        แก้ไขประเภทการแข่งขัน
    </h1>

    <p class="mt-1 text-slate-500 text-xs">
        แก้ไขข้อมูล {{ $competitionCategory->category_name }}
    </p>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-7xl">

    <form
        action="{{ route('superadmin.categories.update', $competitionCategory) }}"
        method="POST"
        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

        @csrf
        @method('PUT')

        {{-- ชื่อประเภทการแข่งขัน --}}
        <div>
            <label
                for="category_name"
                class="block text-sm font-medium text-slate-700">
                ประเภทการแข่งขัน
                <span class="text-red-500">*</span>
            </label>

            <input
                id="category_name"
                type="text"
                name="category_name"
                value="{{ old('category_name', $competitionCategory->category_name) }}"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-800 outline-none transition focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100 @error('category_name') border-red-500 @enderror text-sm py-2 h-10 px-3"
                required>

            @error('category_name')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Slug --}}
        <div class="mt-4">
            <label
                for="category_slug"
                class="block text-sm font-medium text-slate-700">
                ชื่อ URL
                <span class="text-red-500">*</span>
            </label>

            <input
                id="category_slug"
                type="text"
                name="category_slug"
                value="{{ old('category_slug', $competitionCategory->category_slug) }}"
                placeholder="เช่น poster-competition"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-800 outline-none transition focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100 @error('category_slug') border-red-500 @enderror text-sm py-2 h-10 px-3"
                required>

            <p class="mt-1 text-slate-400 text-xs">
                ใช้ภาษาอังกฤษ ตัวเลข และเครื่องหมายขีดกลาง
            </p>

            @error('category_slug')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- คำอธิบาย --}}
        <div class="mt-4">
            <label
                for="description"
                class="block text-sm font-medium text-slate-700">
                คำอธิบาย
            </label>

            <textarea
                id="description"
                name="description"
                rows="5"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100 @error('description') border-red-500 @enderror text-sm">{{ old('description', $competitionCategory->description) }}</textarea>

            @error('description')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- สถานะ --}}
        <div class="mt-4">
            <p class="text-sm font-medium text-slate-700">
                สถานะประเภทการแข่งขัน
                <span class="text-red-500">*</span>
            </p>

            <div class="mt-3 flex flex-wrap gap-4">
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="radio"
                        name="is_active"
                        value="1"
                        @checked((string) old(
                            'is_active',
                            (string) $competitionCategory->is_active
                        ) === '1')
                        class="h-5 w-5 accent-green-700">

                    <span class="font-medium text-slate-700">
                        เปิดใช้งาน
                    </span>
                </label>

                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="radio"
                        name="is_active"
                        value="0"
                        @checked((string) old(
                            'is_active',
                            (string) $competitionCategory->is_active
                        ) === '0')
                        class="h-5 w-5 accent-green-700">

                    <span class="font-medium text-slate-700">
                        ปิดใช้งาน
                    </span>
                </label>
            </div>

            @error('is_active')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- ปุ่ม --}}
        <div class="mt-4 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-4">
            <a
                href="{{ route('superadmin.categories.create') }}"
                class="rounded-xl border border-slate-300 font-medium text-slate-600 transition hover:bg-slate-100 inline-flex items-center justify-center text-sm h-9 px-3">
                ยกเลิก
            </a>

            <button
                type="submit"
                class="rounded-xl bg-green-700 font-medium text-white shadow-sm transition hover:bg-green-800 focus:ring-4 focus:ring-green-200 inline-flex items-center justify-center text-sm h-9 px-3">
                บันทึกการแก้ไข
            </button>
        </div>
    </form>
</div>
@endsection