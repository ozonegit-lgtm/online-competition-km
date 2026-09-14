@extends('layouts.app')

@section('title', 'หมวดหมู่ E-Book')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">หมวดหมู่ E-Book</h1>
        <p class="mt-1 text-sm text-slate-500">เพิ่ม แก้ไข และจัดการสถานะหมวดหมู่หนังสือ</p>
    </div>

    <a
        href="{{ route('superadmin.knowledge-page.books.index') }}"
        class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
    >
        กลับไปจัดการ E-Book
    </a>
</div>
@endsection

@section('content')
@php
    $inputClass = 'h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100';
    $textareaClass = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100';
@endphp

<div class="mx-auto w-full max-w-7xl">

    @error('category')
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $message }}
        </div>
    @enderror

    {{-- Add category --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm" data-knowledge-categories>
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">เพิ่มหมวดหมู่</h2>
            <p class="mt-1 text-sm text-slate-500">สร้างหมวดหมู่ใหม่สำหรับใช้กับ E-Book</p>
        </div>

        <form
            method="POST"
            action="{{ route('superadmin.knowledge-page.categories.store') }}"
            class="p-5"
        >
            @csrf

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">ชื่อ <span class="text-red-500">*</span></span>
                    <input
                        name="name"
                        value="{{ old('name') }}"
                        required
                        class="mt-1.5 {{ $inputClass }}"
                    >
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Slug</span>
                    <input
                        name="slug"
                        value="{{ old('slug') }}"
                        placeholder="สร้างอัตโนมัติได้"
                        class="mt-1.5 {{ $inputClass }}"
                    >
                </label>

                <label class="block md:col-span-2">
                    <span class="text-sm font-medium text-slate-700">คำอธิบาย</span>
                    <input
                        name="description"
                        value="{{ old('description') }}"
                        class="mt-1.5 {{ $inputClass }}"
                    >
                </label>
            </div>

            <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="is_active" value="0">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', true))
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    เปิดใช้งาน
                </label>

                <button
                    type="submit"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    เพิ่มหมวดหมู่
                </button>
            </div>
        </form>
    </section>

    {{-- Category list --}}
    <div class="mt-5 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-900">รายการหมวดหมู่</h2>
            <p class="mt-1 text-sm text-slate-500">แก้ไขข้อมูลและสถานะของหมวดหมู่ที่มีอยู่</p>
        </div>

        <span class="text-sm text-slate-500">
            {{ $categories->total() }} รายการ
        </span>
    </div>

    <div class="mt-3 grid gap-4 lg:grid-cols-2">
        @forelse ($categories as $category)
            <article
                class="rounded-xl border border-slate-200 bg-white shadow-sm"
                data-category-id="{{ $category->id }}"
            >
                <form
                    method="POST"
                    action="{{ route('superadmin.knowledge-page.categories.update', $category) }}"
                    class="p-5"
                >
                    @csrf
                    @method('PUT')

                    <div class="mb-4 flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="min-w-0">
                            <h3 class="truncate text-base font-semibold text-slate-900">
                                {{ $category->name }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">
                                E-Book {{ $category->knowledge_items_count }} รายการ
                            </p>
                        </div>

                        <span
                            class="shrink-0 rounded-md px-2.5 py-1 text-xs font-medium
                                {{ $category->is_active
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-slate-100 text-slate-600'
                                }}"
                        >
                            {{ $category->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                        </span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">ชื่อ</span>
                            <input
                                name="name"
                                value="{{ $category->name }}"
                                required
                                class="mt-1.5 {{ $inputClass }}"
                            >
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Slug</span>
                            <input
                                name="slug"
                                value="{{ $category->slug }}"
                                class="mt-1.5 {{ $inputClass }}"
                            >
                        </label>
                    </div>

                    <label class="mt-4 block">
                        <span class="text-sm font-medium text-slate-700">คำอธิบาย</span>
                        <textarea
                            name="description"
                            rows="3"
                            class="mt-1.5 {{ $textareaClass }}"
                        >{{ $category->description }}</textarea>
                    </label>

                    <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked($category->is_active)
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >
                            เปิดใช้งาน
                        </label>

                        <button
                            type="submit"
                            class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700"
                        >
                            บันทึก
                        </button>
                    </div>
                </form>

                <div class="border-t border-slate-100 bg-slate-50 px-5 py-3">
                    <form
                        method="POST"
                        action="{{ route('superadmin.knowledge-page.categories.destroy', $category) }}"
                        class="flex justify-end"
                        onsubmit="return confirm('ยืนยันการลบหมวดหมู่นี้?')"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-white px-3 text-sm font-medium text-red-700 transition hover:bg-red-50"
                        >
                            ลบหมวดหมู่
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center lg:col-span-2">
                <p class="text-sm font-medium text-slate-700">ยังไม่มีหมวดหมู่ E-Book</p>
                <p class="mt-1 text-sm text-slate-500">เพิ่มหมวดหมู่แรกจากแบบฟอร์มด้านบน</p>
            </div>
        @endforelse
    </div>

    @if ($categories->hasPages())
        <div class="mt-5">
            {{ $categories->links() }}
        </div>
    @endif

</div>
@endsection
