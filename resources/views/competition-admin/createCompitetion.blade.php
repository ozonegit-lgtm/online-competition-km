@extends('layouts.app')

@section('title', 'สร้างการแข่งขัน')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            สร้างการแข่งขัน
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            สร้างการแข่งขัน จัดการกรรมการ และตรวจสอบผลงาน
        </p>
    </div>
@endsection

@section('content')
    <div class="rounded-2xl bg-white p-6 shadow-sm">

        <p class="text-slate-700">
            ยินดีต้อนรับ
            <span class="font-semibold">
                {{ auth()->user()->username }}
            </span>
        </p>

        <p class="mt-2 text-sm text-slate-500">
            สิทธิ์การใช้งาน:
            {{ auth()->user()->role->display_name }}
        </p>

    </div>
@endsection