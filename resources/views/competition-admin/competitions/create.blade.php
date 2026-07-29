@extends('layouts.app')

@section('title', 'สร้างการแข่งขัน')

@section('header')
<div>
    <h1 class="text-2xl font-bold text-slate-800">
        สร้างการแข่งขัน
    </h1>

    <p class="mt-1 text-sm text-slate-500">
        เลือกประเภทและแม่แบบสำหรับสร้างการแข่งขัน
    </p>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-3xl px-6 py-8">

    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">

        {{-- Category --}}
        <div>
            <label
                for="category_id"
                class="block text-sm font-medium text-slate-700">
                ประเภทการแข่งขัน
                <span class="text-red-500">*</span>
            </label>

            <select
                id="category_id"
                name="category_id"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3
                       text-slate-800 outline-none transition
                       focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">

                <option value="">
                    -- เลือกประเภทการแข่งขัน --
                </option>

                @forelse($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->category_name }}
                    </option>
                @empty
                    <option value="" disabled>
                        ไม่มีประเภทการแข่งขันที่เปิดใช้งาน
                    </option>
                @endforelse
            </select>
        </div>

        {{-- Template --}}
        <div class="mt-6">
            <label
                for="template_id"
                class="block text-sm font-medium text-slate-700">
                แม่แบบการแข่งขัน
            </label>

            <select
                id="template_id"
                name="template_id"
                class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3
                       text-slate-800 outline-none transition
                       focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100">

                <option value="">
                    -- ไม่ใช้แม่แบบ --
                </option>

                @forelse($templates as $template)
                    <option value="{{ $template->id }}">
                        {{ $template->template_name }}
                    </option>
                @empty
                    <option value="" disabled>
                        ไม่มีแม่แบบที่เปิดใช้งาน
                    </option>
                @endforelse
            </select>
        </div>

    </div>
</div>
@endsection