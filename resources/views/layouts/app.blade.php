<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', config('app.name'))
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')
</head>

<body class="bg-slate-100 font-sans text-slate-800">

    <div class="min-h-screen lg:flex">

        {{-- Sidebar --}}
        @include('components.sidebar')

        {{-- Mobile Overlay --}}
        <div
            id="sidebar-overlay"
            class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden"
        ></div>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col">

            {{-- Navbar --}}
            @include('components.navbar')

            {{-- Main Content --}}
            <main class="flex-1 p-4 sm:p-6">

                <div class="mx-auto w-full max-w-7xl">

                    {{-- Breadcrumb --}}
                    @hasSection('breadcrumb')
                        <div class="mb-4">
                            @yield('breadcrumb')
                        </div>
                    @endif

                    {{-- Page Header --}}
                    @hasSection('header')
                        <div class="mb-6">
                            @yield('header')
                        </div>
                    @endif

                    {{-- Global Alert --}}
                    <x-alert />

                    {{-- Page Content --}}
                    @yield('content')

                </div>

            </main>

            {{-- Footer --}}
            @include('components.footer')

        </div>

    </div>

    @stack('scripts')
</body>

</html>