@extends('layouts.app')

@section('title', 'เพิ่ม E-Book')

@section('header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 ring-1 ring-blue-100">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5V5.5Z"/>
                    <path d="M4 20.5A2.5 2.5 0 0 1 6.5 18H20"/>
                    <path d="M12 7v6M9 10h6"/>
                </svg>
            </span>
            E-Book Management
        </div>

        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">เพิ่ม E-Book</h1>

        <p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-500">
            เพิ่มข้อมูลหนังสือ ปก เอกสาร และรายละเอียดที่จะแสดงบนหน้า E-Book KM
        </p>
    </div>

    <a
        href="{{ route('superadmin.knowledge-page.books.index') }}"
        class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:text-blue-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-blue-100"
    >
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m15 18-6-6 6-6"/>
        </svg>
        กลับไปรายการ E-Book
    </a>
</div>
@endsection

@section('content')
<div class="mx-auto w-full max-w-6xl">

    {{-- Status notice --}}
    <div class="mb-5 overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-700 p-5 text-white shadow-lg shadow-blue-900/10 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex gap-4">
                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 backdrop-blur">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 4h14v16H5z"/>
                        <path d="M8 8h8M8 12h8M8 16h5"/>
                    </svg>
                </span>

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-100">Create new E-Book</p>
                    <h2 class="mt-1 text-lg font-bold">สร้างรายการใหม่</h2>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-blue-100">
                        กรอกข้อมูลให้ครบถ้วนแล้วบันทึก ระบบจะสร้าง E-Book ใหม่ในสถานะซ่อนก่อนเสมอ
                    </p>
                </div>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-xs font-semibold text-white backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-amber-300"></span>
                เริ่มต้นเป็นสถานะซ่อน
            </div>
        </div>
    </div>

    {{-- Category warning --}}
    @if ($categories->isEmpty())
        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <div class="flex gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 9v4m0 4h.01"/>
                        <path d="M10.3 4.7 3.5 17a2 2 0 0 0 1.7 3h13.6a2 2 0 0 0 1.7-3L13.7 4.7a2 2 0 0 0-3.4 0Z"/>
                    </svg>
                </span>

                <div>
                    <p class="text-sm font-bold text-amber-900">ยังไม่มีหมวดหมู่ E-Book</p>
                    <p class="mt-1 text-sm leading-6 text-amber-800">
                        กรุณาสร้างหมวดหมู่ E-Book ก่อนเพิ่มหนังสือ เพื่อให้สามารถจัดหมวดหมู่รายการได้อย่างถูกต้อง
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Form container --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 4h14v16H5z"/>
                        <path d="M8 8h8M8 12h8M8 16h5"/>
                    </svg>
                </span>

                <div>
                    <h2 class="font-bold text-slate-900">ข้อมูล E-Book</h2>
                    <p class="text-xs text-slate-500">กรอกรายละเอียดของ E-Book และแนบไฟล์ที่เกี่ยวข้อง</p>
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-6">
            <form
                method="POST"
                enctype="multipart/form-data"
                action="{{ route('superadmin.knowledge-page.books.store') }}"
            >
                @csrf

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
                <p class="font-bold">หลังจากบันทึก</p>
                <p class="text-blue-700">
                    E-Book จะยังไม่แสดงบนหน้า Public จนกว่าจะเปลี่ยนสถานะเป็น
                    <span class="font-semibold">แสดง</span> จากหน้าจัดการ E-Book
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
