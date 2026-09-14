@if ($settings->about_enabled)
<section id="about" class="scroll-mt-[60px] bg-white lg:scroll-mt-16">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">
        <div class="{{ $settings->about_image_path ? 'grid items-center gap-6 md:grid-cols-[minmax(0,0.82fr)_minmax(0,1fr)] lg:gap-8' : 'mx-auto max-w-3xl' }}">
            @if ($settings->about_image_path)
                <div class="min-w-0 overflow-hidden border border-slate-200 bg-slate-100">
                    <img
                        src="{{ route('knowledge.assets.show', 'about') }}"
                        alt="{{ $settings->about_title }}"
                        class="h-[240px] w-full object-cover sm:h-[300px] md:h-[320px] lg:h-[330px]"
                        loading="lazy"
                    >
                </div>
            @endif

            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-teal-600">
                    Knowledge Management
                </p>

                <h2 class="mt-2 text-xl font-bold leading-tight text-[#123b5d] sm:text-2xl lg:text-3xl">
                    {{ $settings->about_title }}
                </h2>

                <div class="mt-3 h-0.5 w-10 bg-amber-400"></div>

                @if ($settings->about_content)
                    <div class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600 sm:text-base">{{ $settings->about_content }}</div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif
