@php
    $currentUser = auth()->user();
    $roleName = $currentUser?->role?->role_name;

    $dashboardRoute = match ($roleName) {
        'Super Admin' => 'superadmin.dashboard',
        'Competition Admin' => 'competition-admin.dashboard',
        'Judge' => 'judge.dashboard',
        default => 'dashboard',
    };

    $roleDisplayName = $currentUser?->role?->display_name
        ?? 'ไม่พบข้อมูลสิทธิ์';
@endphp

<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col
           border-r border-slate-800 bg-slate-950 text-white shadow-xl
           transition-transform duration-300 ease-in-out
           lg:static lg:z-auto lg:translate-x-0 lg:shadow-none"
>
    {{-- Logo --}}
    <div class="flex h-20 items-center justify-between border-b border-slate-800 px-5">

        <a
            href="{{ route($dashboardRoute) }}"
            class="flex min-w-0 items-center gap-3"
        >
            <div class="flex h-11 w-11 shrink-0 items-center justify-center
                        rounded-xl bg-blue-600 font-bold text-white shadow-lg
                        shadow-blue-950/40">
                OC
            </div>

            <div class="min-w-0">
                <p class="truncate font-semibold text-white">
                    Competition KM
                </p>

                <p class="truncate text-xs text-slate-400">
                    Management Platform
                </p>
            </div>
        </a>

        <button
            id="sidebar-close-button"
            type="button"
            class="rounded-lg p-2 text-slate-400 transition
                   hover:bg-slate-800 hover:text-white lg:hidden"
            aria-label="ปิดเมนู"
        >
            <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>

    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-4 py-6">

        <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
            เมนูหลัก
        </p>

        <a
            href="{{ route($dashboardRoute) }}"
            class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium
                   transition
                   {{ request()->routeIs($dashboardRoute)
                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            <svg
                class="h-5 w-5 shrink-0"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M3 13h8V3H3v10z"/>
                <path d="M13 21h8V11h-8v10z"/>
                <path d="M13 3h8v6h-8V3z"/>
                <path d="M3 21h8v-6H3v6z"/>
            </svg>

            <span>แดชบอร์ด</span>
        </a>
        {{-- เมนูตาม Role --}}
        <div class="mt-8">

            <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                {{ $roleDisplayName }}
            </p>

            @if ($roleName === 'Super Admin')

                <div class="space-y-1">

                    <a
                        href="{{ route('superadmin.createUser') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
                        {{ request()->routeIs('superadmin.createUser')
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        <span>จัดการผู้ใช้งาน</span>
                    </a>

                    <a
                        href="{{ route('superadmin.templates.index') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
                        {{ request()->routeIs('superadmin.template.index')
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        <span>จัดการข้อมูลแบบform</span>
                    </a>
                                        <a
                        href="{{ route('superadmin.categories.create') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
                        {{ request()->routeIs('superadmin.template.index')
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        <span>จัดการข้อประเภทการแข่งขัน</span>
                    </a>

                </div>

            @elseif ($roleName === 'Competition Admin')

                <div class="space-y-1">

                    <a
                        href="{{ route('competition-admin.createCompitetion') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
                        {{ request()->routeIs('competition-admin.createCompitetion')
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        <span>สร้างการแข่งขัน</span>
                    </a>

                    <a
                        href="#"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium
                            text-slate-500 cursor-not-allowed"
                    >
                        <span>ผลงานที่ส่ง</span>
                        <span class="ml-auto text-xs">เร็ว ๆ นี้</span>
                    </a>

                </div>

            @elseif ($roleName === 'Judge')

                <div class="space-y-1">

                    <a
                        href="#"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium
                            text-slate-500 cursor-not-allowed"
                    >
                        <span>งานที่ได้รับมอบหมาย</span>
                        <span class="ml-auto text-xs">เร็ว ๆ นี้</span>
                    </a>

                    <a
                        href="#"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium
                            text-slate-500 cursor-not-allowed"
                    >
                        <span>ประวัติการให้คะแนน</span>
                        <span class="ml-auto text-xs">เร็ว ๆ นี้</span>
                    </a>

                </div>

            @endif

        </div>

    </nav>

    {{-- User --}}
    <div class="border-t border-slate-800 p-4">

        <div class="flex items-center gap-3 rounded-xl bg-slate-900 p-3">

            <div class="flex h-10 w-10 shrink-0 items-center justify-center
                        rounded-full bg-blue-600 font-semibold text-white">
                {{ strtoupper(substr($currentUser?->username ?? 'U', 0, 1)) }}
            </div>

            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">
                    {{ $currentUser?->username ?? 'Unknown' }}
                </p>

                <p class="truncate text-xs text-slate-400">
                    {{ $roleDisplayName }}
                </p>
            </div>

        </div>

    </div>
</aside>