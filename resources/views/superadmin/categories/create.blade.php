@extends('layouts.app')

@section('title', 'จัดการประเภทการแข่งขัน')

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row
                sm:items-end sm:justify-between">

        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                จัดการประเภทการแข่งขัน
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                เพิ่ม แก้ไข และจัดการประเภทการแข่งขันภายในระบบ
            </p>
        </div>

        <span class="inline-flex w-fit items-center rounded-full
                     border border-blue-200 bg-blue-50 px-3 py-1.5
                     text-sm font-semibold text-blue-700">
            {{ number_format($categories->total()) }}
            ประเภท
        </span>
    </div>
@endsection

@section('content')
    <div class="mx-auto max-w-7xl">

        <div class="grid items-start gap-6 lg:grid-cols-[400px_minmax(0,1fr)]">

            {{-- แบบฟอร์มเพิ่มประเภท --}}
            <section class="overflow-hidden rounded-2xl border
                            border-slate-200 bg-white shadow-sm">

                {{-- หัวการ์ด --}}
                <div class="border-b border-slate-200 px-6 py-5">

                    <div class="flex items-center gap-3">

                        <div class="flex h-11 w-11 shrink-0 items-center
                                    justify-center rounded-xl bg-emerald-100
                                    text-emerald-700">

                            <svg
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M12 5v14"/>
                                <path d="M5 12h14"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                เพิ่มประเภทใหม่
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                กรอกข้อมูลประเภทการแข่งขัน
                            </p>
                        </div>
                    </div>
                </div>

                <form
                    action="{{ route('superadmin.categories.store') }}"
                    method="POST"
                >
                    @csrf

                    <div class="space-y-5 p-6">

                        {{-- ชื่อประเภท --}}
                        <div>
                            <label
                                for="category_name"
                                class="block text-sm font-semibold
                                       text-slate-700"
                            >
                                ชื่อประเภทการแข่งขัน

                                <span class="text-red-500">
                                    *
                                </span>
                            </label>

                            <input
                                id="category_name"
                                type="text"
                                name="category_name"
                                value="{{ old('category_name') }}"
                                placeholder="เช่น การประกวดโปสเตอร์"
                                required
                                class="mt-2 w-full rounded-xl border
                                       bg-white px-4 py-3 text-sm
                                       text-slate-800 outline-none transition
                                       focus:border-blue-500 focus:ring-4
                                       focus:ring-blue-100
                                       @error('category_name')
                                           border-red-400
                                       @else
                                           border-slate-300
                                       @enderror"
                            >

                            @error('category_name')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div>
                            <label
                                for="category_slug"
                                class="block text-sm font-semibold
                                       text-slate-700"
                            >
                                ชื่อ URL
                            </label>

                            <div class="mt-2 flex overflow-hidden rounded-xl
                                        border bg-white transition
                                        focus-within:border-blue-500
                                        focus-within:ring-4
                                        focus-within:ring-blue-100
                                        @error('category_slug')
                                            border-red-400
                                        @else
                                            border-slate-300
                                        @enderror">

                                <span class="flex items-center border-r
                                             border-slate-200 bg-slate-50
                                             px-3 text-sm text-slate-400">
                                    /
                                </span>

                                <input
                                    id="category_slug"
                                    type="text"
                                    name="category_slug"
                                    value="{{ old('category_slug') }}"
                                    placeholder="poster-competition"
                                    class="min-w-0 flex-1 border-0 px-3
                                           py-3 text-sm text-slate-800
                                           outline-none"
                                >
                            </div>

                            <p class="mt-2 text-xs leading-5 text-slate-400">
                                หากไม่กรอก ระบบจะสร้างจากชื่อประเภทให้อัตโนมัติ
                            </p>

                            @error('category_slug')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- คำอธิบาย --}}
                        <div>
                            <label
                                for="description"
                                class="block text-sm font-semibold
                                       text-slate-700"
                            >
                                คำอธิบาย
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="อธิบายรายละเอียดของประเภทการแข่งขัน"
                                class="mt-2 w-full resize-none rounded-xl
                                       border bg-white px-4 py-3 text-sm
                                       text-slate-800 outline-none transition
                                       focus:border-blue-500 focus:ring-4
                                       focus:ring-blue-100
                                       @error('description')
                                           border-red-400
                                       @else
                                           border-slate-300
                                       @enderror"
                            >{{ old('description') }}</textarea>

                            @error('description')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- สถานะ --}}
                        <fieldset>
                            <legend class="text-sm font-semibold
                                           text-slate-700">
                                สถานะการใช้งาน

                                <span class="text-red-500">
                                    *
                                </span>
                            </legend>

                            <div class="mt-3 grid gap-3 sm:grid-cols-2">

                                <label class="flex cursor-pointer items-center
                                              gap-3 rounded-xl border
                                              border-slate-200 bg-slate-50
                                              p-3 transition
                                              hover:border-emerald-300
                                              hover:bg-emerald-50">

                                    <input
                                        type="radio"
                                        name="is_active"
                                        value="1"
                                        @checked(
                                            old('is_active', '1') === '1'
                                        )
                                        class="h-5 w-5 border-slate-300
                                               text-emerald-600
                                               focus:ring-emerald-500"
                                    >

                                    <div>
                                        <p class="text-sm font-semibold
                                                  text-slate-700">
                                            เปิดใช้งาน
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            สามารถเลือกใช้งานได้
                                        </p>
                                    </div>
                                </label>

                                <label class="flex cursor-pointer items-center
                                              gap-3 rounded-xl border
                                              border-slate-200 bg-slate-50
                                              p-3 transition
                                              hover:border-slate-300
                                              hover:bg-slate-100">

                                    <input
                                        type="radio"
                                        name="is_active"
                                        value="0"
                                        @checked(
                                            old('is_active') === '0'
                                        )
                                        class="h-5 w-5 border-slate-300
                                               text-slate-600
                                               focus:ring-slate-500"
                                    >

                                    <div>
                                        <p class="text-sm font-semibold
                                                  text-slate-700">
                                            ปิดใช้งาน
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            ซ่อนจากการเลือกใช้งาน
                                        </p>
                                    </div>
                                </label>
                            </div>

                            @error('is_active')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </fieldset>
                    </div>

                    {{-- ปุ่มบันทึก --}}
                    <div class="flex flex-col-reverse gap-3 border-t
                                border-slate-200 bg-slate-50 px-6 py-5
                                sm:flex-row sm:items-center
                                sm:justify-between">

                        <p class="text-xs text-slate-500">
                            <span class="text-red-500">*</span>
                            จำเป็นต้องกรอก
                        </p>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2
                                rounded-xl bg-green-600 px-5 py-3
                                text-sm font-semibold text-white shadow-sm
                                transition hover:bg-green-700
                                focus:outline-none focus:ring-4
                                focus:ring-green-200"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M12 5v14"/>
                                <path d="M5 12h14"/>
                            </svg>

                            เพิ่มประเภทการแข่งขัน
                        </button>
                    </div>
                </form>
            </section>

            {{-- รายการประเภทการแข่งขัน --}}
            <section class="overflow-hidden rounded-2xl border
                            border-slate-200 bg-white shadow-sm">

                {{-- หัวรายการ --}}
                <div class="flex flex-col gap-3 border-b
                            border-slate-200 px-6 py-5 sm:flex-row
                            sm:items-center sm:justify-between">

                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            รายการประเภทการแข่งขัน
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            จัดการประเภทการแข่งขันทั้งหมดภายในระบบ
                        </p>
                    </div>

                    <span class="inline-flex w-fit rounded-full
                                 bg-slate-100 px-3 py-1.5 text-xs
                                 font-semibold text-slate-600">
                        {{ number_format($categories->total()) }}
                        รายการ
                    </span>
                </div>

                @if ($categories->isEmpty())

                    {{-- Empty state --}}
                    <div class="px-6 py-16 text-center">

                        <div class="mx-auto flex h-16 w-16
                                    items-center justify-center rounded-2xl
                                    bg-slate-100 text-slate-400">

                            <svg
                                class="h-8 w-8"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h7"/>
                                <path d="M16 19h6"/>
                                <path d="M19 16v6"/>
                            </svg>
                        </div>

                        <h3 class="mt-4 font-bold text-slate-700">
                            ยังไม่มีประเภทการแข่งขัน
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            เพิ่มประเภทแรกจากแบบฟอร์มด้านซ้าย
                        </p>
                    </div>

                @else

                    {{-- รายการ --}}
                    <div class="divide-y divide-slate-100">

                        @foreach ($categories as $category)
                            <article class="p-5 transition
                                            hover:bg-slate-50">

                                <div class="flex flex-col gap-4
                                            sm:flex-row sm:items-start
                                            sm:justify-between">

                                    <div class="flex min-w-0 gap-4">

                                        {{-- Icon --}}
                                        <div class="flex h-11 w-11 shrink-0
                                                    items-center justify-center
                                                    rounded-xl bg-blue-100
                                                    text-blue-700">

                                            <svg
                                                class="h-5 w-5"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h7"/>
                                                <path d="M16 19h6"/>
                                                <path d="M19 16v6"/>
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap
                                                        items-center gap-2">

                                                <h3 class="font-bold
                                                           text-slate-900">
                                                    {{ $category->category_name }}
                                                </h3>

                                                @if ($category->is_active)
                                                    <span class="rounded-full
                                                                 border
                                                                 border-emerald-200
                                                                 bg-emerald-50
                                                                 px-2.5 py-1
                                                                 text-xs
                                                                 font-semibold
                                                                 text-emerald-700">
                                                        เปิดใช้งาน
                                                    </span>
                                                @else
                                                    <span class="rounded-full
                                                                 border
                                                                 border-slate-200
                                                                 bg-slate-100
                                                                 px-2.5 py-1
                                                                 text-xs
                                                                 font-semibold
                                                                 text-slate-600">
                                                        ปิดใช้งาน
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="mt-1 truncate
                                                      font-mono text-xs
                                                      text-slate-400">
                                                /{{ $category->category_slug }}
                                            </p>

                                            <p class="mt-3 text-sm leading-6
                                                      text-slate-600">
                                                {{ $category->description
                                                    ?: 'ไม่มีคำอธิบาย' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- ปุ่มจัดการ --}}
                                    <div class="flex shrink-0 items-center
                                                gap-2 sm:justify-end">

                                        <a
                                            href="{{ route(
                                                'superadmin.categories.edit',
                                                $category
                                            ) }}"
                                            class="inline-flex items-center
                                                   justify-center gap-1.5
                                                   rounded-lg border
                                                   border-amber-200
                                                   bg-amber-50 px-3 py-2
                                                   text-sm font-semibold
                                                   text-amber-700 transition
                                                   hover:bg-amber-100"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.1 2.1 0 013 3L8 18l-4 1 1-4z"/>
                                            </svg>

                                            แก้ไข
                                        </a>

                                        <form
                                            action="{{ route(
                                                'superadmin.categories.destroy',
                                                $category
                                            ) }}"
                                            method="POST"
                                            onsubmit="return confirm(
                                                'ยืนยันการลบประเภทการแข่งขันนี้หรือไม่?'
                                            )"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="inline-flex items-center
                                                       justify-center gap-1.5
                                                       rounded-lg border
                                                       border-red-200
                                                       bg-red-50 px-3 py-2
                                                       text-sm font-semibold
                                                       text-red-700 transition
                                                       hover:bg-red-100"
                                            >
                                                <svg
                                                    class="h-4 w-4"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path d="M3 6h18"/>
                                                    <path d="M8 6V4h8v2"/>
                                                    <path d="M19 6l-1 14H6L5 6"/>
                                                </svg>

                                                ลบ
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($categories->hasPages())
                        <div class="border-t border-slate-200 px-6 py-5">
                            {{ $categories->links() }}
                        </div>
                    @endif
                @endif
            </section>
        </div>
    </div>
@endsection