@extends('layouts.app')

@section('title', 'แก้ไขผู้ใช้งาน')

@section('header')
    <div>
        <h1 class="text-slate-800 text-xl font-bold">
            แก้ไขผู้ใช้งาน
        </h1>

        <p class="mt-1 text-slate-500 text-xs">
            แก้ไขข้อมูลบัญชี บทบาท และสถานะผู้ใช้งาน
        </p>
    </div>
@endsection

@section('content')
<div>
    <form
        action="{{ route('superadmin.updateUser', ['id' => $user->id]) }}"
        enctype="multipart/form-data"
        method="POST"
        autocomplete="off"
        class="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">

        @csrf
        @method('PUT')
        {{-- หัวข้อฟอร์ม --}}
        <div class="flex items-center gap-3 border-b border-slate-200 pb-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-700">
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
                <h2 class="text-slate-800 text-base font-semibold">
                    ข้อมูลบัญชี
                </h2>

                <p class="text-slate-500 text-xs">
                    กรอกข้อมูลสำหรับใช้เข้าสู่ระบบ
                </p>
            </div>
        </div>
        {{-- ข้อมูลบัญชี --}}
        <div class="mt-4 grid gap-4 md:grid-cols-2">
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
                        value="{{ old('username',$user->username) }}"
                        placeholder="เช่น admin_jade"
                        autocomplete="off"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100 text-sm py-2 h-10 px-3 pr-11">
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
                <p class="mt-1.5 text-slate-500 text-xs">
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
                        value="{{ old('email',$user->email) }}"
                        placeholder="admin@university.ac.th"
                        autocomplete="off"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100 text-sm py-2 h-10 px-3 pr-11">
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
                <label for="password"
                    class="block text-sm font-medium text-slate-700">
                    รหัสผ่านใหม่
                    <span class="ml-1 text-xs font-normal text-slate-400">
                        (ไม่บังคับ)
                    </span>
                </label>
                <div class="relative mt-2">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="เว้นว่างไว้ หากไม่ต้องการเปลี่ยนรหัสผ่าน"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100 text-sm py-2 h-10 px-3 pr-12">
                    <button
                        type="button"
                        onclick="togglePassword('password', this)"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700 inline-flex items-center justify-center text-sm h-9 px-3"
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
                <p class="mt-1.5 text-slate-500 text-xs">
                    เว้นว่างไว้เพื่อใช้รหัสผ่านเดิม หากต้องการเปลี่ยน รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร
                </p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
            {{-- Confirm Password --}}
            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm font-medium text-slate-700">
                    ยืนยันรหัสผ่านใหม่
                    <span class="ml-1 text-xs font-normal text-slate-400">
                        (กรอกเมื่อต้องการเปลี่ยนรหัสผ่าน)
                    </span>
                </label>
                @error('password_confirmation')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
                <div class="relative mt-2">
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        placeholder="ยืนยันรหัสผ่านใหม่"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100 text-sm py-2 h-10 px-3 pr-12">
                    <button
                        type="button"
                        onclick="togglePassword('password_confirmation', this)"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 inline-flex items-center justify-center text-sm h-9 px-3"
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
                    class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 text-slate-800 outline-none transition focus:border-green-600 focus:bg-white focus:ring-4 focus:ring-green-100 text-sm py-2 h-10 px-3">
                    <option value="">เลือกบทบาท</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}"
                            @selected(old('role_id', $user->role_id) == $role->id)>
                            {{ $role->role_name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        {{-- สถานะบัญชี --}}
        <div class="mt-4">
            <p class="text-sm font-medium text-slate-700">
                สถานะบัญชี <span class="text-red-500">*</span>
            </p>
            <div class="mt-3 flex flex-wrap gap-4">
                <label class="flex cursor-pointer items-center gap-3">
                    <input
                        type="radio"
                        name="is_active"
                        value="1"
                        @checked((string) old('is_active', $user->is_active) === '1')
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
                        @checked((string) old('is_active', $user->is_active) === '0')
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
        <div class="mt-4 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-4">
            <a
                href="{{ route('superadmin.showUser', ['id' => $user->id]) }}"
                class="rounded-xl border border-slate-300 font-medium text-slate-600 transition hover:bg-slate-100 inline-flex items-center justify-center text-sm h-9 px-3">
                ย้อนกลับ
            </a>
            {{-- <button
                type="submit"
                class="rounded-xl bg-green-700 px-6 py-2.5 font-medium text-white
                       shadow-sm transition hover:bg-green-800 focus:ring-4 focus:ring-green-200">
                เพิ่มข้อมูลผู้ใช้
            </button> --}}
            <button type="submit"
                    class="rounded-xl bg-green-700 font-medium text-white shadow-sm transition hover:bg-green-800 focus:ring-4 focus:ring-green-200 inline-flex items-center justify-center text-sm h-9 px-3">
                    บันทึกการแก้ไข
            </button>
        </div>
    </form>
</div>
@endsection