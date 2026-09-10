@extends('layouts.app')

@section('title', 'เพิ่มองค์ความรู้')

@section('header')
    <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
            <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                <path d="M12 7v6M9 10h6"/>
            </svg>
        </div>

        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">
                เพิ่มองค์ความรู้
            </h1>

            <p class="mt-1 text-xs leading-5 text-slate-500">
                เพิ่มบทความ เอกสาร หรือข้อมูลความรู้เพื่อเผยแพร่ในคลังองค์ความรู้
            </p>
        </div>
    </div>
@endsection

@section('content')
    <div class="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 lg:px-8">

        {{-- Breadcrumb / Back --}}
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a
                    href="{{ route('superadmin.km.index') }}"
                    class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-emerald-700"
                >
                    <svg
                        class="h-3.5 w-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path d="m15 18-6-6 6-6"/>
                    </svg>

                    จัดการองค์ความรู้
                </a>

                <span>/</span>

                <span class="font-medium text-slate-600">
                    เพิ่มรายการใหม่
                </span>
            </div>

            <div
                class="inline-flex w-fit items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-medium text-amber-700"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                บันทึกเป็นฉบับร่างก่อนเผยแพร่
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('superadmin.km.store') }}"
            enctype="multipart/form-data"
            class="space-y-5"
        >
            @csrf

            @include('superadmin.km._form')

            {{-- Actions --}}
            <div
                class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs leading-5 text-slate-400">
                    ตรวจสอบข้อมูลให้ครบถ้วนก่อนบันทึก
                </p>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <a
                        href="{{ route('superadmin.km.index') }}"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200/60"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path d="m15 18-6-6 6-6"/>
                        </svg>

                        ยกเลิก
                    </a>

                    <button
                        type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path d="M5 5h11l3 3v11H5z"/>
                            <path d="M8 5v5h8V5M8 15h8"/>
                        </svg>

                        บันทึกองค์ความรู้
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
