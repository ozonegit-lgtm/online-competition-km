@extends('layouts.app')
@section('title', 'แก้ไของค์ความรู้')
@section('header')
<div><h1 class="text-slate-900 text-xl font-bold">แก้ไของค์ความรู้</h1>
<p class="mt-1 text-slate-500 text-xs">แหล่งที่มา: {{ $knowledgeItem->submission_id ? 'การแข่งขัน' : 'Manual KM' }} · เจ้าของ: {{ $knowledgeItem->creator?->username ?? 'ไม่มีเจ้าของ' }}</p></div>
@endsection
@section('content')
<div class="mx-auto max-w-7xl">
    <form method="POST" action="{{ route('superadmin.km.update', $knowledgeItem) }}" enctype="multipart/form-data" class="rounded-xl border bg-white p-4 shadow-sm sm:p-4 border-slate-200">
        @csrf @method('PUT')
        @include('superadmin.km._form')
        <div class="mt-4 flex flex-wrap justify-end gap-3">
            <a href="{{ route('superadmin.km.show', $knowledgeItem) }}" class="rounded-xl border text-sm font-semibold inline-flex items-center justify-center h-9 px-3">ยกเลิก</a>
            <button class="rounded-xl bg-blue-600 text-sm font-semibold text-white inline-flex items-center justify-center h-9 px-3">บันทึกการแก้ไข</button>
        </div>
    </form>
</div>
@endsection
