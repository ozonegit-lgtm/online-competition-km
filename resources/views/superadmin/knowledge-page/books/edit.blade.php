@extends('layouts.app')

@section('title', 'แก้ไข E-Book')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">แก้ไข E-Book</h1>
        <p class="mt-1 max-w-2xl truncate text-xs text-slate-500 sm:text-sm">{{ $ebook->title }}</p>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row">
        <a
            href="{{ route('superadmin.knowledge-page.books.show', $ebook) }}"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        >
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z"/>
                <circle cx="12" cy="12" r="2.5"/>
            </svg>
            ดูรายละเอียด
        </a>

        <a
            href="{{ route('superadmin.knowledge-page.books.index') }}"
            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
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

    {{-- Current item summary --}}
    <div class="mb-4 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="truncate text-sm font-semibold text-slate-900">{{ $ebook->title }}</h2>

                    @if ($ebook->status === 'published')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            กำลังแสดง
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                            ถูกซ่อน
                        </span>
                    @endif
                </div>

                <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                    <span>หมวดหมู่: {{ $ebook->knowledgeCategory?->name ?: 'ไม่ระบุ' }}</span>
                    <span>ปี: {{ $ebook->publication_year ?: '-' }}</span>
                    <span>เล่ม: {{ $ebook->volume ?: '-' }}</span>
                    <span>ฉบับ: {{ $ebook->issue ?: '-' }}</span>
                </div>
            </div>

            <p class="shrink-0 text-xs text-slate-400">แก้ไขข้อมูลเดิมโดยตรง</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">กรุณาตรวจสอบข้อมูลที่กรอก</p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <form
        method="POST"
        enctype="multipart/form-data"
        action="{{ route('superadmin.knowledge-page.books.update', $ebook) }}"
    >
        @csrf
        @method('PUT')

        @include('superadmin.knowledge-page.books._form')
    </form>

    {{-- Help --}}
    <div class="mt-3 flex items-start gap-2 px-1 text-xs leading-5 text-slate-500">
        <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5m0-8h.01"/>
        </svg>
        <p>การบันทึกหน้านี้จะแก้ไขข้อมูล E-Book เดิม ส่วนสถานะ “แสดง/ซ่อน” ยังคงควบคุมจากหน้าจัดการ E-Book</p>
    </div>

</div>
@endsection
