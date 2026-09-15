@extends('layouts.app')

@section('title', 'จัดการ E-Book')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">จัดการ E-Book</h1>
        <p class="mt-1 text-xs text-slate-500 sm:text-sm">จัดการรายการ E-Book ลำดับการแสดง และสถานะเผยแพร่</p>
    </div>
    <a href="{{ route('superadmin.knowledge-page.books.create') }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"></path></svg>
        เพิ่ม E-Book
    </a>
</div>
@endsection

@section('content')
<div class="mx-auto w-full max-w-7xl">
    <form method="GET" action="{{ route('superadmin.knowledge-page.books.index') }}" class="mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="relative min-w-0 flex-1">
                <label for="ebook-search" class="sr-only">ค้นหา E-Book</label>
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input id="ebook-search" type="search" name="q" value="{{ request('q') }}" placeholder="ค้นหาชื่อ E-Book..." class="h-9 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex h-9 flex-1 items-center justify-center rounded-lg bg-slate-800 px-4 text-sm font-medium text-white transition hover:bg-slate-900 sm:flex-none">ค้นหา</button>
                @if (request('q'))
                    <a href="{{ route('superadmin.knowledge-page.books.index') }}" class="inline-flex h-9 flex-1 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-50 sm:flex-none">ล้าง</a>
                @endif
            </div>
        </div>
    </form>

    <div class="mb-3 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">รายการ E-Book</h2>
            <p class="mt-0.5 text-xs text-slate-500">ทั้งหมด {{ $ebooks->total() }} รายการ</p>
        </div>
    </div>

    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:block" data-ebook-admin-list>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-left">
                <thead class="bg-slate-50">
                    <tr class="border-b border-slate-200 text-xs font-semibold text-slate-500">
                        <th class="w-20 px-4 py-3">ปก</th>
                        <th class="px-4 py-3">E-Book</th>
                        <th class="w-32 px-4 py-3">ปี / เล่ม / ฉบับ</th>
                        <th class="w-28 px-4 py-3 text-center">สถานะ</th>
                        <th class="w-40 px-4 py-3">ลำดับ</th>
                        <th class="w-56 px-4 py-3 text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($ebooks as $ebook)
                        <tr class="align-middle transition hover:bg-slate-50/70" data-ebook-id="{{ $ebook->id }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('superadmin.knowledge-page.books.show', $ebook) }}" class="block w-fit">
                                    @if ($ebook->cover_image_url)
                                        <img src="{{ $ebook->cover_image_url }}" alt="ปก {{ $ebook->title }}" class="h-16 w-12 rounded-md border border-slate-200 bg-white object-cover">
                                    @else
                                        <div class="flex h-16 w-12 items-center justify-center rounded-md border border-dashed border-slate-300 bg-slate-50 px-1 text-center text-[8px] font-semibold text-slate-400">NO COVER</div>
                                    @endif
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('superadmin.knowledge-page.books.show', $ebook) }}" class="block max-w-xl truncate text-sm font-semibold text-slate-900 transition hover:text-blue-700">{{ $ebook->title }}</a>
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $ebook->knowledgeCategory?->name ?: 'ไม่ระบุหมวดหมู่' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-1 text-xs text-slate-600">
                                    <div><span class="text-slate-400">ปี:</span> {{ $ebook->publication_year ?: '-' }}</div>
                                    <div><span class="text-slate-400">เล่ม:</span> {{ $ebook->volume ?: '-' }} <span class="mx-1 text-slate-300">•</span> <span class="text-slate-400">ฉบับ:</span> {{ $ebook->issue ?: '-' }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($ebook->status === 'published')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>แสดง</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>ซ่อน</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('superadmin.knowledge-page.books.reorder') }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="items[0][id]" value="{{ $ebook->id }}">
                                    <input type="number" min="0" name="items[0][sort_order]" value="{{ $ebook->sort_order }}" class="h-8 w-16 rounded-md border border-slate-300 bg-white px-2 text-center text-xs text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                    <button type="submit" class="inline-flex h-8 items-center justify-center rounded-md border border-slate-300 bg-white px-2.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">บันทึก</button>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('superadmin.knowledge-page.books.edit', $ebook) }}" class="inline-flex h-8 items-center justify-center rounded-md border border-slate-300 bg-white px-2.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">แก้ไข</a>
                                    @if ($ebook->status === 'published')
                                        <form method="POST" action="{{ route('superadmin.knowledge-page.books.hide', $ebook) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 items-center justify-center rounded-md border border-amber-200 bg-amber-50 px-2.5 text-xs font-medium text-amber-700 transition hover:bg-amber-100">ซ่อน</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('superadmin.knowledge-page.books.publish', $ebook) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex h-8 items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 px-2.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100">แสดง</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('superadmin.knowledge-page.books.destroy', $ebook) }}" onsubmit="return confirm('ยืนยันการลบ E-Book นี้และไฟล์ที่เกี่ยวข้อง?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="confirm_delete" value="yes">
                                        <button type="submit" class="inline-flex h-8 items-center justify-center rounded-md border border-red-200 bg-red-50 px-2.5 text-xs font-medium text-red-700 transition hover:bg-red-100">ลบ</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <p class="text-sm font-semibold text-slate-700">ยังไม่มี E-Book</p>
                                <p class="mt-1 text-xs text-slate-500">เริ่มต้นโดยเพิ่ม E-Book รายการแรก</p>
                                <a href="{{ route('superadmin.knowledge-page.books.create') }}" class="mt-4 inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-700">เพิ่ม E-Book</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-3 lg:hidden" data-ebook-admin-list>
        @forelse ($ebooks as $ebook)
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm" data-ebook-id="{{ $ebook->id }}">
                <div class="flex gap-3">
                    <a href="{{ route('superadmin.knowledge-page.books.show', $ebook) }}" class="shrink-0">
                        @if ($ebook->cover_image_url)
                            <img src="{{ $ebook->cover_image_url }}" alt="ปก {{ $ebook->title }}" class="h-24 w-16 rounded-md border border-slate-200 bg-white object-cover">
                        @else
                            <div class="flex h-24 w-16 items-center justify-center rounded-md border border-dashed border-slate-300 bg-slate-50 px-1 text-center text-[9px] font-semibold text-slate-400">NO COVER</div>
                        @endif
                    </a>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('superadmin.knowledge-page.books.show', $ebook) }}" class="block line-clamp-2 text-sm font-semibold leading-5 text-slate-900 hover:text-blue-700">{{ $ebook->title }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $ebook->knowledgeCategory?->name ?: 'ไม่ระบุหมวดหมู่' }}</p>
                            </div>
                            @if ($ebook->status === 'published')
                                <span class="shrink-0 rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700">แสดง</span>
                            @else
                                <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600">ซ่อน</span>
                            @endif
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-500">
                            <span>ปี {{ $ebook->publication_year ?: '-' }}</span>
                            <span>เล่ม {{ $ebook->volume ?: '-' }}</span>
                            <span>ฉบับ {{ $ebook->issue ?: '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 border-t border-slate-100 pt-3">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <form method="POST" action="{{ route('superadmin.knowledge-page.books.reorder') }}" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="items[0][id]" value="{{ $ebook->id }}">
                            <span class="text-xs font-medium text-slate-500">ลำดับ</span>
                            <input type="number" min="0" name="items[0][sort_order]" value="{{ $ebook->sort_order }}" class="h-8 w-16 rounded-md border border-slate-300 bg-white px-2 text-center text-xs text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            <button type="submit" class="inline-flex h-8 items-center justify-center rounded-md border border-slate-300 bg-white px-2.5 text-xs font-medium text-slate-600 hover:bg-slate-50">บันทึก</button>
                        </form>
                        <div class="flex flex-wrap gap-1.5">
                            <a href="{{ route('superadmin.knowledge-page.books.edit', $ebook) }}" class="inline-flex h-8 items-center justify-center rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 hover:bg-slate-50">แก้ไข</a>
                            @if ($ebook->status === 'published')
                                <form method="POST" action="{{ route('superadmin.knowledge-page.books.hide', $ebook) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-8 items-center justify-center rounded-md border border-amber-200 bg-amber-50 px-3 text-xs font-medium text-amber-700 hover:bg-amber-100">ซ่อน</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('superadmin.knowledge-page.books.publish', $ebook) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex h-8 items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 px-3 text-xs font-medium text-emerald-700 hover:bg-emerald-100">แสดง</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('superadmin.knowledge-page.books.destroy', $ebook) }}" onsubmit="return confirm('ยืนยันการลบ E-Book นี้และไฟล์ที่เกี่ยวข้อง?')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirm_delete" value="yes">
                                <button type="submit" class="inline-flex h-8 items-center justify-center rounded-md border border-red-200 bg-red-50 px-3 text-xs font-medium text-red-700 hover:bg-red-100">ลบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center">
                <p class="text-sm font-semibold text-slate-700">ยังไม่มี E-Book</p>
                <p class="mt-1 text-xs text-slate-500">เริ่มต้นโดยเพิ่ม E-Book รายการแรก</p>
                <a href="{{ route('superadmin.knowledge-page.books.create') }}" class="mt-4 inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700">เพิ่ม E-Book</a>
            </div>
        @endforelse
    </div>

    @if ($ebooks->hasPages())
        <div class="mt-4">
            {{ $ebooks->links() }}
        </div>
    @endif
</div>
@endsection
