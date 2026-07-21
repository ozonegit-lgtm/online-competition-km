@extends('layouts.app')

@section('title', 'แดชบอร์ด')

@section('content')
    <div class="p-8">
        <div class="rounded-2xl bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-800">
                แดชบอร์ด
            </h1>

            <p class="mt-2 text-gray-600">
                ยินดีต้อนรับ {{ auth()->user()->username }}
            </p>

            <form action="{{ route('logout') }}" method="POST" class="mt-6">
                @csrf

                <button
                    type="submit"
                    class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                >
                    ออกจากระบบ
                </button>
            </form>
        </div>
    </div>
@endsection