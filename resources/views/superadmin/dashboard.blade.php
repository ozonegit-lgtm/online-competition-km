@extends('layouts.app')

@section('title', 'แดชบอร์ดผู้ดูแลระบบสูงสุด')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            แดชบอร์ดผู้ดูแลระบบสูงสุด
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            จัดการผู้ใช้งาน การอนุมัติ และข้อมูลภาพรวมของระบบ
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