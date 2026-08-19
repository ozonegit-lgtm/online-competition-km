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
    class="fixed inset-y-0 left-0 z-50 flex w-60 -translate-x-full flex-col
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
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-3
                        text-sm font-medium transition
                        {{ request()->routeIs('superadmin.createUser')
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0
                            3.75 3.75 0 017.5 0zM4.5 20.12a7.5
                            7.5 0 0115 0A17.93 17.93 0 0112
                            21.75c-2.68 0-5.22-.59-7.5-1.63z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 8.25v4.5M21.75 10.5h-4.5"
                        />
                    </svg>

                    <span>จัดการผู้ใช้งาน</span>
                </a>
                    <a
                        href="{{ route('superadmin.categories.create') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3
                            text-sm font-medium transition
                            {{ request()->routeIs('superadmin.categories.*')
                                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h7"/>
                            <path d="M16 19h6"/>
                            <path d="M19 16v6"/>
                        </svg>

                        <span>จัดการประเภทการแข่งขัน</span>
                    </a>
                    <a
                        href="{{ route('superadmin.templates.index') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3
                            text-sm font-medium transition
                            {{ request()->routeIs('superadmin.templates.*')
                                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 shrink-0"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5.25H6.75A2.25 2.25 0 004.5 7.5v10.5a2.25
                                2.25 0 002.25 2.25h10.5A2.25 2.25 0 0019.5
                                18V7.5a2.25 2.25 0 00-2.25-2.25H15M9
                                5.25a3 3 0 006 0M9 5.25a3 3 0 016 0M8.25
                                11.25h7.5M8.25 15h5.25"
                            />
                        </svg>

                        <span>จัดการ Form-Template</span>
                    </a>
                    <a
                        href="{{ route('superadmin.competitions.judges.list') }}"
                        data-tooltip="จัดการสิทธิ์การตัดสินการแข่งขัน"
                        class="sidebar-tooltip-trigger flex w-full items-center gap-3 rounded-xl px-3 py-3
                            text-sm font-medium transition
                            {{ request()->routeIs('superadmin.competitions.judges.*')
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
                            <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 00-3-3.87"/>
                            <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>

                        <span class="min-w-0 flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
                            จัดการสิทธิ์การตัดสินการแข่งขัน
                        </span>
                    </a>

                </div>

            @elseif ($roleName === 'Competition Admin')

                <div class="space-y-1">

                    <a
                        href="{{ route('profile.edit') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
                        {{ request()->routeIs('profile.*')
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">

                        <svg
                            class="h-5 w-5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M20 21a8 8 0 0 0-16 0" />

                            <circle
                                cx="12"
                                cy="7"
                                r="4" />
                        </svg>

                        <span>โปรไฟล์ของฉัน</span>
                    </a>
                    <a
                        href="{{ route('competition-admin.competitions.index') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
                        {{ request()->routeIs('competition-admin.createCompitetion')
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
                            <path d="M12 5v14M5 12h14"/>
                        </svg>

                        <span>สร้างการแข่งขัน</span>
                    </a>

                    <a
                        href="{{ route('competition-admin.submissions.index') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition
                        {{ request()->routeIs('competition-admin.submissions.*')
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-950/30'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg
                            class="h-5 w-5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5a3 3 0 0 1 6 0v2H9V5Z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 12h8M8 16h5"
                            />
                        </svg>

                        <span>ผลงานที่ส่ง</span>
                    </a>


                    <a
                        href="{{ route('competition-admin.judging-rooms.index') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3
                            text-sm font-medium transition
                            {{ request()->routeIs(
                                    'competition-admin.judging-rooms.*',
                                    'competition-admin.competitions.judging-room.*'
                            )
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
                            <rect
                                x="3"
                                y="5"
                                width="18"
                                height="14"
                                rx="2"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M7 9h4m-4 4h7m3-4h.01m-.01 4h.01"
                            />
                        </svg>

                        <span>ควบคุมห้องตัดสิน</span>
                    </a>
                </div>

            @elseif ($roleName === 'Judge')

                <div class="space-y-1">
                    <a
                        href="{{ route('profile.edit') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3
                            text-sm font-medium transition
                            {{ request()->routeIs('profile.*')
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
                            <circle cx="12" cy="7" r="4" />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 21a8 8 0 0 1 16 0"
                            />
                        </svg>

                        <span>โปรไฟล์ของฉัน</span>
                    </a>
                    <a
                        href="{{ route('judge.judging-rooms.index') }}"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3
                            text-sm font-medium transition
                            {{ request()->routeIs('judge.judging-rooms.*')
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
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5a3 3 0 0 1 6 0v2H9V5Zm0 8 2 2 4-4"
                            />
                        </svg>

                        <span>ห้องตัดสิน</span>
                    </a>

                    <a
                        href="#"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium
                            text-slate-500 cursor-not-allowed"
                    >
                        <svg
                            class="h-5 w-5 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 7v5l3 3"
                            />
                        </svg>

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
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tooltip = document.createElement('div');
    tooltip.className = `
        pointer-events-none fixed z-[9999] w-max max-w-xs rounded-lg
        bg-slate-900 px-3 py-2 text-xs font-normal text-white shadow-xl
        opacity-0 scale-95 transition-all duration-150 ease-out
    `.trim().replace(/\s+/g, ' ');
    document.body.appendChild(tooltip);

    let hideTimeout;

    document.querySelectorAll('.sidebar-tooltip-trigger').forEach((trigger) => {
        trigger.addEventListener('mouseenter', () => {
            clearTimeout(hideTimeout);
            const text = trigger.dataset.tooltip;
            if (!text) return;

            tooltip.textContent = text;
            tooltip.style.opacity = '0';

            // วางตำแหน่งชั่วคราวเพื่อวัดขนาด แล้วค่อยจัดจริง
            const rect = trigger.getBoundingClientRect();
            tooltip.style.left = `${rect.right + 10}px`;
            tooltip.style.top = `${rect.top + rect.height / 2}px`;
            tooltip.style.transform = 'translateY(-50%) scale(0.95)';

            requestAnimationFrame(() => {
                tooltip.style.opacity = '1';
                tooltip.style.transform = 'translateY(-50%) scale(1)';
            });
        });

        trigger.addEventListener('mouseleave', () => {
            hideTimeout = setTimeout(() => {
                tooltip.style.opacity = '0';
                tooltip.style.transform = 'translateY(-50%) scale(0.95)';
            }, 50);
        });
    });
});
</script>