@extends('layouts.app')
@section('title', 'แก้ไของค์ความรู้')
@section('header')
<div><h1 class="text-2xl font-bold text-slate-900">แก้ไของค์ความรู้</h1>
<p class="mt-1 text-sm text-slate-500">แหล่งที่มา: {{ $knowledgeItem->submission_id ? 'การแข่งขัน' : 'Manual KM' }} · เจ้าของ: {{ $knowledgeItem->creator?->username ?? 'ไม่มีเจ้าของ' }}</p></div>
@endsection
@section('content')
<div class="mx-auto max-w-5xl">
    <form method="POST" action="{{ route('superadmin.km.update', $knowledgeItem) }}" enctype="multipart/form-data" class="rounded-2xl border bg-white p-5 shadow-sm sm:p-7">
        @csrf @method('PUT')
        @include('superadmin.km._form')
        <div class="mt-8 flex flex-wrap justify-end gap-3">
            <a href="{{ route('superadmin.km.show', $knowledgeItem) }}" class="rounded-xl border px-5 py-2.5 text-sm font-semibold">ยกเลิก</a>
            <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white">บันทึกการแก้ไข</button>
        </div>
    </form>
</div>
@endsection
