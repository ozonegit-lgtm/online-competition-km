@extends('layouts.app')

@section('title', 'เพิ่ม E-Book')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">เพิ่ม E-Book</h1>
        <p class="mt-1 text-xs text-slate-500 sm:text-sm">เพิ่มข้อมูลหนังสือ ปก เอกสาร และรายละเอียดสำหรับหน้า E-Book KM</p>
    </div>

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
@endsection

@section('content')
<div class="mx-auto w-full max-w-5xl">

    @if ($categories->isEmpty())
        <div class="mb-4 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 9v4m0 4h.01"/>
                    <path d="M10.3 4.7 3.5 17a2 2 0 0 0 1.7 3h13.6a2 2 0 0 0 1.7-3L13.7 4.7a2 2 0 0 0-3.4 0Z"/>
                </svg>
            </span>

            <div class="min-w-0">
                <p class="text-sm font-semibold text-amber-900">ยังไม่มีหมวดหมู่ E-Book</p>
                <p class="mt-0.5 text-xs leading-5 text-amber-800">กรุณาสร้างหมวดหมู่ก่อนเพิ่ม E-Book เพื่อให้สามารถจัดหมวดหมู่รายการได้อย่างถูกต้อง</p>
            </div>
        </div>
    @endif

    <form
        method="POST"
        enctype="multipart/form-data"
        action="{{ route('superadmin.knowledge-page.books.store') }}"
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
    >
        @csrf

        <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">ข้อมูล E-Book</h2>
                <p class="mt-0.5 text-xs text-slate-500">กรอกข้อมูลที่จำเป็นและแนบไฟล์ที่ต้องการเผยแพร่</p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                เริ่มต้นในสถานะซ่อน
            </div>
        </div>

        <div class="p-4 sm:p-5">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold">กรุณาตรวจสอบข้อมูลที่กรอก</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('superadmin.knowledge-page.books._form')
        </div>
    </form>

    <div class="mt-3 flex items-start gap-2 px-1 text-xs leading-5 text-slate-500">
        <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5m0-8h.01"/>
        </svg>
        <p>E-Book ที่เพิ่มใหม่จะยังไม่แสดงบนหน้า Public จนกว่าจะเปลี่ยนสถานะเป็น “แสดง” จากหน้าจัดการ E-Book</p>
    </div>

</div>
@endsection
