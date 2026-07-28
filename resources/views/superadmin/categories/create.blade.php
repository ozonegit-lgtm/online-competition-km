@extends('layouts.app')

@section('title', 'เพิ่มประเภทการแข่งขัน')

@section('header')
<div>
    <h1 class="text-2xl font-bold text-slate-800">
        เพิ่มประเภทการแข่งขัน
    </h1>

    <p class="mt-1 text-sm text-slate-500">
        เพิ่มข้อมูลประเภทการแข่งขันใหม่
    </p>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-3xl px-6 py-8">

    <form
        action="{{ route('superadmin.categories.store') }}"
        method="POST"
        class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">

        @csrf

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
                value="{{ old('category_name') }}"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3
                       text-slate-800 outline-none transition
                       focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100
                       @error('category_name') border-red-500 @enderror"
                required>

            @error('category_name')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Slug --}}
        <div class="mt-6">
            <label
                for="category_slug"
                class="block text-sm font-medium text-slate-700">
                ชื่อ URL
            </label>

            <input
                id="category_slug"
                type="text"
                name="category_slug"
                value="{{ old('category_slug') }}"
                placeholder="เช่น poster-competition"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3
                       text-slate-800 outline-none transition
                       focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100
                       @error('category_slug') border-red-500 @enderror">

            <p class="mt-1 text-xs text-slate-400">
                หากไม่กรอก ระบบจะสร้างจากชื่อประเภทการแข่งขัน
            </p>

            @error('category_slug')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- คำอธิบาย --}}
        <div class="mt-6">
            <label
                for="description"
                class="block text-sm font-medium text-slate-700">
                คำอธิบาย
            </label>

            <textarea
                id="description"
                name="description"
                rows="5"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3
                       text-slate-800 outline-none transition
                       focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100
                       @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>

            @error('description')
                <p class="mt-1 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- สถานะ --}}
        <div class="mt-8">
            <p class="text-sm font-medium text-slate-700">
                สถานะประเภทการแข่งขัน
                <span class="text-red-500">*</span>
            </p>

            <div class="mt-3 flex flex-wrap gap-6">
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="radio"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', '1') === '1')
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
                        @checked(old('is_active') === '0')
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
        <div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
            <a
                href="{{ route('superadmin.categories.index') }}"
                class="rounded-xl border border-slate-300 px-5 py-2.5 font-medium text-slate-600
                       transition hover:bg-slate-100">
                ย้อนกลับ
            </a>

            <button
                type="submit"
                class="rounded-xl bg-green-700 px-6 py-2.5 font-medium text-white
                       shadow-sm transition hover:bg-green-800 focus:ring-4 focus:ring-green-200">
                เพิ่มประเภทการแข่งขัน
            </button>
        </div>
    </form>
</div>
    {{-- ข้อความสำเร็จ --}}
    @if(session('success'))
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- รายการประเภทการแข่งขัน --}}
    <div class="mt-10">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    รายการประเภทการแข่งขัน
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    ข้อมูลทั้งหมด {{ $categories->total() }} รายการ
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @forelse($categories as $category)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md">

                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="truncate text-lg font-bold text-slate-800">
                                {{ $category->category_name }}
                            </h3>

                            <p class="mt-1 font-mono text-sm text-slate-400">
                                {{ $category->category_slug }}
                            </p>
                        </div>

                        @if($category->is_active)
                            <span class="shrink-0 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                เปิดใช้งาน
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                ปิดใช้งาน
                            </span>
                        @endif
                    </div>

                    <p class="mt-4 min-h-12 text-sm leading-6 text-slate-600">
                        {{ $category->description ?: 'ไม่มีคำอธิบาย' }}
                    </p>

                    <div class="mt-5 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                        <a
                            href="{{ route('superadmin.categories.edit', $category) }}"
                            class="rounded-lg bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-100">
                            แก้ไข
                        </a>

                        <form
                            action="{{ route('superadmin.categories.destroy', $category) }}"
                            method="POST"
                            onsubmit="return confirm('ยืนยันการลบประเภทการแข่งขันนี้หรือไม่?')">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100">
                                ลบ
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center md:col-span-2">
                    <p class="font-medium text-slate-600">
                        ยังไม่มีข้อมูลประเภทการแข่งขัน
                    </p>
                </div>
            @endforelse
        </div>

        @if($categories->hasPages())
            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection