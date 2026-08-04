@php
    $navbarUser = auth()->user();
    $navbarAvatar = $navbarUser?->adminProfile?->avatar;
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">

    <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6">

        <div class="flex min-w-0 items-center gap-3">

            {{-- Mobile Menu --}}
            <button
                id="sidebar-open-button"
                type="button"
                class="rounded-xl border border-slate-200 p-2.5 text-slate-600
                       transition hover:bg-slate-100 hover:text-slate-900 lg:hidden"
                aria-label="เปิดเมนู"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="min-w-0">
                <p class="truncate text-lg font-semibold text-slate-800">
                    @yield('title', 'แดชบอร์ด')
                </p>

                <p class="hidden truncate text-sm text-slate-500 sm:block">
                    Online Competition & Knowledge Management Platform
                </p>
            </div>

        </div>

        <div class="flex shrink-0 items-center gap-3">

            {{-- ข้อมูลผู้ใช้งาน --}}
            <div class="hidden text-right md:block">
                <p class="text-sm font-semibold text-slate-800">
                    {{ $navbarUser?->username }}
                </p>

                <p class="text-xs text-slate-500">
                    {{ $navbarUser?->role?->display_name }}
                </p>
            </div>

            {{-- รูปโปรไฟล์ --}}
            <a
                href="{{ route('profile.edit') }}"
                class="flex h-10 w-10 shrink-0 items-center justify-center
                       overflow-hidden rounded-full bg-blue-100
                       font-semibold text-blue-700 ring-2 ring-transparent
                       transition hover:ring-blue-300"
                title="แก้ไขโปรไฟล์"
                aria-label="แก้ไขโปรไฟล์"
            >
                @if ($navbarAvatar)
                    <img
                        src="{{ asset('storage/' . $navbarAvatar) }}"
                        alt="รูปโปรไฟล์ของ {{ $navbarUser?->username }}"
                        class="h-full w-full object-cover"
                    >
                @else
                    <span>
                        {{ strtoupper(substr($navbarUser?->username ?? 'U', 0, 1)) }}
                    </span>
                @endif
            </a>

            {{-- ปุ่มออกจากระบบ --}}
            <form
                action="{{ route('logout') }}"
                method="POST"
            >
                @csrf

                <button
                    type="submit"
                    class="rounded-xl border border-red-200 px-3 py-2
                           text-sm font-medium text-red-600 transition
                           hover:bg-red-50 hover:text-red-700"
                >
                    <span class="hidden sm:inline">
                        ออกจากระบบ
                    </span>

                    <svg
                        class="h-5 w-5 sm:hidden"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M10 17l5-5-5-5"/>
                        <path d="M15 12H3"/>
                        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                    </svg>
                </button>
            </form>

        </div>

    </div>

</header>