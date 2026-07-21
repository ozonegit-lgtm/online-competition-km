@extends('layouts.auth')

@section('title', 'เข้าสู่ระบบ')

@section('content')
<link rel="stylesheet" href="/build/assets/app-COHMdTRk.css">
<div class="w-full max-w-md">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <img src="{{ asset('images/logo.png') }}"
             alt="Logo"
             class="w-20 h-20 mx-auto mb-4">

        <h1 class="text-2xl font-bold text-gray-800">
            Online Competition
        </h1>

        <p class="text-gray-500 mt-2">
            ระบบจัดการแข่งขันออนไลน์และคลังองค์ความรู้
        </p>
    </div>

    {{-- Card --}}
    <div class="bg-white shadow-xl rounded-2xl p-8">

        <h2 class="text-xl font-semibold text-gray-800 mb-6">
            เข้าสู่ระบบ
        </h2>

        {{-- Success --}}
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 border border-green-300 px-4 py-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error --}}
        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-100 border border-red-300 px-4 py-3 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form
            action="{{ route('login.post') }}"
            method="POST"
            class="space-y-5">

            @csrf

            {{-- Email --}}
            <div>

                <label
                    class="block mb-2 text-sm font-medium text-gray-700">

                    Email

                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autofocus
                    required
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none">

                @error('email')
                    <p class="text-red-500 text-sm mt-2">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- Password --}}
            <div>

                <label
                    class="block mb-2 text-sm font-medium text-gray-700">

                    Password

                </label>

                <input
                    type="password"
                    name="password"
                    required
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none">

                @error('password')
                    <p class="text-red-500 text-sm mt-2">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- Remember --}}
            <div class="flex items-center justify-between">

                <label class="flex items-center gap-2">

                    <input
                        type="checkbox"
                        name="remember"
                        class="rounded border-gray-300">

                    <span class="text-sm text-gray-600">
                        จดจำการเข้าสู่ระบบ
                    </span>

                </label>

            </div>

            {{-- Button --}}
            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-medium py-3 rounded-xl">

                เข้าสู่ระบบ

            </button>

        </form>

    </div>

    <div class="mt-6 text-center text-sm text-gray-500">
        © {{ date('Y') }} Online Competition Platform
    </div>

</div>

@endsection