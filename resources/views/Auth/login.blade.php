@extends('layouts.auth')

@section('title', 'เข้าสู่ระบบ')

@section('content')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="text-center mb-6">
        <img src="{{ asset('images/logo.png') }}"
             alt="Logo"
             class="w-14 h-14 mx-auto mb-3">

        <h1 class="text-xl font-bold text-gray-800">
            Online Competition
        </h1>

        <p class="text-gray-500 text-sm mt-1">
            ระบบจัดการแข่งขันออนไลน์และคลังองค์ความรู้
        </p>
    </div>

    {{-- Card --}}
    <div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">

        <div class="flex items-center gap-2 mb-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <path d="M10 17l5-5-5-5"/>
                    <path d="M15 12H3"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">
                เข้าสู่ระบบ
            </h2>
        </div>

        {{-- Success --}}
        @if(session('success'))
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-green-50 border border-green-200 px-3 py-2.5 text-sm text-green-700">
                <svg class="h-4 w-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Error --}}
        @if(session('error'))
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-red-50 border border-red-200 px-3 py-2.5 text-sm text-red-700">
                <svg class="h-4 w-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 8v5"/>
                    <path d="M12 16h.01"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form
            action="{{ route('login.post') }}"
            method="POST"
            class="space-y-4">

            @csrf

            {{-- Email --}}
            <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">
                    Email
                </label>

                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="m3 7 9 6 9-6"/>
                    </svg>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autofocus
                        required
                        placeholder="you@example.com"
                        class="w-full rounded-xl border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                </div>

                @error('email')
                    <p class="text-red-500 text-xs mt-1.5">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">
                    Password
                </label>

                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="11" width="16" height="9" rx="2"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>

                    <input
                        id="km-password"
                        type="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-gray-300 pl-10 pr-11 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">

                    <button
                        type="button"
                        id="km-toggle-password"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        aria-label="แสดง/ซ่อนรหัสผ่าน">
                        <svg id="km-eye-open" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="km-eye-closed" class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a20.6 20.6 0 0 1 4.22-5.06M9.9 4.24A9.6 9.6 0 0 1 12 4c7 0 11 7 11 7a20.6 20.6 0 0 1-2.16 3.19"/>
                            <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                            <path d="M1 1l22 22"/>
                        </svg>
                    </button>
                </div>

                @error('password')
                    <p class="text-red-500 text-xs mt-1.5">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="remember"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                    <span class="text-sm text-gray-600">
                        จดจำการเข้าสู่ระบบ
                    </span>
                </label>
            </div>

            {{-- Button --}}
            <button
                type="submit"
                class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 transition text-white text-sm font-medium py-2.5 rounded-xl shadow-sm shadow-blue-600/20">

                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <path d="M10 17l5-5-5-5"/>
                    <path d="M15 12H3"/>
                </svg>
                เข้าสู่ระบบ
            </button>

        </form>

    </div>

    <div class="mt-5 text-center text-xs text-gray-400">
        © {{ date('Y') }} Online Competition Platform
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('km-toggle-password');
        const input  = document.getElementById('km-password');
        const eyeOpen   = document.getElementById('km-eye-open');
        const eyeClosed = document.getElementById('km-eye-closed');

        if (!toggle || !input) return;

        toggle.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isPassword);
            eyeClosed.classList.toggle('hidden', !isPassword);
        });
    });
</script>

@endsection