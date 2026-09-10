@extends('layouts.app')

@section('title', 'แก้ไของค์ความรู้')

@section('header')
    <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
            <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path d="M12 20h9"/>
                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
            </svg>
        </div>

        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">
                แก้ไของค์ความรู้
            </h1>

            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5">
                    @if ($knowledgeItem->submission_id)
                        <svg
                            class="h-3.5 w-3.5 text-emerald-600"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path d="M8 21h8"/>
                            <path d="M12 17v4"/>
                            <path d="M7 4h10v5a5 5 0 0 1-10 0V4Z"/>
                            <path d="M7 6H4a1 1 0 0 0-1 1v1a4 4 0 0 0 4 4"/>
                            <path d="M17 6h3a1 1 0 0 1 1 1v1a4 4 0 0 1-4 4"/>
                        </svg>
                    @else
                        <svg
                            class="h-3.5 w-3.5 text-emerald-600"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                        </svg>
                    @endif

                    แหล่งที่มา:
                    <span class="font-semibold text-slate-700">
                        {{ $knowledgeItem->submission_id ? 'การแข่งขัน' : 'Manual KM' }}
                    </span>
                </span>

                <span class="hidden h-3 w-px bg-slate-300 sm:block"></span>

                <span class="inline-flex items-center gap-1.5">
                    <svg
                        class="h-3.5 w-3.5 text-slate-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                    </svg>

                    เจ้าของ:
                    <span class="font-semibold text-slate-700">
                        {{ $knowledgeItem->creator?->username ?? 'ไม่มีเจ้าของ' }}
                    </span>
                </span>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 lg:px-8">

        {{-- Breadcrumb / Meta --}}
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
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

                <a
                    href="{{ route('superadmin.km.show', $knowledgeItem) }}"
                    class="max-w-[220px] truncate font-medium text-slate-500 transition hover:text-emerald-700 sm:max-w-sm"
                    title="{{ $knowledgeItem->title }}"
                >
                    {{ $knowledgeItem->title }}
                </a>

                <span>/</span>

                <span class="font-medium text-slate-700">
                    แก้ไข
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold
                        {{ $knowledgeItem->status === 'published'
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : ($knowledgeItem->status === 'hidden'
                                ? 'border-slate-200 bg-slate-100 text-slate-600'
                                : 'border-amber-200 bg-amber-50 text-amber-700') }}"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full
                            {{ $knowledgeItem->status === 'published'
                                ? 'bg-emerald-500'
                                : ($knowledgeItem->status === 'hidden'
                                    ? 'bg-slate-400'
                                    : 'bg-amber-500') }}"
                    ></span>

                    {{ $knowledgeItem->status === 'published'
                        ? 'เผยแพร่แล้ว'
                        : ($knowledgeItem->status === 'hidden'
                            ? 'ซ่อนอยู่'
                            : 'ฉบับร่าง') }}
                </span>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('superadmin.km.update', $knowledgeItem) }}"
            enctype="multipart/form-data"
            class="space-y-5"
        >
            @csrf
            @method('PUT')

            @include('superadmin.km._form')

            {{-- Actions --}}
            <div
                class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-2 text-xs leading-5 text-slate-400">
                    <svg
                        class="mt-0.5 h-4 w-4 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 8v4l3 2"/>
                    </svg>

                    <span>
                        การแก้ไขจะบันทึกทับข้อมูลเดิมของรายการนี้
                    </span>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <a
                        href="{{ route('superadmin.km.show', $knowledgeItem) }}"
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
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
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

                        บันทึกการแก้ไข
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
