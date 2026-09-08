@extends('layouts.app')

@section('title', 'แก้ไของค์ความรู้')

@section('header')
    <div>
        <h1 class="text-slate-900 text-xl font-bold">แก้ไของค์ความรู้</h1>
        <p class="mt-1 text-slate-500 text-xs">แหล่งที่มา: {{ $knowledgeItem->submission_id ? 'ผลงานจากการแข่งขัน' : 'Manual KM' }}</p>
    </div>
@endsection

@section('content')
    <div class="mx-auto max-w-7xl">
        <form method="POST" action="{{ route('competition-admin.km.update', $knowledgeItem) }}" enctype="multipart/form-data"
            class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-4">
            @csrf
            @method('PUT')
            @include('competition-admin.km._form')
            <div class="mt-4 flex flex-wrap justify-end gap-3">
                <a href="{{ route('competition-admin.km.show', $knowledgeItem) }}" class="rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 inline-flex items-center justify-center h-9 px-3">ยกเลิก</a>
                <button class="rounded-xl bg-blue-600 text-sm font-semibold text-white hover:bg-blue-700 inline-flex items-center justify-center h-9 px-3">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
@endsection