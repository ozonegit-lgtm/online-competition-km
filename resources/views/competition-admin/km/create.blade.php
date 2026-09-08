@extends('layouts.app')

@section('title', 'เพิ่มองค์ความรู้')

@section('header')
    <h1 class="text-slate-900 text-xl font-bold">เพิ่มองค์ความรู้</h1>
@endsection

@section('content')
    <div class="mx-auto max-w-7xl">
        <form method="POST" action="{{ route('competition-admin.km.store') }}" enctype="multipart/form-data"
            class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-4">
            @csrf
            @include('competition-admin.km._form')
            <div class="mt-4 flex flex-wrap justify-end gap-3">
                <a href="{{ route('competition-admin.km.index') }}" class="rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 inline-flex items-center justify-center h-9 px-3">ยกเลิก</a>
                <button class="rounded-xl bg-blue-600 text-sm font-semibold text-white hover:bg-blue-700 inline-flex items-center justify-center h-9 px-3">บันทึกองค์ความรู้</button>
            </div>
        </form>
    </div>
@endsection