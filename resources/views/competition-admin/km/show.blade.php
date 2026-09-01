@extends('layouts.app')

@section('title', $knowledgeItem->title)

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $knowledgeItem->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $knowledgeItem->submission_id ? 'ผลงานจากการแข่งขัน' : 'Manual KM' }}</p>
        </div>
        <a href="{{ route('competition-admin.km.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">กลับหน้ารายการ</a>
    </div>
@endsection

@section('content')
    <article class="mx-auto max-w-5xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @if ($knowledgeItem->cover_image)
            @php
                $coverUrl = str_starts_with($knowledgeItem->cover_image, 'knowledge-items/covers/')
                    ? route('knowledge-items.cover', $knowledgeItem)
                    : Storage::disk('public')->url($knowledgeItem->cover_image);
            @endphp
            <img src="{{ $coverUrl }}" alt="{{ $knowledgeItem->title }}" class="max-h-96 w-full object-cover">
        @else
            <div class="flex h-48 items-center justify-center bg-slate-100 text-slate-400">ไม่มีรูปปก</div>
        @endif
        <div class="space-y-6 p-5 sm:p-8">
            <div class="flex flex-wrap gap-2 text-sm">
                <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ $knowledgeItem->category?->category_name ?? 'ไม่ระบุหมวดหมู่' }}</span>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">{{ $knowledgeItem->status }}</span>
                <span class="rounded-full bg-violet-50 px-3 py-1 text-violet-700">{{ $knowledgeItem->submission_id ? 'การแข่งขัน' : 'Manual' }}</span>
            </div>
            @if ($knowledgeItem->submission_id)
                <p class="text-sm text-slate-600">การแข่งขัน: {{ $knowledgeItem->submission?->competition?->title ?? 'ไม่พบข้อมูลการแข่งขัน' }}</p>
            @endif
            @if ($knowledgeItem->summary)<section><h2 class="font-bold text-slate-900">บทสรุป</h2><p class="mt-2 whitespace-pre-line text-slate-700">{{ $knowledgeItem->summary }}</p></section>@endif
            @if ($knowledgeItem->content)<section><h2 class="font-bold text-slate-900">เนื้อหา</h2><div class="mt-2 whitespace-pre-line text-slate-700">{{ $knowledgeItem->content }}</div></section>@endif
            @if ($knowledgeItem->attachment_path)
                <a href="{{ route('knowledge-items.attachment', $knowledgeItem) }}" class="inline-flex rounded-xl bg-slate-100 px-4 py-3 text-sm font-semibold text-blue-700">{{ $knowledgeItem->attachment_original_name ?: 'ดาวน์โหลดไฟล์แนบ' }}</a>
            @endif
            @if ($knowledgeItem->published_at)<p class="text-sm text-slate-500">เผยแพร่เมื่อ {{ $knowledgeItem->published_at->format('d/m/Y H:i') }}</p>@endif
            <div class="flex flex-wrap gap-2 border-t pt-5">
                @can('update', $knowledgeItem)<a href="{{ route('competition-admin.km.edit', $knowledgeItem) }}" class="rounded-xl border px-4 py-2 text-sm font-semibold">แก้ไข</a>@endcan
                @if ($knowledgeItem->status === 'published')
                    @can('unpublish', $knowledgeItem)<x-ajax-form :action="route('competition-admin.km.unpublish', $knowledgeItem)" method="DELETE" confirm="ยืนยันถอนเผยแพร่?" success="ถอนเผยแพร่เรียบร้อย"><button class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white">ถอนเผยแพร่</button></x-ajax-form>@endcan
                @else
                    @can('publish', $knowledgeItem)<x-ajax-form :action="route('competition-admin.km.publish', $knowledgeItem)" method="POST" confirm="ยืนยันเผยแพร่?" success="เผยแพร่เรียบร้อย"><button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">เผยแพร่</button></x-ajax-form>@endcan
                @endif
                @can('delete', $knowledgeItem)<x-ajax-form :action="route('competition-admin.km.destroy', $knowledgeItem)" method="DELETE" confirm="ยืนยันลบองค์ความรู้นี้?" success="ลบเรียบร้อย"><button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white">ลบ</button></x-ajax-form>@endcan
            </div>
        </div>
    </article>
@endsection
