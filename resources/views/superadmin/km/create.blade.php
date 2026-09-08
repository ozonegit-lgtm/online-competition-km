@extends('layouts.app')
@section('title', 'เพิ่มองค์ความรู้')
@section('header')<h1 class="text-slate-900 text-xl font-bold">เพิ่มองค์ความรู้</h1>@endsection
@section('content')
<div class="mx-auto max-w-7xl">
    <form method="POST" action="{{ route('superadmin.km.store') }}" enctype="multipart/form-data" class="rounded-xl border bg-white p-4 shadow-sm sm:p-4 border-slate-200">
        @csrf
        @include('superadmin.km._form')
        <div class="mt-4 flex flex-wrap justify-end gap-3">
            <a href="{{ route('superadmin.km.index') }}" class="rounded-xl border text-sm font-semibold inline-flex items-center justify-center h-9 px-3">ยกเลิก</a>
            <button class="rounded-xl bg-blue-600 text-sm font-semibold text-white inline-flex items-center justify-center h-9 px-3">บันทึกองค์ความรู้</button>
        </div>
    </form>
</div>
@endsection
