@extends('layouts.app')

@section('title', 'แดชบอร์ดผู้จัดการแข่งขัน')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            แดชบอร์ดผู้จัดการแข่งขัน
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

        <form
            action="{{ route('logout') }}"
            method="POST"
            class="mt-6"
        >
            @csrf

            <button
                type="submit"
                class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700"
            >
                ออกจากระบบ
            </button>
        </form>

    </div>
@endsection