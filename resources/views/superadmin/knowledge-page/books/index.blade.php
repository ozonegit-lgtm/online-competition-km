@extends('layouts.app')

@section('title', 'จัดการ E-Book')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">จัดการ E-Book</h1>
        <p class="mt-1 text-sm text-slate-500">
            จัดการข้อมูล ลำดับ และสถานะการเผยแพร่ E-Book
        </p>
    </div>

    <a
        href="{{ route('superadmin.knowledge-page.books.create') }}"
        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
    >
        เพิ่ม E-Book
    </a>
</div>
@endsection

@section('content')
<div class="mx-auto w-full max-w-6xl">

    {{-- Search toolbar --}}
    <form
        method="GET"
        action="{{ route('superadmin.knowledge-page.books.index') }}"
        class="mb-5 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center"
    >
        <div class="min-w-0 flex-1">
            <label for="ebook-search" class="sr-only">ค้นหา E-Book</label>
            <input
                id="ebook-search"
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="ค้นหาชื่อหรือรายละเอียด E-Book..."
                class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
            >
        </div>

        <div class="flex gap-2">
            <button
                type="submit"
                class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-medium text-white transition hover:bg-slate-800"
            >
                ค้นหา
            </button>

            @if (request('q'))
                <a
                    href="{{ route('superadmin.knowledge-page.books.index') }}"
                    class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    ล้าง
                </a>
            @endif
        </div>
    </form>

    {{-- List header --}}
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-900">รายการ E-Book</h2>
            <p class="mt-1 text-sm text-slate-500">
                ทั้งหมด {{ $ebooks->total() }} รายการ
            </p>
        </div>
    </div>

    {{-- E-Book list --}}
    <section class="space-y-3" data-ebook-admin-list>
        @forelse ($ebooks as $ebook)
            <article
                class="rounded-xl border border-slate-200 bg-white shadow-sm"
                data-ebook-id="{{ $ebook->id }}"
            >
                <div class="grid gap-4 p-4 sm:p-5 lg:grid-cols-[88px_minmax(0,1fr)_220px] lg:items-center">

                    {{-- Cover --}}
                    <div class="flex justify-center lg:justify-start">
                        @if ($ebook->cover_image_url)
                            <a
                                href="{{ route('superadmin.knowledge-page.books.show', $ebook) }}"
                                class="block"
                            >
                                <img
                                    src="{{ $ebook->cover_image_url }}"
                                    alt="ปก {{ $ebook->title }}"
                                    class="h-28 w-20 rounded-lg border border-slate-200 bg-white object-cover"
                                >
                            </a>
                        @else
                            <div class="flex h-28 w-20 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-2 text-center text-[10px] font-medium text-slate-400">
                                NO COVER
                            </div>
                        @endif
                    </div>

                    {{-- Main info --}}
                    <div class="min-w-0">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <a
                                    href="{{ route('superadmin.knowledge-page.books.show', $ebook) }}"
                                    class="block truncate text-base font-semibold text-slate-900 transition hover:text-blue-700"
                                >
                                    {{ $ebook->title }}
                                </a>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $ebook->knowledgeCategory?->name ?: 'ไม่ระบุหมวดหมู่' }}
                                </p>
                            </div>

                            <span
                                class="inline-flex w-fit shrink-0 items-center rounded-md px-2.5 py-1 text-xs font-medium
                                    {{ $ebook->status === 'published'
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-100 text-slate-600'
                                    }}"
                            >
                                {{ $ebook->status === 'published' ? 'กำลังแสดง' : 'ถูกซ่อน' }}
                            </span>
                        </div>

                        <dl class="mt-4 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-3">
                            <div>
                                <dt class="text-xs text-slate-400">ปีเผยแพร่</dt>
                                <dd class="mt-0.5 font-medium text-slate-700">
                                    {{ $ebook->publication_year ?: '-' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs text-slate-400">เล่ม</dt>
                                <dd class="mt-0.5 font-medium text-slate-700">
                                    {{ $ebook->volume ?: '-' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs text-slate-400">ฉบับ</dt>
                                <dd class="mt-0.5 font-medium text-slate-700">
                                    {{ $ebook->issue ?: '-' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Controls --}}
                    <div class="border-t border-slate-100 pt-4 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                        <div>
                            <p class="text-xs font-medium text-slate-500">ลำดับการแสดง</p>

                            <form
                                method="POST"
                                action="{{ route('superadmin.knowledge-page.books.reorder') }}"
                                class="mt-2 flex items-center gap-2"
                            >
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="items[0][id]" value="{{ $ebook->id }}">

                                <input
                                    type="number"
                                    min="0"
                                    name="items[0][sort_order]"
                                    value="{{ $ebook->sort_order }}"
                                    class="h-9 w-20 rounded-lg border border-slate-300 bg-white px-2 text-center text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                >

                                <button
                                    type="submit"
                                    class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    บันทึก
                                </button>
                            </form>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a
                                href="{{ route('superadmin.knowledge-page.books.edit', $ebook) }}"
                                class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-50"
                            >
                                แก้ไข
                            </a>

                            @if ($ebook->status === 'published')
                                <form
                                    method="POST"
                                    action="{{ route('superadmin.knowledge-page.books.hide', $ebook) }}"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-amber-200 bg-white px-3 text-xs font-medium text-amber-700 transition hover:bg-amber-50"
                                    >
                                        ซ่อน
                                    </button>
                                </form>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route('superadmin.knowledge-page.books.publish', $ebook) }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex h-9 w-full items-center justify-center rounded-lg bg-emerald-600 px-3 text-xs font-medium text-white transition hover:bg-emerald-700"
                                    >
                                        แสดง
                                    </button>
                                </form>
                            @endif
                        </div>

                        <form
                            method="POST"
                            action="{{ route('superadmin.knowledge-page.books.destroy', $ebook) }}"
                            class="mt-2"
                            onsubmit="return confirm('ยืนยันการลบ E-Book นี้และไฟล์ที่เกี่ยวข้อง?')"
                        >
                            @csrf
                            @method('DELETE')

                            <input type="hidden" name="confirm_delete" value="yes">

                            <button
                                type="submit"
                                class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-red-200 bg-white px-3 text-xs font-medium text-red-700 transition hover:bg-red-50"
                            >
                                ลบ E-Book
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <p class="text-sm font-medium text-slate-700">ยังไม่มี E-Book</p>
                <p class="mt-1 text-sm text-slate-500">
                    เริ่มต้นโดยเพิ่ม E-Book รายการแรก
                </p>

                <a
                    href="{{ route('superadmin.knowledge-page.books.create') }}"
                    class="mt-4 inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-700"
                >
                    เพิ่ม E-Book
                </a>
            </div>
        @endforelse
    </section>

    @if ($ebooks->hasPages())
        <div class="mt-5">
            {{ $ebooks->links() }}
        </div>
    @endif
</div>
@endsection
