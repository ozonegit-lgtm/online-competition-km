@extends('layouts.app')
@section('title', 'เพิ่มองค์ความรู้')
@section('header')<h1 class="text-2xl font-bold text-slate-900">เพิ่มองค์ความรู้</h1>@endsection
@section('content')
<div class="mx-auto max-w-5xl">
    <form method="POST" action="{{ route('superadmin.km.store') }}" enctype="multipart/form-data" class="rounded-2xl border bg-white p-5 shadow-sm sm:p-7">
        @csrf
        @include('superadmin.km._form')
        <div class="mt-8 flex flex-wrap justify-end gap-3">
            <a href="{{ route('superadmin.km.index') }}" class="rounded-xl border px-5 py-2.5 text-sm font-semibold">ยกเลิก</a>
            <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white">บันทึกองค์ความรู้</button>
        </div>
    </form>
</div>
@endsection
