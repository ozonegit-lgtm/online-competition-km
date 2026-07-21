<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', config('app.name'))
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100">

    <div class="min-h-screen flex">

        {{-- Left Section --}}
        <div class="hidden lg:flex lg:w-1/2 bg-blue-600 text-white">

            <div class="flex flex-col justify-center items-center w-full px-16">

                {{-- Logo --}}
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Logo"
                    class="w-28 h-28 mb-8">

                <h1 class="text-4xl font-bold mb-4 text-center">
                    ระบบจัดการแข่งขันออนไลน์
                </h1>

                <h2 class="text-2xl font-semibold mb-6 text-center">
                    Online Competition & Knowledge Management Platform
                </h2>

                <p class="text-center text-blue-100 leading-8 max-w-lg">
                    ระบบสำหรับบริหารจัดการแข่งขันออนไลน์
                    การประเมินผลงาน
                    และเผยแพร่ผลงานในรูปแบบคลังองค์ความรู้
                </p>

            </div>

        </div>

        {{-- Right Section --}}
        <div class="w-full lg:w-1/2 flex justify-center items-center px-6 py-10">

            <div class="w-full max-w-md">

                @yield('content')

            </div>

        </div>

    </div>

</body>

</html>