@php
    $alerts = [];

    if (session('success')) {
        $alerts[] = [
            'type' => 'success',
            'title' => 'ดำเนินการสำเร็จ',
            'message' => session('success'),
        ];
    }

    if (session('error')) {
        $alerts[] = [
            'type' => 'error',
            'title' => 'เกิดข้อผิดพลาด',
            'message' => session('error'),
        ];
    }

    if (session('warning')) {
        $alerts[] = [
            'type' => 'warning',
            'title' => 'โปรดตรวจสอบ',
            'message' => session('warning'),
        ];
    }

    if (session('info')) {
        $alerts[] = [
            'type' => 'info',
            'title' => 'แจ้งให้ทราบ',
            'message' => session('info'),
        ];
    }

    if ($errors->any()) {
        $alerts[] = [
            'type' => 'error',
            'title' => 'ข้อมูลไม่ถูกต้อง',
            'message' => $errors->first(),
        ];
    }
@endphp

@if (count($alerts))
    <div
        class="pointer-events-none fixed bottom-5 left-0 right-0 z-[100]
               flex flex-col items-center gap-3 px-4 lg:left-80"
        aria-live="polite"
        aria-atomic="true"
    >
        @foreach ($alerts as $alert)
            @php
                $styles = match ($alert['type']) {
                    'success' => [
                        'iconBox' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
                        'dot' => 'bg-emerald-500',
                    ],
                    'error' => [
                        'iconBox' => 'bg-red-50 text-red-600 ring-red-100',
                        'dot' => 'bg-red-500',
                    ],
                    'warning' => [
                        'iconBox' => 'bg-amber-50 text-amber-600 ring-amber-100',
                        'dot' => 'bg-amber-500',
                    ],
                    default => [
                        'iconBox' => 'bg-sky-50 text-sky-600 ring-sky-100',
                        'dot' => 'bg-sky-500',
                    ],
                };
            @endphp

            <div
                data-app-toast
                role="alert"
                class="pointer-events-auto relative flex w-full max-w-[380px]
                       translate-y-6 items-start gap-3 overflow-hidden
                       rounded-2xl border border-slate-200 bg-white p-4
                       opacity-0 shadow-[0_12px_35px_rgba(15,23,42,0.14)]
                       transition-all duration-300"
            >
                {{-- Icon --}}
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center
                           rounded-xl ring-1 {{ $styles['iconBox'] }}"
                >
                    @if ($alert['type'] === 'success')
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m8 12 2.5 2.5L16 9"
                            />
                        </svg>
                    @elseif ($alert['type'] === 'error')
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path
                                stroke-linecap="round"
                                d="M12 8v5m0 3h.01"
                            />
                        </svg>
                    @elseif ($alert['type'] === 'warning')
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.25"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10.3 4.2 3 17a2 2 0 0 0 1.7 3h14.6a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z"
                            />
                            <path
                                stroke-linecap="round"
                                d="M12 9v4m0 3h.01"
                            />
                        </svg>
                    @else
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.25"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path
                                stroke-linecap="round"
                                d="M12 11v5m0-8h.01"
                            />
                        </svg>
                    @endif
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1 pt-0.5">
                    <p class="text-sm font-semibold text-slate-800">
                        {{ $alert['title'] }}
                    </p>

                    <p class="mt-1 break-words text-sm leading-5 text-slate-500">
                        {{ $alert['message'] }}
                    </p>
                </div>

                {{-- Close --}}
                <button
                    type="button"
                    data-toast-close
                    class="shrink-0 rounded-lg p-1.5 text-slate-400
                           transition hover:bg-slate-100 hover:text-slate-700
                           focus:outline-none focus:ring-2 focus:ring-slate-200"
                    aria-label="ปิดข้อความแจ้งเตือน"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            d="m6 6 12 12M18 6 6 18"
                        />
                    </svg>
                </button>

                {{-- Progress --}}
                <div
                    data-toast-progress
                    class="absolute bottom-0 left-0 h-0.5 w-full
                           origin-left {{ $styles['dot'] }}"
                ></div>
            </div>
        @endforeach
    </div>

    @once
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('[data-app-toast]')
                        .forEach((toast, index) => {
                            const closeButton =
                                toast.querySelector('[data-toast-close]');

                            const progress =
                                toast.querySelector('[data-toast-progress]');

                            const duration = 4500;
                            let timer;

                            const closeToast = () => {
                                clearTimeout(timer);

                                toast.classList.add(
                                    'translate-y-6',
                                    'opacity-0'
                                );

                                setTimeout(() => toast.remove(), 300);
                            };

                            setTimeout(() => {
                                toast.classList.remove(
                                    'translate-y-6',
                                    'opacity-0'
                                );

                                progress?.animate(
                                    [
                                        { transform: 'scaleX(1)' },
                                        { transform: 'scaleX(0)' }
                                    ],
                                    {
                                        duration,
                                        easing: 'linear',
                                        fill: 'forwards'
                                    }
                                );
                            }, index * 120);

                            timer = setTimeout(
                                closeToast,
                                duration + (index * 120)
                            );

                            closeButton?.addEventListener(
                                'click',
                                closeToast
                            );
                        });
                });
            </script>
        @endpush
    @endonce
@endif