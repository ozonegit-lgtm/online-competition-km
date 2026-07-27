@extends('layouts.app')

@section('title','จัดการ Templates')

@section('header')
<div class="flex items-start justify-between">

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-800">
            จัดการ Templates
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            จัดการแม่แบบการแข่งขันทั้งหมดของระบบ
        </p>
    </div>

    <a href="{{ route('superadmin.templates.create') }}"
        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">

        <svg xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">

            <path stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"/>

        </svg>

        สร้าง Template

    </a>

</div>
@endsection

@section('content')

<div class="space-y-5">

    {{-- Search --}}
    <div
        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex flex-col gap-4">

            <div class="relative">

                <div
                class="pointer-events-none absolute inset-y-0 flex items-center"
                style="left: 24px;">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-slate-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-4-4m1-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>

                <input
                type="text"
                placeholder="ค้นหา Template..."
                class="w-full rounded-xl
                    border border-slate-300
                    bg-slate-50
                    py-3
                    pr-4
                    text-sm
                    text-slate-700
                    placeholder:text-slate-400
                    outline-none
                    focus:outline-none
                    focus:border-blue-500
                    focus:ring-2
                    focus:ring-blue-100"
                style="padding-left:56px;">
            </div>
            <div class="flex items-center">
                <span
                    class="rounded-full bg-blue-100 px-4 py-1.5 text-sm font-medium text-blue-700">
                    ทั้งหมด {{ $competitionTemplates->count() }} รายการ
                </span>
            </div>
        </div>
    </div>
    @forelse($competitionTemplates as $template)
<div
    class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-blue-400 hover:shadow-md">
    <div class="flex">
        {{-- Accent Bar --}}
        <div
            class="w-1 bg-transparent transition-all duration-300 group-hover:bg-blue-600">
        </div>
        <div class="flex-1 p-6">

            <div class="flex items-start justify-between gap-6">

                {{-- Left --}}
                <a
                    href="{{ route('superadmin.templates.show',$template) }}"
                    class="flex flex-1 gap-5">

                    {{-- Icon --}}
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/>

                        </svg>

                    </div>

                    {{-- Content --}}
                    <div class="min-w-0 flex-1">

                        <div class="flex items-center gap-2">

                            <h2
                                class="truncate text-lg font-semibold text-slate-800">

                                {{ $template->template_name }}

                            </h2>

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-slate-400 transition group-hover:translate-x-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"/>

                            </svg>

                        </div>

                        {{-- Description --}}
                        <p
                            class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">

                            {{ $template->default_description ?: 'ไม่มีรายละเอียด' }}

                        </p>

                        {{-- Meta --}}
                        <div
                            class="mt-5 flex flex-wrap items-center gap-3">

                            @if($template->is_active)

                                <span
                                    class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">

                                    Active

                                </span>

                            @else

                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                    Inactive
                                </span>

                            @endif

                            <span
                                class="text-xs text-slate-400">

                                แก้ไขล่าสุด
                                {{ $template->updated_at?->format('d/m/Y') }}

                            </span>

                        </div>

                    </div>

                </a>

                {{-- Right --}}
                <div
                    class="flex shrink-0 items-center gap-3 opacity-0 transition duration-300 group-hover:opacity-100">

                    <a
                        href="{{ route('superadmin.templates.show',$template) }}"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-100">

                        ดู

                    </a>


                    <form
                        action="{{ route('superadmin.templates.destroy',$template) }}"
                        method="POST"
                        onsubmit="return confirm('ต้องการลบ Template นี้หรือไม่?')">

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="rounded-xl border border-red-200 px-4 py-2 text-sm text-red-600 transition hover:bg-red-50">

                            ลบ

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>
@empty
<div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white py-20 text-center">

    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-blue-600">

        <svg xmlns="http://www.w3.org/2000/svg"
            class="h-8 w-8"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">

            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/>

        </svg>

    </div>

    <h3 class="mt-6 text-xl font-semibold text-slate-800">
        ยังไม่มี Template
    </h3>

    <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-500">
        เริ่มต้นด้วยการสร้าง Template แรกของคุณ
        เพื่อใช้เป็นแม่แบบสำหรับการแข่งขัน
    </p>

    <a
        href="{{ route('superadmin.templates.create') }}"
        class="mt-8 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">

        <svg xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">

            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"/>

        </svg>

        สร้าง Template

    </a>

</div>

@endforelse

{{-- Pagination --}}
@if(method_exists($competitionTemplates,'links'))

<div class="mt-8 flex items-center justify-between border-t border-slate-200 pt-6">

    <div class="text-sm text-slate-500">

        แสดงทั้งหมด

        <span class="font-semibold text-slate-700">

            {{ $competitionTemplates->count() }}

        </span>

        รายการ

    </div>

    <div>

        {{ $competitionTemplates->onEachSide(1)->links() }}

    </div>

</div>
</div>
@endif

@endsection