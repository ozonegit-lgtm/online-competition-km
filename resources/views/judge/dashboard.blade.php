@extends('layouts.app')

@section('title', 'แดชบอร์ดกรรมการ')

@section('header')
    <div>
        <h1 class="text-slate-800 text-xl font-bold">
            แดชบอร์ดกรรมการ
        </h1>

        <p class="mt-1 text-slate-500 text-xs">
            ตรวจสอบผลงานและให้คะแนนการแข่งขันที่ได้รับมอบหมาย
        </p>
    </div>
@endsection

@section('content')
    <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">

        <p class="text-slate-700">
            ยินดีต้อนรับ
            <span class="font-semibold">
                {{ auth()->user()->username }}
            </span>
        </p>

        <p class="mt-2 text-slate-500 text-xs">
            สิทธิ์การใช้งาน:
            {{ auth()->user()->role->display_name }}
        </p>


    </div>
@endsection