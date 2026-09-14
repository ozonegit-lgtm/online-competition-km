@if ($settings->books_enabled)
<section id="books" class="scroll-mt-[60px] border-y border-slate-200 bg-slate-50 lg:scroll-mt-16">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">
        {{-- Header --}}
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-teal-600">E-BOOK KNOWLEDGE</p>
            <h2 class="mt-2 text-xl font-bold leading-tight text-[#123b5d] sm:text-2xl lg:text-3xl">{{ $settings->books_title }}</h2>
            <div class="mx-auto mt-3 h-0.5 w-10 bg-amber-400"></div>
            @if ($settings->books_description)
                <p class="mx-auto mt-3 max-w-2xl whitespace-pre-line text-sm leading-6 text-slate-600 sm:text-base sm:leading-7">{{ $settings->books_description }}</p>
            @endif
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('knowledge.index') }}" class="mx-auto mt-6 border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(220px,1fr)_200px_140px_auto]">
                <label class="relative">
                    <span class="sr-only">ค้นหา E-Book</span>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="ค้นหา E-Book..." class="h-10 w-full border border-slate-300 bg-white pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                </label>

                <label>
                    <span class="sr-only">หมวดหมู่</span>
                    <select name="category" class="h-10 w-full border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                        <option value="">ทุกหมวดหมู่</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="sr-only">ปีที่เผยแพร่</span>
                    <input type="number" min="1" max="65535" name="year" value="{{ request('year') }}" placeholder="ปีเผยแพร่" class="h-10 w-full border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                </label>

                <div class="flex gap-2">
                    <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center gap-2 bg-teal-600 px-4 text-sm font-semibold text-white transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                        ค้นหา
                    </button>
                    @if (request()->hasAny(['q', 'category', 'year']))
                        <a href="{{ route('knowledge.index') }}#books" class="inline-flex h-10 items-center justify-center border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50">ล้าง</a>
                    @endif
                </div>
            </div>
        </form>

        {{-- E-Book List --}}
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-5" data-ebook-list>
            @forelse ($knowledgeItems as $ebook)
                <article class="group flex min-w-0 flex-col overflow-hidden border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-teal-300 hover:shadow-md" data-ebook-id="{{ $ebook->id }}">
                    {{-- Cover --}}
                    <a href="{{ route('knowledge.show', $ebook) }}" class="relative block overflow-hidden bg-slate-100">
                        <div class="mx-auto aspect-[3/4] w-full overflow-hidden">
                            @if ($ebook->cover_image_url)
                                <img src="{{ $ebook->cover_image_url }}" alt="ปก {{ $ebook->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy">
                            @else
                                <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-[#123b5d] to-teal-600 px-6 text-center text-white">
                                    <div class="flex h-12 w-12 items-center justify-center border border-white/30 bg-white/10">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path>
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-base font-bold">E-BOOK</p>
                                    <p class="mt-1 text-xs text-white/70">ไม่มีรูปปก</p>
                                </div>
                            @endif
                        </div>

                        @if ($ebook->knowledgeCategory)
                            <span class="absolute left-2.5 top-2.5 max-w-[calc(100%-1.25rem)] truncate bg-teal-600 px-2.5 py-1 text-[10px] font-semibold text-white">{{ $ebook->knowledgeCategory->name }}</span>
                        @endif
                    </a>

                    {{-- Content --}}
                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                            @if ($ebook->publication_year)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                                        <path d="M16 3v4M8 3v4M3 10h18"></path>
                                    </svg>
                                    {{ $ebook->publication_year }}
                                </span>
                            @endif

                            @if ($ebook->volume || $ebook->issue)
                                <span>
                                    @if ($ebook->volume) เล่ม {{ $ebook->volume }} @endif
                                    @if ($ebook->volume && $ebook->issue) · @endif
                                    @if ($ebook->issue) ฉบับ {{ $ebook->issue }} @endif
                                </span>
                            @endif
                        </div>

                        <h3 class="mt-2.5 line-clamp-2 text-base font-bold leading-6 text-[#123b5d] transition group-hover:text-teal-700">
                            <a href="{{ route('knowledge.show', $ebook) }}">{{ $ebook->title }}</a>
                        </h3>

                        @if ($ebook->summary)
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ $ebook->summary }}</p>
                        @endif

                        <div class="mt-auto pt-4">
                            <div class="border-t border-slate-100 pt-3">
                                <a href="{{ route('knowledge.show', $ebook) }}" class="inline-flex items-center text-sm font-semibold text-teal-700 transition hover:text-teal-900">
                                    อ่านเพิ่มเติม →
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="border border-dashed border-slate-300 bg-white px-6 py-10 text-center sm:col-span-2 lg:col-span-3 xl:col-span-4">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center bg-teal-50 text-teal-600">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path>
                        </svg>
                    </div>
                    <p class="mt-4 font-semibold text-[#123b5d]">ยังไม่มี E-Book ที่ตรงกับเงื่อนไข</p>
                    <p class="mt-1 text-sm text-slate-500">ลองเปลี่ยนคำค้นหา หมวดหมู่ หรือปีเผยแพร่</p>
                </div>
            @endforelse
        </div>

        @if ($knowledgeItems->hasPages())
            <div class="mt-8">{{ $knowledgeItems->links() }}</div>
        @endif
    </div>
</section>
@endif
