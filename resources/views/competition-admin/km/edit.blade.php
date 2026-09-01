@extends('layouts.app')

@section('title', 'แก้ไของค์ความรู้')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-900">แก้ไของค์ความรู้</h1>
        <p class="mt-1 text-sm text-slate-500">แหล่งที่มา: {{ $knowledgeItem->submission_id ? 'ผลงานจากการแข่งขัน' : 'Manual KM' }}</p>
    </div>
@endsection

@section('content')
    <div class="mx-auto max-w-5xl">
        <form method="POST" action="{{ route('competition-admin.km.update', $knowledgeItem) }}" enctype="multipart/form-data"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            @csrf
            @method('PUT')
            @include('competition-admin.km._form')
            <div class="mt-8 flex flex-wrap justify-end gap-3">
                <a href="{{ route('competition-admin.km.show', $knowledgeItem) }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700">ยกเลิก</a>
                <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
@endsection