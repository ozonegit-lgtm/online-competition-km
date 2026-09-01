@extends('layouts.app')
@section('title', $knowledgeItem->title)
@section('header')<h1 class="text-2xl font-bold text-slate-900">รายละเอียดองค์ความรู้</h1>@endsection
@section('content')
<article class="mx-auto max-w-5xl overflow-hidden rounded-2xl border bg-white shadow-sm">
    @if($knowledgeItem->cover_image)
        @php
            $coverUrl = str_starts_with($knowledgeItem->cover_image, 'knowledge-items/covers/')
                ? route('knowledge-items.cover', $knowledgeItem)
                : Storage::disk('public')->url($knowledgeItem->cover_image);
        @endphp
        <img src="{{ $coverUrl }}" alt="{{ $knowledgeItem->title }}" class="h-64 w-full object-cover">
    @else
        <div class="flex h-40 items-center justify-center bg-slate-100 text-slate-400">ไม่มีรูปปก</div>
    @endif
    <div class="space-y-6 p-5 sm:p-8">
        <div><div class="flex flex-wrap gap-2 text-xs"><span class="rounded-full bg-blue-50 px-3 py-1">{{ $knowledgeItem->submission_id?'การแข่งขัน':'Manual' }}</span><span class="rounded-full bg-slate-100 px-3 py-1">{{ ucfirst($knowledgeItem->status) }}</span>@if($knowledgeItem->is_featured)<span class="rounded-full bg-amber-100 px-3 py-1">Featured</span>@endif</div><h2 class="mt-3 text-2xl font-bold">{{ $knowledgeItem->title }}</h2></div>
        <dl class="grid gap-3 text-sm sm:grid-cols-2"><div><dt class="font-semibold">เจ้าของ</dt><dd>{{ $knowledgeItem->creator?->username ?? 'ไม่มีเจ้าของ' }}</dd></div><div><dt class="font-semibold">หมวดหมู่</dt><dd>{{ $knowledgeItem->category?->category_name ?? '-' }}</dd></div><div><dt class="font-semibold">วันเผยแพร่</dt><dd>{{ $knowledgeItem->published_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>@if($knowledgeItem->submission_id)<div><dt class="font-semibold">การแข่งขัน</dt><dd>{{ $knowledgeItem->submission?->competition?->title ?? '-' }}</dd></div>@endif</dl>
        @if($knowledgeItem->summary)<section><h3 class="font-bold">บทสรุป</h3><p class="mt-2 whitespace-pre-line text-slate-700">{{ $knowledgeItem->summary }}</p></section>@endif
        @if($knowledgeItem->content)<section><h3 class="font-bold">เนื้อหา</h3><div class="mt-2 whitespace-pre-line text-slate-700">{{ $knowledgeItem->content }}</div></section>@endif
        @if($knowledgeItem->attachment_path)<a href="{{ route('knowledge-items.attachment', $knowledgeItem) }}" class="inline-flex rounded-xl border px-4 py-2 text-sm font-semibold">{{ $knowledgeItem->attachment_original_name ?: 'ดาวน์โหลดไฟล์แนบ' }}</a>@endif
        <div class="flex flex-wrap gap-2 border-t pt-5">
            @can('update',$knowledgeItem)<a href="{{ route('superadmin.km.edit',$knowledgeItem) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">แก้ไข</a>@endcan
            @can($knowledgeItem->status==='published'?'unpublish':'publish',$knowledgeItem)<form method="POST" action="{{ route($knowledgeItem->status==='published'?'superadmin.km.unpublish':'superadmin.km.publish',$knowledgeItem) }}">@csrf @if($knowledgeItem->status==='published') @method('DELETE') @endif<button class="rounded-lg border px-4 py-2 text-sm font-semibold">{{ $knowledgeItem->status==='published'?'ถอนเผยแพร่':'เผยแพร่' }}</button></form>@endcan
            @can('feature',$knowledgeItem)<form method="POST" action="{{ route($knowledgeItem->is_featured?'superadmin.km.unfeature':'superadmin.km.feature',$knowledgeItem) }}">@csrf @if($knowledgeItem->is_featured) @method('DELETE') @endif<button class="rounded-lg border px-4 py-2 text-sm font-semibold">{{ $knowledgeItem->is_featured?'ถอน Featured':'ตั้ง Featured' }}</button></form>@endcan
            @can('delete',$knowledgeItem)<form method="POST" action="{{ route('superadmin.km.destroy',$knowledgeItem) }}" onsubmit="return confirm('ยืนยันการลบรายการนี้?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600">ลบ</button></form>@endcan
        </div>
    </div>
</article>
@endsection
