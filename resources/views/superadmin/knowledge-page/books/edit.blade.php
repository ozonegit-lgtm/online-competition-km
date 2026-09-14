@extends('layouts.app')

@section('title', 'แก้ไข E-Book')

@section('header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div class="min-w-0">
        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 ring-1 ring-blue-100">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5V5.5Z"/>
                    <path d="M4 20.5A2.5 2.5 0 0 1 6.5 18H20"/>
                    <path d="m14.5 8.5 1-1a2.1 2.1 0 0 1 3 3l-1 1-5 5-3 1 1-3 4-4Z"/>
                </svg>
            </span>
            E-Book Management
        </div>

        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">แก้ไข E-Book</h1>

        <p class="mt-1.5 max-w-2xl truncate text-sm leading-6 text-slate-500">
            {{ $ebook->title }}
        </p>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row">
        <a
            href="{{ route('superadmin.knowledge-page.books.show', $ebook) }}"
            class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:text-blue-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-blue-100"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/>
                <circle cx="12" cy="12" r="2.5"/>
            </svg>
            ดูรายละเอียด
        </a>

        <a
            href="{{ route('superadmin.knowledge-page.books.index') }}"
            class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            กลับไปรายการ
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="mx-auto w-full max-w-6xl">

    {{-- Current item overview --}}
    <div class="mb-5 overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-600 via-blue-600 to-cyan-600 p-5 text-white shadow-lg shadow-indigo-900/10 sm:p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 gap-4">
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 backdrop-blur">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 4h14v16H5z"/>
                        <path d="M8 8h8M8 12h8M8 16h5"/>
                    </svg>
                </span>

                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-100">Editing E-Book</p>
                    <h2 class="mt-1 truncate text-lg font-bold sm:text-xl">{{ $ebook->title }}</h2>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-blue-100">
                        แก้ไขรายละเอียด ไฟล์ รูปปก หมวดหมู่ และข้อมูลการเผยแพร่ของ E-Book รายการนี้
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-xs font-semibold text-white backdrop-blur">
                    <span class="h-2 w-2 rounded-full {{ $ebook->status === 'published' ? 'bg-emerald-300' : 'bg-amber-300' }}"></span>
                    {{ $ebook->status === 'published' ? 'กำลังแสดง' : 'ถูกซ่อน' }}
                </span>

                @if ($ebook->knowledgeCategory?->name)
                    <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-2 text-xs font-semibold text-white backdrop-blur">
                        {{ $ebook->knowledgeCategory->name }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Form --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z"/>
                    </svg>
                </span>

                <div>
                    <h2 class="font-bold text-slate-900">แก้ไขข้อมูล E-Book</h2>
                    <p class="text-xs text-slate-500">ปรับปรุงข้อมูลที่ต้องการแล้วกดบันทึกการเปลี่ยนแปลง</p>
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-6">
            <form
                method="POST"
                enctype="multipart/form-data"
                action="{{ route('superadmin.knowledge-page.books.update', $ebook) }}"
            >
                @csrf
                @method('PUT')

                @include('superadmin.knowledge-page.books._form')
            </form>
        </div>
    </section>

    {{-- Help --}}
    <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3">
        <div class="flex gap-3">
            <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 11v5m0-8h.01"/>
                </svg>
            </span>

            <div class="text-xs leading-5 text-blue-800">
                <p class="font-bold">การแก้ไขข้อมูล</p>
                <p class="text-blue-700">
                    การบันทึกหน้านี้จะแก้ไขข้อมูล E-Book เดิมโดยตรง
                    ส่วนสถานะ <span class="font-semibold">แสดง/ซ่อน</span> ยังสามารถควบคุมได้จากหน้ารายการ E-Book
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
