@extends('layouts.app')

@section('title', 'เพิ่มองค์ความรู้')

@section('header')
    <h1 class="text-2xl font-bold text-slate-900">เพิ่มองค์ความรู้</h1>
@endsection

@section('content')
    <div class="mx-auto max-w-5xl">
        <form method="POST" action="{{ route('competition-admin.km.store') }}" enctype="multipart/form-data"
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            @csrf
            @include('competition-admin.km._form')
            <div class="mt-8 flex flex-wrap justify-end gap-3">
                <a href="{{ route('competition-admin.km.index') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700">ยกเลิก</a>
                <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">บันทึกองค์ความรู้</button>
            </div>
        </form>
    </div>
@endsection