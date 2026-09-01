@extends('layouts.app')
@section('title', 'จัดการองค์ความรู้ทั้งหมด')
@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div><h1 class="text-2xl font-bold text-slate-900">จัดการองค์ความรู้ทั้งหมด</h1><p class="mt-1 text-sm text-slate-500">{{ number_format($knowledgeItems->total()) }} รายการ</p></div>
    @can('create', App\Models\KnowledgeItem::class)<a href="{{ route('superadmin.km.create') }}" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">เพิ่มองค์ความรู้</a>@endcan
</div>
@endsection
@section('content')
<div class="space-y-6">
    <form method="GET" action="{{ route('superadmin.km.index') }}" class="grid gap-3 rounded-2xl border bg-white p-4 shadow-sm md:grid-cols-6">
        <input name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อ บทสรุป เจ้าของ หรือการแข่งขัน" class="rounded-xl border-slate-300 md:col-span-2">
        <select name="category_id" class="rounded-xl border-slate-300"><option value="">ทุกหมวดหมู่</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category_id')===(string)$category->id)>{{ $category->category_name }}</option>@endforeach</select>
        <select name="status" class="rounded-xl border-slate-300"><option value="">ทุกสถานะ</option><option value="draft" @selected(request('status')==='draft')>Draft</option><option value="published" @selected(request('status')==='published')>Published</option><option value="hidden" @selected(request('status')==='hidden')>Hidden</option></select>
        <select name="source" class="rounded-xl border-slate-300"><option value="">ทุกแหล่งที่มา</option><option value="manual" @selected(request('source')==='manual')>Manual</option><option value="competition" @selected(request('source')==='competition')>การแข่งขัน</option></select>
        <select name="owner" class="rounded-xl border-slate-300"><option value="">ทุกเจ้าของ</option><option value="unassigned" @selected(request('owner')==='unassigned')>ไม่มีเจ้าของ</option>@foreach($owners as $owner)<option value="{{ $owner->id }}" @selected((string)request('owner')===(string)$owner->id)>{{ $owner->username }}</option>@endforeach</select>
        <div class="flex gap-2 md:col-span-6 md:justify-end"><a href="{{ route('superadmin.km.index') }}" class="rounded-xl border px-4 py-2 text-sm font-semibold">ล้าง Filter</a><button class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white">ค้นหา</button></div>
    </form>
    <div class="grid gap-4 lg:grid-cols-2">
    @forelse($knowledgeItems as $item)
        <article class="rounded-2xl border bg-white p-5 shadow-sm">
            <div class="flex flex-wrap gap-2 text-xs font-semibold"><span class="rounded-full bg-blue-50 px-2.5 py-1 text-blue-700">{{ $item->submission_id ? 'การแข่งขัน' : 'Manual' }}</span><span class="rounded-full bg-slate-100 px-2.5 py-1">{{ ucfirst($item->status) }}</span>@if($item->is_featured)<span class="rounded-full bg-amber-100 px-2.5 py-1 text-amber-700">Featured</span>@endif</div>
            <h2 class="mt-3 text-lg font-bold text-slate-900">{{ $item->title }}</h2>
            <div class="mt-2 space-y-1 text-sm text-slate-500"><p>เจ้าของ: {{ $item->creator?->username ?? 'ไม่มีเจ้าของ' }}</p><p>หมวดหมู่: {{ $item->category?->category_name ?? '-' }}</p>@if($item->submission_id)<p>การแข่งขัน: {{ $item->submission?->competition?->title ?? '-' }}</p>@endif @if($item->published_at)<p>เผยแพร่: {{ $item->published_at->format('d/m/Y H:i') }}</p>@endif</div>
            <div class="mt-5 flex flex-wrap gap-2">
                @can('view',$item)<a href="{{ route('superadmin.km.show',$item) }}" class="rounded-lg border px-3 py-2 text-xs font-semibold">ดู</a>@endcan
                @can('update',$item)<a href="{{ route('superadmin.km.edit',$item) }}" class="rounded-lg border px-3 py-2 text-xs font-semibold">แก้ไข</a>@endcan
                @can($item->status==='published'?'unpublish':'publish',$item)<form method="POST" action="{{ route($item->status==='published'?'superadmin.km.unpublish':'superadmin.km.publish',$item) }}">@csrf @if($item->status==='published') @method('DELETE') @endif<button class="rounded-lg border px-3 py-2 text-xs font-semibold">{{ $item->status==='published'?'ถอนเผยแพร่':'เผยแพร่' }}</button></form>@endcan
                @can('feature',$item)<form method="POST" action="{{ route($item->is_featured?'superadmin.km.unfeature':'superadmin.km.feature',$item) }}">@csrf @if($item->is_featured) @method('DELETE') @endif<button class="rounded-lg border px-3 py-2 text-xs font-semibold">{{ $item->is_featured?'ถอน Featured':'ตั้ง Featured' }}</button></form>@endcan
                @can('delete',$item)<form method="POST" action="{{ route('superadmin.km.destroy',$item) }}" onsubmit="return confirm('ยืนยันการลบรายการนี้?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-600">ลบ</button></form>@endcan
            </div>
        </article>
    @empty <div class="rounded-2xl border border-dashed bg-white p-12 text-center text-slate-500 lg:col-span-2">ไม่พบองค์ความรู้</div>@endforelse
    </div>
    @if($knowledgeItems->hasPages())<div>{{ $knowledgeItems->links() }}</div>@endif
</div>
@endsection
