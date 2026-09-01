@extends('layouts.app')

@section('title', 'จัดการองค์ความรู้')

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">จัดการองค์ความรู้</h1>
            <p class="mt-1 text-sm text-slate-500">{{ number_format($knowledgeItems->total()) }} รายการ</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('competition-admin.km.submissions.index') }}" class="rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-700">เลือกผลงานจากการแข่งขัน</a>
            @can('create', App\Models\KnowledgeItem::class)
                <a href="{{ route('competition-admin.km.create') }}" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">เพิ่มองค์ความรู้</a>
            @endcan
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        <form method="GET" action="{{ route('competition-admin.km.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
            <input name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อ บทสรุป หรือการแข่งขัน" class="rounded-xl border-slate-300 md:col-span-2">
            <select name="category_id" class="rounded-xl border-slate-300">
                <option value="">ทุกหมวดหมู่</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->category_name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border-slate-300">
                <option value="">ทุกสถานะ</option>
                <option value="draft" @selected(request('status') === 'draft')>ฉบับร่าง</option>
                <option value="published" @selected(request('status') === 'published')>เผยแพร่</option>
                <option value="hidden" @selected(request('status') === 'hidden')>ซ่อน</option>
            </select>
            <select name="source" class="rounded-xl border-slate-300">
                <option value="">ทุกแหล่งที่มา</option>
                <option value="manual" @selected(request('source') === 'manual')>Manual</option>
                <option value="competition" @selected(request('source') === 'competition')>การแข่งขัน</option>
            </select>
            <div class="flex gap-2 md:col-span-5 md:justify-end">
                <a href="{{ route('competition-admin.km.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">ล้างตัวกรอง</a>
                <button class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white">ค้นหา</button>
            </div>
        </form>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($knowledgeItems as $item)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700">{{ $item->submission_id ? 'การแข่งขัน' : 'Manual' }}</span>
                                <span class="rounded-full px-2.5 py-1 {{ $item->status === 'published' ? 'bg-emerald-50 text-emerald-700' : ($item->status === 'hidden' ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-700') }}">{{ $item->status }}</span>
                            </div>
                            <h2 class="mt-3 text-lg font-bold text-slate-900">{{ $item->title }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $item->category?->category_name ?? 'ไม่ระบุหมวดหมู่' }}</p>
                        </div>
                        @if ($item->published_at)
                            <time class="text-xs text-slate-400">{{ $item->published_at->format('d/m/Y H:i') }}</time>
                        @endif
                    </div>
                    @if ($item->submission_id)
                        <p class="mt-3 text-sm text-slate-600">การแข่งขัน: {{ $item->submission?->competition?->title ?? 'ไม่พบข้อมูลการแข่งขัน' }}</p>
                    @endif
                    @if ($item->summary)<p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $item->summary }}</p>@endif
                    <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                        @can('view', $item)<a href="{{ route('competition-admin.km.show', $item) }}" class="rounded-lg border px-3 py-2 text-sm">ดูรายละเอียด</a>@endcan
                        @can('update', $item)<a href="{{ route('competition-admin.km.edit', $item) }}" class="rounded-lg border px-3 py-2 text-sm">แก้ไข</a>@endcan
                        @if ($item->status === 'published')
                            @can('unpublish', $item)<x-ajax-form :action="route('competition-admin.km.unpublish', $item)" method="DELETE" confirm="ยืนยันถอนเผยแพร่รายการนี้?" success="ถอนเผยแพร่เรียบร้อย"><button class="rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white">ถอนเผยแพร่</button></x-ajax-form>@endcan
                        @else
                            @can('publish', $item)<x-ajax-form :action="route('competition-admin.km.publish', $item)" method="POST" confirm="ยืนยันเผยแพร่รายการนี้?" success="เผยแพร่เรียบร้อย"><button class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">เผยแพร่</button></x-ajax-form>@endcan
                        @endif
                        @can('delete', $item)<x-ajax-form :action="route('competition-admin.km.destroy', $item)" method="DELETE" confirm="ยืนยันลบองค์ความรู้นี้?" success="ลบเรียบร้อย"><button class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white">ลบ</button></x-ajax-form>@endcan
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500 lg:col-span-2">ไม่พบรายการองค์ความรู้</div>
            @endforelse
        </div>
        @if ($knowledgeItems->hasPages())<div>{{ $knowledgeItems->links() }}</div>@endif
    </div>
@endsection