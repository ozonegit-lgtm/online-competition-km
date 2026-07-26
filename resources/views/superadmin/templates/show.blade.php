@extends('layouts.app')

@section('title', 'รายละเอียด Template')

@section('header')
<div>
    <h1 class="text-3xl font-bold text-slate-800">
        รายละเอียด Template
    </h1>

    <p class="mt-2 text-sm text-slate-500">
        แสดงรายละเอียดของ Template การแข่งขัน
    </p>
</div>
@endsection

@section('content')
{{-- {{dd($competitionTemplate->cover_image);}} --}}
<div class="mx-auto max-w-6xl">

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white" style="padding: 24px 40px;">

            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3v18M4 7h16M4 7a2 2 0 012-2h12a2 2 0 012 2m-16 0v10a2 2 0 002 2h12a2 2 0 002-2V7"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-slate-800">
                        {{ $competitionTemplate->template_name }}
                    </h2>
                    <p class="mt-0.5 text-sm text-slate-500">
                        รายละเอียดทั้งหมดของ Template
                    </p>
                </div>
            </div>

            @if($competitionTemplate->is_active)
                <span class="inline-flex items-center gap-2 rounded-full bg-green-50 px-4 py-2 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-200">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    เปิดใช้งาน
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-4 py-2 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-200">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    ปิดใช้งาน
                </span>
            @endif

        </div>

        {{-- Body --}}
        <div style="display:grid; grid-template-columns: 3fr 2fr; column-gap:40px; row-gap:40px; padding:40px;">

            {{-- Left: Info --}}
            <div style="display:flex; flex-direction:column; row-gap:32px;">

                <div style="display:grid; grid-template-columns: 1fr 1fr; column-gap:32px;">

                    {{-- Template Name --}}
                    <div>
                        <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            ชื่อ Template
                        </label>

                        <p class="mt-2 text-lg font-semibold text-slate-800">
                            {{ $competitionTemplate->template_name }}
                        </p>
                    </div>

                    {{-- Slug --}}
                    <div>
                        <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-4 4a4 4 0 01-5.656-5.656l1.5-1.5M10.172 13.828a4 4 0 010-5.656l4-4a4 4 0 015.656 5.656l-1.5 1.5"/>
                            </svg>
                            Slug
                        </label>

                        <p class="mt-2 inline-flex rounded-lg bg-slate-100 px-3 py-1.5 font-mono text-sm text-slate-700">
                            {{ $competitionTemplate->template_slug ?: '-' }}
                        </p>
                    </div>

                </div>

                {{-- Description --}}
                <div>
                    <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6"/>
                        </svg>
                        รายละเอียด
                    </label>

                    <div class="mt-3 whitespace-pre-line rounded-xl border border-slate-100 bg-slate-50 p-5 leading-7 text-slate-700">
                        {{ $competitionTemplate->default_description ?: '-' }}
                    </div>
                </div>

            </div>

            {{-- Right: Cover Image --}}
            <div>

                <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    ภาพหน้าปก
                </label>

                <div class="mt-3">
                    @if($competitionTemplate->cover_image)

                        <img
                            src="{{ asset('storage/'.$competitionTemplate->cover_image) }}"
                            class="w-full rounded-xl border border-slate-200 object-cover shadow-sm"
                            style="max-height: 320px;">

                    @else

                        <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm">ไม่มีรูปภาพ</span>
                        </div>

                    @endif
                </div>

            </div>

        </div>

        {{-- Footer --}}
        <div class="border-t border-slate-200 bg-slate-50" style="padding: 20px 40px;">

            <div class="flex justify-end gap-3">

                <a
                    href="{{ route('superadmin.templates.index') }}"
                    class="inline-flex h-11 min-w-[120px] items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-6 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    กลับ
                </a>

                <a
                    href="{{ route('superadmin.templates.edit', $competitionTemplate) }}"
                    class="inline-flex h-11 min-w-[140px] items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    แก้ไขข้อมูล
                </a>

            </div>

        </div>

    </div>

</div>

@endsection
