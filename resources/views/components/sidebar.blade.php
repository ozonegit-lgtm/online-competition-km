@php
    $currentUser = auth()->user();
    $roleName = $currentUser?->role?->role_name;

    $dashboardRoute = match ($roleName) {
        'Super Admin' => 'superadmin.dashboard',
        'Competition Admin' => 'competition-admin.dashboard',
        'Judge' => 'judge.dashboard',
        default => 'dashboard',
    };

    $roleDisplayName = $currentUser?->role?->display_name ?? 'ไม่พบข้อมูลสิทธิ์';
@endphp

<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-60 shrink-0 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 text-white shadow-sm transition-[width,transform] duration-200 ease-in-out lg:static lg:z-auto lg:translate-x-0 lg:shadow-none">

    {{-- Logo --}}
    <div class="sidebar-header relative flex h-16 shrink-0 items-center border-b border-slate-800 px-4 pr-14">
        <a href="{{ route($dashboardRoute) }}" class="sidebar-brand flex min-w-0 items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 font-bold text-white shadow-sm shadow-blue-950/40">OC</div>

            <div class="sidebar-brand-text min-w-0">
                <p class="truncate font-semibold text-white">Competition KM</p>
                <p class="truncate text-xs text-slate-400">Management Platform</p>
            </div>
        </a>

        {{-- Desktop Collapse --}}
        <button id="sidebar-collapse-button" type="button" class="absolute right-3 top-1/2 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 p-0 text-slate-300 leading-none transition hover:border-blue-500 hover:bg-blue-600 hover:text-white lg:inline-flex" aria-label="พับ Sidebar" aria-expanded="true">
            <svg id="sidebar-collapse-icon" class="block h-4 w-4 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
            </svg>
        </button>

        {{-- Mobile Close --}}
        <button id="sidebar-close-button" type="button" class="absolute right-3 top-1/2 inline-flex h-9 -translate-y-1/2 items-center justify-center rounded-lg px-3 text-slate-400 leading-none transition hover:bg-slate-800 hover:text-white lg:hidden" aria-label="ปิดเมนู">
            <svg class="block h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav flex-1 overflow-y-auto overflow-x-hidden px-4 py-4">

        <p class="sidebar-section-title mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">เมนูหลัก</p>

        <a href="{{ route($dashboardRoute) }}" data-sidebar-tooltip="แดชบอร์ด" class="sidebar-menu-link flex h-9 items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs($dashboardRoute) ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3h8v6h-8V3zM3 21h8v-6H3v6z"/></svg>
            <span class="sidebar-menu-text">แดชบอร์ด</span>
        </a>

        <div class="sidebar-role-menu mt-4">
            <p class="sidebar-section-title mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $roleDisplayName }}</p>

            @if ($roleName === 'Super Admin')

                <div class="space-y-1">
                    <a href="{{ route('superadmin.km.index') }}" data-sidebar-tooltip="จัดการองค์ความรู้" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('superadmin.km.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5v-16ZM20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5v-16Z"/></svg>
                        <span class="sidebar-menu-text">จัดการองค์ความรู้</span>
                    </a>

                    <a href="{{ route('superadmin.createUser') }}" data-sidebar-tooltip="จัดการผู้ใช้งาน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('superadmin.createUser') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.12a7.5 7.5 0 0115 0A17.93 17.93 0 0112 21.75c-2.68 0-5.22-.59-7.5-1.63zM19.5 8.25v4.5M21.75 10.5h-4.5"/></svg>
                        <span class="sidebar-menu-text">จัดการผู้ใช้งาน</span>
                    </a>

                    <a href="{{ route('superadmin.categories.create') }}" data-sidebar-tooltip="จัดการประเภทการแข่งขัน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('superadmin.categories.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h7M16 19h6M19 16v6"/></svg>
                        <span class="sidebar-menu-text">จัดการประเภทการแข่งขัน</span>
                    </a>

                    <a href="{{ route('superadmin.templates.index') }}" data-sidebar-tooltip="จัดการ ช่างรับผลงาน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('superadmin.templates.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5.25H6.75A2.25 2.25 0 004.5 7.5V18a2.25 2.25 0 002.25 2.25h10.5A2.25 2.25 0 0019.5 18V7.5a2.25 2.25 0 00-2.25-2.25H15M9 5.25a3 3 0 006 0M9 5.25a3 3 0 016 0M8.25 11.25h7.5M8.25 15h5.25"/></svg>
                        <span class="sidebar-menu-text">จัดการ ช่องรับผลงาน</span>
                    </a>

                    <a href="{{ route('superadmin.competitions.judges.list') }}" data-sidebar-tooltip="จัดการสิทธิ์การตัดสินการแข่งขัน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('superadmin.competitions.judges.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                        <span class="sidebar-menu-text min-w-0 flex-1 truncate">จัดการสิทธิ์การตัดสินการแข่งขัน</span>
                    </a>
                </div>

            @elseif ($roleName === 'Competition Admin')

                <div class="space-y-1">
                    <a href="{{ route('profile.edit') }}" data-sidebar-tooltip="โปรไฟล์ของฉัน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path stroke-linecap="round" d="M4 21a8 8 0 0 1 16 0"/></svg>
                        <span class="sidebar-menu-text">โปรไฟล์ของฉัน</span>
                    </a>

                    <a href="{{ route('competition-admin.competitions.index') }}" data-sidebar-tooltip="สร้างการแข่งขัน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('competition-admin.createCompitetion') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        <span class="sidebar-menu-text">สร้างการแข่งขัน</span>
                    </a>

                    <a href="{{ route('competition-admin.submissions.index') }}" data-sidebar-tooltip="ผลงานที่ส่ง" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('competition-admin.submissions.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a3 3 0 0 1 6 0v2H9V5ZM8 12h8M8 16h5"/></svg>
                        <span class="sidebar-menu-text">ผลงานที่ส่ง</span>
                    </a>

                    <a href="{{ route('competition-admin.judging-rooms.index') }}" data-sidebar-tooltip="ควบคุมห้องตัดสิน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('competition-admin.judging-rooms.*', 'competition-admin.competitions.judging-room.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M7 9h4m-4 4h7m3-4h.01m-.01 4h.01"/></svg>
                        <span class="sidebar-menu-text">ควบคุมห้องตัดสิน</span>
                    </a>

                    <a href="{{ route('competition-admin.results.index') }}" data-sidebar-tooltip="ผลการแข่งขัน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('competition-admin.results.*', 'competition-admin.competitions.results.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4ZM7 6H4v2a4 4 0 0 0 4 4M17 6h3v2a4 4 0 0 1-4 4"/></svg>
                        <span class="sidebar-menu-text">ผลการแข่งขัน</span>
                    </a>

                    <a href="{{ route('competition-admin.km.index') }}" data-sidebar-tooltip="จัดการผลงาน KM" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('competition-admin.km.*', 'competition-admin.submissions.km.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5v-16ZM20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5v-16Z"/></svg>
                        <span class="sidebar-menu-text">จัดการผลงาน KM</span>
                    </a>
                </div>

            @elseif ($roleName === 'Judge')

                <div class="space-y-1">
                    <a href="{{ route('profile.edit') }}" data-sidebar-tooltip="โปรไฟล์ของฉัน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path stroke-linecap="round" d="M4 21a8 8 0 0 1 16 0"/></svg>
                        <span class="sidebar-menu-text">โปรไฟล์ของฉัน</span>
                    </a>

                    <a href="{{ route('judge.judging-rooms.index') }}" data-sidebar-tooltip="ห้องตัดสิน" class="sidebar-menu-link flex h-9 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium transition {{ request()->routeIs('judge.judging-rooms.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a3 3 0 0 1 6 0v2H9V5Zm0 8 2 2 4-4"/></svg>
                        <span class="sidebar-menu-text">ห้องตัดสิน</span>
                    </a>

                    <a href="#" data-sidebar-tooltip="ประวัติการให้คะแนน — เร็ว ๆ นี้" class="sidebar-menu-link flex h-9 w-full cursor-not-allowed items-center gap-3 rounded-xl px-3 text-sm font-medium text-slate-500">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 3"/></svg>
                        <span class="sidebar-menu-text">ประวัติการให้คะแนน</span>
                        <span class="sidebar-menu-extra ml-auto text-xs">เร็ว ๆ นี้</span>
                    </a>
                </div>

            @endif
        </div>
    </nav>

    {{-- User --}}
    <div class="sidebar-user-wrap shrink-0 border-t border-slate-800 p-4">
        <div class="sidebar-user flex items-center gap-3 rounded-xl bg-slate-900 p-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 font-semibold text-white">
                {{ strtoupper(substr($currentUser?->username ?? 'U', 0, 1)) }}
            </div>

            <div class="sidebar-user-text min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ $currentUser?->username ?? 'Unknown' }}</p>
                <p class="truncate text-xs text-slate-400">{{ $roleDisplayName }}</p>
            </div>
        </div>
    </div>
</aside>


<style>
    @media (min-width: 1024px) {
        html.sidebar-collapsed #app-sidebar {
            width: 72px;
        }

        html.sidebar-collapsed #app-sidebar .sidebar-brand-text,
        html.sidebar-collapsed #app-sidebar .sidebar-section-title,
        html.sidebar-collapsed #app-sidebar .sidebar-menu-text,
        html.sidebar-collapsed #app-sidebar .sidebar-menu-extra,
        html.sidebar-collapsed #app-sidebar .sidebar-user-text {
            display: none;
        }

        html.sidebar-collapsed #app-sidebar .sidebar-header {
            height: 96px;
            display: grid;
            place-items: center;
            padding: 0;
        }

        html.sidebar-collapsed #app-sidebar .sidebar-brand {
            display: none;
        }

        html.sidebar-collapsed #app-sidebar #sidebar-collapse-button {
            position: static !important;
            inset: auto !important;
            margin: 0 !important;
            padding: 0;
            translate: none !important;
            transform: none !important;
        }

        html.sidebar-collapsed #app-sidebar #sidebar-collapse-icon {
            transform: rotate(180deg);
        }

        html.sidebar-collapsed #app-sidebar .sidebar-nav {
            display: block;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 16px 12px;
        }

        html.sidebar-collapsed #app-sidebar .sidebar-role-menu {
            margin-top: 16px;
        }

        html.sidebar-collapsed #app-sidebar .sidebar-menu-link {
            justify-content: center;
            gap: 0;
            padding-left: 0;
            padding-right: 0;
        }

        html.sidebar-collapsed #app-sidebar .sidebar-user-wrap {
            padding-left: 12px;
            padding-right: 12px;
        }

        html.sidebar-collapsed #app-sidebar .sidebar-user {
            justify-content: center;
            padding: 8px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('app-sidebar');
        const collapseButton = document.getElementById('sidebar-collapse-button');
        const desktop = window.matchMedia('(min-width: 1024px)');
        const storageKey = 'app-sidebar-collapsed';

        if (!sidebar || !collapseButton) return;

        const isCollapsed = () => {
            return desktop.matches && document.documentElement.classList.contains('sidebar-collapsed');
        };

        const updateButton = () => {
            const collapsed = isCollapsed();

            collapseButton.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            collapseButton.setAttribute('aria-label', collapsed ? 'ขยาย Sidebar' : 'พับ Sidebar');
        };

        const setCollapsed = collapsed => {
            if (!desktop.matches) {
                document.documentElement.classList.remove('sidebar-collapsed');
                updateButton();
                return;
            }

            document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
            localStorage.setItem(storageKey, collapsed ? '1' : '0');

            updateButton();
        };

        updateButton();

        collapseButton.addEventListener('click', () => {
            setCollapsed(!isCollapsed());
        });

        const tooltip = document.createElement('div');
        tooltip.className = 'pointer-events-none fixed z-[9999] hidden whitespace-nowrap rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs font-medium text-white shadow-xl';
        document.body.appendChild(tooltip);

        document.querySelectorAll('#app-sidebar [data-sidebar-tooltip]').forEach(item => {
            item.addEventListener('mouseenter', () => {
                if (!isCollapsed()) return;

                const text = item.dataset.sidebarTooltip;
                if (!text) return;

                const rect = item.getBoundingClientRect();

                tooltip.textContent = text;
                tooltip.style.left = `${rect.right + 10}px`;
                tooltip.style.top = `${rect.top + rect.height / 2}px`;
                tooltip.style.transform = 'translateY(-50%)';
                tooltip.classList.remove('hidden');
            });

            item.addEventListener('mouseleave', () => {
                tooltip.classList.add('hidden');
            });
        });

        desktop.addEventListener('change', event => {
            tooltip.classList.add('hidden');

            if (event.matches) {
                const collapsed = localStorage.getItem(storageKey) === '1';
                document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
            } else {
                document.documentElement.classList.remove('sidebar-collapsed');
            }

            updateButton();
        });
    });
</script>