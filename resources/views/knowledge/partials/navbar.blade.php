<header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="h-0.5 bg-gradient-to-r from-teal-500 via-teal-400 to-amber-400"></div>
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="เมนูหลัก">
        <div class="flex h-[58px] items-center justify-between gap-3 sm:h-[60px] lg:h-[62px]">
            {{-- Brand --}}
            <a href="{{ route('knowledge.index') }}" class="flex min-w-0 items-center gap-2.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2">
                @if ($settings->site_logo_path)
                    <img src="{{ route('knowledge.assets.show', 'logo') }}" alt="โลโก้ {{ $settings->site_name }}" class="h-10 w-10 shrink-0 object-contain lg:h-11 lg:w-11">
                @else
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center bg-[#123b5d] text-xs font-bold text-white lg:h-11 lg:w-11">KM</span>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-base font-bold leading-tight text-[#123b5d]">{{ $settings->site_name }}</p>
                    <p class="mt-0.5 hidden text-[10px] font-medium uppercase tracking-[0.12em] text-teal-600 sm:block">Knowledge Management</p>
                </div>
            </a>

            {{-- Desktop Menu --}}
            <div class="hidden h-full items-center lg:flex">
                @foreach ($links->get('navbar', collect()) as $item)
                    <a href="{{ $item->url }}" target="{{ $item->target }}" @if ($item->target === '_blank') rel="noopener noreferrer" @endif class="group relative flex h-full items-center px-3 text-sm font-semibold text-slate-600 transition hover:text-teal-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-teal-500 xl:px-4">
                        {{ $item->label }}
                        <span class="absolute bottom-0 left-1/2 h-0.5 w-0 -translate-x-1/2 bg-teal-500 transition-all duration-200 group-hover:w-7"></span>
                    </a>
                @endforeach
            </div>

            {{-- Mobile Menu --}}
            <details class="group relative block shrink-0 lg:hidden">
                <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center border border-slate-200 bg-white text-[#123b5d] transition hover:border-teal-400 hover:text-teal-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    <span class="sr-only">เปิดเมนู</span>
                    <svg class="h-5 w-5 group-open:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16"></path>
                        <path d="M4 12h16"></path>
                        <path d="M4 18h16"></path>
                    </svg>
                    <svg class="hidden h-5 w-5 group-open:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 6l12 12"></path>
                        <path d="M18 6 6 18"></path>
                    </svg>
                </summary>

                <div class="absolute right-0 top-[49px] w-[min(18rem,calc(100vw-2rem))] border border-slate-200 bg-white shadow-lg">
                    <div class="border-t-[3px] border-teal-500 py-1.5">
                        @foreach ($links->get('navbar', collect()) as $item)
                            <a href="{{ $item->url }}" target="{{ $item->target }}" @if ($item->target === '_blank') rel="noopener noreferrer" @endif class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition last:border-b-0 hover:bg-teal-50 hover:text-teal-700">
                                <span class="min-w-0 truncate">{{ $item->label }}</span>
                                <svg class="ml-3 h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </details>
        </div>
    </nav>
</header>
