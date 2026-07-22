<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', config('app.name'))
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="bg-slate-100">

    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        @include('components.sidebar')

        <div class="flex flex-col flex-1 min-w-0">

            {{-- Navbar --}}
            @include('components.navbar')

            {{-- Main Content --}}
            <main class="flex-1 p-6">

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

                {{-- Flash Message --}}
                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Page Content --}}
                @yield('content')

            </main>

            {{-- Footer --}}
            @include('components.footer')

        </div>

    </div>

    @stack('scripts')

</body>

</html>