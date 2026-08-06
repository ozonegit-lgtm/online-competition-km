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
        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">

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
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex flex-col gap-4">

            <div class="relative">

                <div class="pointer-events-none absolute inset-y-0 flex items-center" style="left: 24px;">
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
                class="w-full rounded-xl border border-slate-300 bg-slate-50 py-3 pr-4 text-sm text-slate-600 placeholder:text-slate-400 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                style="padding-left:56px;">
            </div>

            <div class="flex items-center">
                <span class="rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 text-sm font-medium text-blue-700">
                    ทั้งหมด {{ $competitionTemplates->count() }} รายการ
                </span>
            </div>

        </div>
    </div>

    <div class="grid items-stretch gap-6 sm:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">

@forelse($competitionTemplates as $template)

<div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">

    <div class="relative shrink-0">
        @if($template->cover_image)
            <img src="{{ asset('storage/'.$template->cover_image) }}" class="h-56 w-full object-cover">
        @else
            <div class="flex h-56 w-full items-center justify-center bg-slate-100 text-slate-400">
                ไม่มีรูปภาพ
            </div>
        @endif

        <div class="absolute right-4 top-4">
            @if($template->is_active)
                <span class="rounded-full bg-green-500 px-3 py-1 text-xs font-semibold text-white">เปิดใช้งาน</span>
            @else
                <span class="rounded-full bg-rose-500 px-3 py-1 text-xs font-semibold text-white">ปิดใช้งาน</span>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col p-6">

        <h2 class="truncate text-xl font-bold text-slate-800">{{ $template->template_name }}</h2>

        <div class="mt-2">
            <span class="rounded-lg bg-slate-100 px-3 py-1 font-mono text-xs text-slate-600">
                {{ $template->template_slug }}
            </span>
        </div>

        <p class="mt-4 flex-1 line-clamp-3 text-sm leading-6 text-slate-500">
            {{ $template->default_description ?: 'ไม่มีรายละเอียด' }}
        </p>

        <div class="mt-6 space-y-3 border-t border-slate-100 pt-4">

            <a href="{{ route('superadmin.templates.form-fields.create', ['template' => $template->id]) }}"
               class="flex w-full items-center justify-center rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                จัดการ Form
            </a>

            {{-- <a href="{{ route('superadmin.templates.edit',$template) }}"
               class="flex w-full items-center justify-center rounded-xl border border-blue-200 bg-white py-2.5 text-center text-sm font-semibold text-blue-600 transition duration-200 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                แก้ไข
            </a> --}}

            <div class="grid grid-cols-2 gap-2">

                <a href="{{ route('superadmin.templates.show',$template) }}"
                   class="flex h-full w-full items-center justify-center rounded-xl border border-blue-200 bg-white py-2.5 text-center text-sm font-semibold text-blue-600 transition duration-200 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    ดูรายละเอียด
                </a>

                <form action="{{ route('superadmin.templates.destroy',$template) }}" method="POST" class="h-full" onsubmit="return confirm('ต้องการลบ Template นี้หรือไม่?')">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                        class="flex h-full w-full items-center justify-center rounded-xl bg-rose-500 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-rose-600 focus:outline-none focus:ring-4 focus:ring-rose-200">
                        ลบ
                    </button>
                </form>

            </div>

        </div>

    </div>

</div>

@empty

<div class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 bg-white py-20 text-center">

    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-blue-600">
        <!-- icon -->
    </div>

    <h3 class="mt-6 text-xl font-semibold text-slate-800">ยังไม่มี Template</h3>

    <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-500">
        เริ่มต้นด้วยการสร้าง Template แรกของคุณ เพื่อใช้เป็นแม่แบบสำหรับการแข่งขัน
    </p>

    <a href="{{ route('superadmin.templates.create') }}"
        class="mt-8 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
        สร้าง Template
    </a>

</div>

@endforelse

</div>

@if(method_exists($competitionTemplates,'links'))
<div class="mt-8 flex items-center justify-between border-t border-slate-200 pt-6">
    <div class="text-sm text-slate-500">
        แสดงทั้งหมด <span class="font-semibold text-slate-600">{{ $competitionTemplates->count() }}</span> รายการ
    </div>

    <div>
        {{ $competitionTemplates->onEachSide(1)->links() }}
    </div>
</div>
@endif

</div>

@endsection
