@if ($settings->footer_enabled)
<footer class="border-t-[3px] border-teal-500 bg-[#123b5d] text-slate-300">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <div class="grid gap-7 md:grid-cols-2 lg:grid-cols-[1.3fr_1fr_0.8fr] lg:gap-8">
            {{-- Organization --}}
            <div>
                @if ($settings->footer_show_logo && $settings->footer_logo_path)
                    <img src="{{ route('knowledge.assets.show', 'footer-logo') }}" alt="โลโก้ {{ $settings->footer_organization_name ?: $settings->site_name }}" class="mb-4 h-12 max-w-48 object-contain object-left">
                @endif
                <h2 class="text-base font-bold leading-snug text-white sm:text-lg">
                    {{ $settings->footer_organization_name ?: $settings->site_name }}
                </h2>
                <div class="mt-2.5 h-0.5 w-9 bg-amber-400"></div>
                @if ($settings->footer_show_description && $settings->footer_description)
                    <p class="mt-3 max-w-md whitespace-pre-line text-sm leading-6 text-slate-300">
                        {{ $settings->footer_description }}
                    </p>
                @endif
            </div>

            {{-- Contact --}}
            <div class="text-sm">
                <h3 class="text-base font-bold text-white">ข้อมูลติดต่อ</h3>
                <div class="mt-2 h-0.5 w-8 bg-teal-400"></div>
                <div class="mt-4 space-y-3">
                    @if ($settings->footer_show_address && $settings->footer_address)
                        <div class="flex gap-3">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-teal-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                                <circle cx="12" cy="10" r="2.5"></circle>
                            </svg>
                            <p class="whitespace-pre-line leading-6 text-slate-300">{{ $settings->footer_address }}</p>
                        </div>
                    @endif
                    @if ($settings->footer_show_phone && $settings->footer_phone)
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 shrink-0 text-teal-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"></path>
                            </svg>
                            <a href="tel:{{ $settings->footer_phone }}" class="transition hover:text-teal-300">{{ $settings->footer_phone }}</a>
                        </div>
                    @endif
                    @if ($settings->footer_show_email && $settings->footer_email)
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-teal-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                <path d="m3 7 9 6 9-6"></path>
                            </svg>
                            <a href="mailto:{{ $settings->footer_email }}" class="break-all transition hover:text-teal-300">{{ $settings->footer_email }}</a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Links --}}
            <div class="text-sm">
                <h3 class="text-base font-bold text-white">เมนูเว็บไซต์</h3>
                <div class="mt-2 h-0.5 w-8 bg-teal-400"></div>
                <nav class="mt-4 space-y-2.5">
                    @foreach ($links->get('footer', collect())->concat($links->get('social', collect())) as $item)
                        <a href="{{ $item->url }}" target="{{ $item->target }}" @if ($item->target === '_blank') rel="noopener noreferrer" @endif class="group flex items-center gap-2 text-slate-300 transition hover:text-white">
                            <svg class="h-3.5 w-3.5 shrink-0 text-teal-400 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                            <span>{{ $item->label }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="border-t border-white/10 bg-[#0d304c]">
        <div class="mx-auto flex max-w-7xl flex-col gap-1.5 px-4 py-3 text-center text-xs text-slate-400 sm:px-6 md:flex-row md:items-center md:justify-between md:text-left lg:px-8">
            <p>{{ $settings->footer_copyright ?: '© '.now()->year.' '.$settings->site_name }}</p>
            <p>Knowledge Management & E-Book</p>
        </div>
    </div>
</footer>
@endif
