@if ($settings->hero_enabled)
<section id="hero" class="relative h-[290px] scroll-mt-[60px] overflow-hidden bg-[#123b5d] sm:h-[330px] md:h-[360px] lg:h-[420px] lg:scroll-mt-16">
    @if ($settings->hero_image_path)
        <img
            src="{{ route('knowledge.assets.show', 'hero') }}"
            alt="{{ $settings->hero_title }}"
            class="absolute inset-0 h-full w-full object-cover"
        >
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-[#123b5d] via-[#175076] to-teal-700" aria-hidden="true"></div>
    @endif

    <div class="absolute inset-0 bg-gradient-to-r from-[#08283f]/95 via-[#123b5d]/75 to-[#123b5d]/20"></div>

    <div class="relative mx-auto flex h-full max-w-7xl items-center px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <div class="max-w-2xl text-white">
            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-teal-200 sm:text-xs">
                Knowledge Management
            </p>

            <h1 class="mt-2 line-clamp-2 break-words text-2xl font-bold leading-tight tracking-tight sm:text-3xl lg:text-4xl">
                {{ $settings->hero_title }}
            </h1>

            <div class="mt-3 h-0.5 w-10 bg-amber-400 sm:w-12"></div>

            @if ($settings->hero_description)
                <p class="mt-3 line-clamp-2 max-w-xl break-words whitespace-pre-line text-sm leading-6 text-slate-100 sm:mt-4 sm:line-clamp-3 sm:text-base sm:leading-7">
                    {{ $settings->hero_description }}
                </p>
            @endif

            @if ($settings->hero_button_enabled && $settings->hero_button_label && $settings->hero_button_url)
                @php
                    $heroExternal = str_starts_with($settings->hero_button_url, 'http://')
                        || str_starts_with($settings->hero_button_url, 'https://');
                @endphp

                <a
                    href="{{ $settings->hero_button_url }}"
                    @if ($heroExternal)
                        target="_blank"
                        rel="noopener noreferrer"
                    @endif
                    class="mt-5 inline-flex h-10 items-center justify-center gap-2 bg-teal-600 px-4 text-sm font-semibold text-white transition hover:bg-teal-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-300 focus-visible:ring-offset-2 focus-visible:ring-offset-[#123b5d]"
                >
                    {{ $settings->hero_button_label }}
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14"></path>
                        <path d="m13 6 6 6-6 6"></path>
                    </svg>
                </a>
            @endif
        </div>
    </div>

    <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-teal-500 via-teal-400 to-amber-400"></div>
</section>
@endif
