@extends('layouts.app')

@section('title', 'ผลงานที่ส่งเข้าประกวด')

@section('header')
<div>
    <h1 class="text-xl font-bold text-slate-800">
        ผลงานที่ส่งเข้าประกวด
    </h1>

    <p class="mt-1 text-sm text-slate-500">
        ตรวจสอบผลงานที่ผู้เข้าร่วมส่งเข้าประกวด
    </p>
</div>
@endsection

@section('content')

<div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">

@forelse($submissions as $submission)

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

    <div class="aspect-video overflow-hidden bg-slate-100">

        @php
            $file = $submission->files->first();
        @endphp

        @if($file)

            <img
                src="{{ Storage::disk('public')->url($file->file_path) }}"
                alt="{{ $submission->project_title }}"
                class="h-full w-full object-cover">

        @else

            <div class="flex h-full items-center justify-center text-xs text-slate-400">
                ไม่มีรูปภาพ
            </div>

        @endif

    </div>

    <div class="p-3">

        <span class="rounded-lg bg-blue-100 px-2 py-0.5 text-[11px] font-medium text-blue-700">
            {{ $submission->competition->category->category_name }}
        </span>

        <h3 class="mt-2 line-clamp-2 text-sm font-bold text-slate-800">
            {{ $submission->project_title }}
        </h3>

        <div class="mt-2 space-y-0.5 text-xs text-slate-600">

            <p class="truncate">
                <span class="font-medium">การแข่งขัน :</span>
                {{ $submission->competition->title }}
            </p>

            <p class="truncate">
                <span class="font-medium">รหัสผลงาน :</span>
                {{ $submission->submission_code }}
            </p>

            <p>
                <span class="font-medium">ส่งเมื่อ :</span>
                {{ $submission->submitted_at?->format('d/m/Y H:i') }}
            </p>

            <p>
                <span class="font-medium">สถานะ :</span>

                <span class="font-semibold text-green-600">
                    ส่งผลงานแล้ว
                </span>
            </p>

        </div>

    </div>

</div>

@empty

<div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
    ยังไม่มีผลงานที่ส่งเข้าประกวด
</div>

@endforelse

</div>

<div class="mt-6">
    {{ $submissions->links() }}
</div>

@endsection