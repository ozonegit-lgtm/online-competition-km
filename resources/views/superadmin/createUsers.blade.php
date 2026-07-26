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
    <form
        action="{{ route('superadmin.storeUser') }}"
        {{--  --}}
        method="POST"
        autocomplete="off"
        class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
        @csrf
        
        {{-- หัวข้อฟอร์ม --}}
        <div class="flex items-center gap-3 border-b border-slate-200 pb-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-700">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    class="block h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M4 21a8 8 0 0 1 16 0"></path>
                </svg>
            </div>

            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    ข้อมูลบัญชี
                </h2>

                <p class="text-sm text-slate-500">
                    กรอกข้อมูลสำหรับใช้เข้าสู่ระบบ
                </p>
            </div>
        </div>
        {{-- ข้อมูลบัญชี --}}
        <div class="mt-6 grid gap-6 md:grid-cols-2">
            {{-- Username --}}
            <div>
                <label
                    for="username"
                    class="block text-sm font-medium text-slate-700">
                    ชื่อผู้ใช้ <span class="text-red-500">*</span>
                </label>
                <div class="relative mt-2">
                    <input
                        id="username"
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        placeholder="เช่น admin_jade"
                        autocomplete="off"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 pr-11
                               text-slate-800 outline-none transition
                               placeholder:text-slate-400
                               focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100">
                    <svg
                        width="20"
                        height="20"
                        class="absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0ZM4 21a8 8 0 0116 0"/>
                    </svg>
                </div>
                <p class="mt-1.5 text-xs text-slate-500">
                    ต้องไม่ซ้ำกับบัญชีอื่นและควรมีอย่างน้อย 6 ตัวอักษร
                </p>
                @error('username')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            {{-- Email --}}
            <div>
                <label
                    for="email"
                    class="block text-sm font-medium text-slate-700">
                    อีเมล <span class="text-red-500">*</span>
                </label>
                <div class="relative mt-2">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="admin@university.ac.th"
                        autocomplete="off"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 pr-11
                               text-slate-800 outline-none transition
                               placeholder:text-slate-400
                               focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100">
                    <svg
                        width="20"
                        height="20"
                        class="absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2Z"/>
                    </svg>
                </div>

                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            {{-- Password --}}
            <div>
                <label
                    for="password"
                    class="block text-sm font-medium text-slate-700">
                    รหัสผ่าน <span class="text-red-500">*</span>
                </label>
                <div class="relative mt-2">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="อย่างน้อย 8 ตัวอักษร"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 pr-12
                               text-slate-800 outline-none transition
                               placeholder:text-slate-400
                               focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100">
                    <button
                        type="button"
                        onclick="togglePassword('password', this)"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                        aria-label="แสดงหรือซ่อนรหัสผ่าน">
                            <svg
                                width="20"
                                height="20"
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="3"
                                    stroke-width="2"/>
                            </svg>
                    </button>
                </div>
                {{-- <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200">
                    <div class="h-full w-1/3 rounded-full bg-amber-500"></div>
                </div> --}}
                <p class="mt-1.5 text-xs text-slate-500">
                    รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร
                </p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            {{-- Confirm Password --}}
            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm font-medium text-slate-700">
                    ยืนยันรหัสผ่าน <span class="text-red-500">*</span>
                </label>
                <div class="relative mt-2">
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        placeholder="กรอกรหัสผ่านอีกครั้ง"
                        autocomplete="new-password"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 pr-12
                               text-slate-800 outline-none transition
                               placeholder:text-slate-400
                               focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100">
                    <button
                        type="button"
                        onclick="togglePassword('password_confirmation', this)"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                        aria-label="แสดงหรือซ่อนรหัสผ่าน">
                        <svg
                            width="20"
                            height="20"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                            <circle cx="12" cy="12" r="3" stroke-width="2" />
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Role --}}
            <div>
                <label
                    for="role_id"
                    class="block text-sm font-medium text-slate-700">
                    บทบาทผู้ใช้งาน <span class="text-red-500">*</span>
                </label>
                <select
                    id="role_id"
                    name="role_id"
                    required
                    class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3
                           text-slate-800 outline-none transition
                           focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100">
                    <option value="">เลือกบทบาท</option>
                    @foreach ($roles as $role)
                        <option
                            value="{{ $role->id }}"
                            @selected(old('role_id') == $role->id)>
                            {{ $role->role_name }} 
                            {{-- — {{ $role->description }} --}}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        {{-- สถานะบัญชี --}}
        <div class="mt-8">
            <p class="text-sm font-medium text-slate-700">
                สถานะบัญชี <span class="text-red-500">*</span>
            </p>
            <div class="mt-3 flex flex-wrap gap-6">
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="radio"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', '1') === '1')
                        class="h-5 w-5 accent-green-700">
                    <span class="font-medium text-slate-700">
                        ใช้งาน
                    </span>
                </label>
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="radio"
                        name="is_active"
                        value="0"
                        @checked(old('is_active') === '0')
                        class="h-5 w-5 accent-green-700">
                    <span class="font-medium text-slate-700">
                        ปิดใช้งาน
                    </span>
                </label>
            </div>
            @error('is_active')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        {{-- ปุ่ม --}}
        <div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
            <a
                href="{{ route('superadmin.dashboard') }}"
                class="rounded-xl border border-slate-300 px-5 py-2.5 font-medium text-slate-600
                       transition hover:bg-slate-100">
                ย้อนกลับ
            </a>
            <button
                type="submit"
                class="rounded-xl bg-green-700 px-6 py-2.5 font-medium text-white
                       shadow-sm transition hover:bg-green-800 focus:ring-4 focus:ring-green-200">
                เพิ่มข้อมูลผู้ใช้
            </button>
        </div>
    </form>
    <div class="mt-6 flex flex-col gap-2">
        @if ($users->isNotEmpty())
            @foreach ($users as $user)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:shadow-md">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        {{-- ข้อมูลผู้ใช้งาน --}}
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100">
                                <span class="text-lg font-bold text-blue-600">
                                    {{ strtoupper(substr($user->username, 0, 1)) }}
                                </span>
                            </div>

                            <div>
                                <h3 class="text-base font-semibold text-gray-900">
                                    {{ $user->username }}
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $user->email }}
                                </p>
                            </div>
                        </div>

                        {{-- ปุ่มจัดการ --}}
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('superadmin.showUser', ['id' => $user->id]) }}"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100">
                                View
                            </a>

                            {{-- <a href="#"
                            style="background-color: #fef3c7; color: #a16207;"
                            class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition hover:opacity-80">
                                Edit
                            </a>

                            <form action="#" method="POST" class="inline">
                                @csrf

                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100">
                                    Delete
                                </button>
                            </form> --}}
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                    <svg class="h-6 w-6 text-gray-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                </div>

                <h3 class="mt-4 text-sm font-semibold text-gray-900">
                    ไม่พบข้อมูลผู้ใช้
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    ขณะนี้ยังไม่มีข้อมูล Users อยู่ในระบบ
                </p>
            </div>
        @endif
    </div>
@endsection
@push('scripts')
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);

            input.type = input.type === 'password'
                ? 'text'
                : 'password';
        }
    </script>
@endpush