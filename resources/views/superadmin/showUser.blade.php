@extends('layouts.app')

@section('title', 'รายละเอียดผู้ใช้งาน')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            รายละเอียดผู้ใช้งาน
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            ตรวจสอบข้อมูลบัญชีผู้ใช้งานภายในระบบ
        </p>
    </div>
@endsection

@section('content')
    <div class="mx-auto max-w-4xl">

        {{-- ปุ่มย้อนกลับ --}}
        <div class="mb-6">
            <a href="{{ route('superadmin.createUser') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">

                <svg class="h-4 w-4"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="2"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                กลับไปหน้าจัดการผู้ใช้งาน
            </a>
        </div>

        {{-- การ์ดรายละเอียดผู้ใช้งาน --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- ส่วนหัวการ์ด --}}
            <div class="border-b border-slate-200 bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">

                    {{-- รูปโปรไฟล์ --}}
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-blue-600 shadow-sm">
                        <span class="text-2xl font-bold uppercase text-white">
                            {{ substr($user->username, 0, 1) }}
                        </span>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-bold text-slate-900">
                                {{ $user->username }}
                            </h2>

                            @if ($user->is_active)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    เปิดใช้งาน
                                </span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                    ปิดใช้งาน
                                </span>
                            @endif
                        </div>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $user->email }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- รายละเอียด --}}
            <div class="p-6">
                <h3 class="mb-5 text-base font-semibold text-slate-800">
                    ข้อมูลบัญชี
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    {{-- ID --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                            User ID
                        </p>

                        <p class="mt-2 font-semibold text-slate-800">
                            #{{ $user->id }}
                        </p>
                    </div>

                    {{-- Username --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Username
                        </p>

                        <p class="mt-2 font-semibold text-slate-800">
                            {{ $user->username }}
                        </p>
                    </div>

                    {{-- Email --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Email
                        </p>

                        <p class="mt-2 break-all font-semibold text-slate-800">
                            {{ $user->email }}
                        </p>
                    </div>

                    {{-- Role --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Role
                        </p>

                        <p class="mt-2 font-semibold text-slate-800">
                            {{ $user->role->role_name ?? 'ไม่พบข้อมูลบทบาท' }}
                        </p>
                    </div>

                    {{-- วันที่สร้าง --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                            วันที่สร้างบัญชี
                        </p>

                        <p class="mt-2 font-semibold text-slate-800">
                            {{ $user->created_at
                                ? $user->created_at->format('d/m/Y H:i')
                                : 'ไม่มีข้อมูล' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- ส่วนท้าย --}}
            <div class="flex flex-wrap items-center justify-end border-t border-slate-200 bg-slate-50 px-6 py-4"
                style="gap: 12px;">
                <a href="{{ route('superadmin.createUser') }}"
                class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    เสร็จสิ้น
                </a>
                <a href="{{ route('superadmin.editeUser', ['id' => $user->id]) }}"
                
                style="background-color: #fef3c7; color: #a16207;"
                class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition hover:opacity-80">
                    Edit
                </a>
                <form action="{{ route('superadmin.deleteUser', ['id' => $user->id]) }}" method="POST" class="inline" onsubmit="return confirm('คุณยืนยันที่จะลบข้อมูลหรือไม่');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection