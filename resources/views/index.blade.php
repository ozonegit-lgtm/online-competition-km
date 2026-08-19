@extends('layouts.km')

@section('title', 'คลังผลงานการประกวด')

@section('content')

<div class="min-h-screen bg-slate-50">

    {{-- =========================================================
        HERO
    ========================================================== --}}
    <section
        class="relative isolate overflow-hidden
        bg-gradient-to-br from-emerald-50 via-white to-emerald-50"
    >

        {{-- Background glow --}}
        <div
            class="pointer-events-none absolute -left-32 top-20
            h-[420px] w-[420px] rounded-full
            bg-emerald-100/70 blur-2xl"
        ></div>

        <div
            class="pointer-events-none absolute -right-32 -top-20
            h-[420px] w-[420px] rounded-full
            bg-emerald-100/80 blur-2xl"
        ></div>

        {{-- Decorative dots left --}}
        <div
            class="pointer-events-none absolute left-14 top-56 hidden
            h-24 w-24 opacity-50 lg:block"
        >
            <div class="grid grid-cols-4 gap-4">
                @for ($i = 0; $i < 16; $i++)
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                @endfor
            </div>
        </div>

        {{-- Decorative dots right --}}
        <div
            class="pointer-events-none absolute right-14 top-52 hidden
            h-24 w-24 opacity-50 lg:block"
        >
            <div class="grid grid-cols-4 gap-4">
                @for ($i = 0; $i < 16; $i++)
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                @endfor
            </div>
        </div>

        {{-- Decorative circles --}}
        <div
            class="pointer-events-none absolute -left-32 top-24
            hidden h-80 w-80 rounded-full border-[80px]
            border-emerald-100/50 lg:block"
        ></div>

        <div
            class="pointer-events-none absolute -right-24 -top-24
            hidden h-80 w-80 rounded-full border-[70px]
            border-emerald-100/60 lg:block"
        ></div>

        {{-- HERO CONTENT --}}
        <div
            class="relative mx-auto flex min-h-[340px] max-w-7xl
            items-center justify-center px-4 py-10
            sm:px-6 lg:px-8"
        >

            <div class="w-full max-w-4xl text-center">

                {{-- Badge --}}
                <div
                    class="mb-5 inline-flex items-center gap-2 rounded-full
                    border border-emerald-400 bg-white/90 px-3 py-1.5
                    text-xs font-medium text-emerald-700 shadow-sm"
                >
                    <span
                        class="h-2 w-2 rounded-full bg-emerald-500"
                    ></span>

                    คลังผลงานการประกวด
                </div>


                {{-- Heading --}}
                <h1
                    class="mx-auto max-w-4xl text-3xl font-extrabold
                    leading-tight tracking-tight text-slate-900
                    sm:text-4xl lg:text-5xl"
                >
                    ผลงานที่สร้างแรงบันดาลใจ

                    <span
                        class="mt-2 block text-emerald-600"
                    >
                        และองค์ความรู้
                    </span>
                </h1>


                {{-- Description --}}
                <p
                    class="mx-auto mt-4 max-w-2xl text-sm
                    leading-7 text-slate-500 sm:text-base"
                >
                    รวบรวมผลงานจากการแข่งขันที่ผ่านการตัดสินและตรวจสอบแล้ว
                    <br class="hidden sm:block">
                    เพื่อให้คุณค้นหา เรียนรู้ และนำแนวคิดดี ๆ ไปต่อยอดได้ง่ายขึ้น
                </p>


                {{-- Search --}}
                <form
                    method="GET"
                    action="{{ route('home') }}"
                    class="mx-auto mt-6 w-full max-w-2xl"
                >

                    <div
                        class="flex flex-col gap-3 sm:flex-row"
                    >

                        {{-- Input --}}
                        <div class="relative flex-1">

                            <svg
                                class="pointer-events-none absolute left-4
                                top-1/2 h-4 w-4 -translate-y-1/2
                                text-slate-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>

                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="ค้นหาผลงาน หรือชื่อการแข่งขัน..."
                                class="h-12 w-full rounded-2xl border
                                border-slate-200 bg-white pl-11 pr-5
                                text-sm text-slate-700 shadow-sm
                                outline-none transition
                                placeholder:text-slate-400
                                focus:border-emerald-500
                                focus:ring-4
                                focus:ring-emerald-500/10"
                            >

                        </div>


                        {{-- Button --}}
                        <button
                            type="submit"
                            class="h-12 shrink-0 rounded-2xl
                            bg-emerald-600 px-7 text-sm font-bold
                            text-white shadow-sm transition
                            hover:bg-emerald-700 hover:shadow-md
                            focus:outline-none focus:ring-4
                            focus:ring-emerald-500/20"
                        >
                            ค้นหาผลงาน
                        </button>

                    </div>

                </form>


                {{-- Helper --}}
                <p
                    class="mt-3 text-xs text-slate-400"
                >
                    ค้นหาจากชื่อผลงาน ชื่อทีม หรือชื่อการแข่งขัน
                </p>

            </div>

        </div>


        {{-- Bottom wave --}}
        <div
            class="pointer-events-none absolute -bottom-1 left-0
            h-20 w-full overflow-hidden"
        >
            <svg
                class="absolute bottom-0 h-full w-full"
                viewBox="0 0 1440 100"
                preserveAspectRatio="none"
                fill="none"
            >
                <path
                    d="M0 55C180 5 330 10 520 45C720 82 860 95 1060 55C1230 20 1330 20 1440 45V100H0V55Z"
                    fill="#f8fafc"
                />
            </svg>
        </div>

    </section>


    {{-- =========================================================
        MAIN CONTENT
    ========================================================== --}}
    <main
        class="relative mx-auto w-full max-w-7xl
        px-4 py-8 sm:px-6 lg:px-8"
    >


        {{-- =====================================================
            CATEGORIES
        ====================================================== --}}
        <section>

            <div class="mb-4">

                <p
                    class="text-xs font-semibold text-emerald-600"
                >
                    สำรวจผลงาน
                </p>

                <h2
                    class="mt-1 text-xl font-bold text-slate-900"
                >
                    หมวดหมู่
                </h2>

            </div>


            <div
                class="flex gap-2 overflow-x-auto pb-2"
            >

                {{-- ALL --}}
                <a
                    href="{{ route('home') }}"
                    class="shrink-0 rounded-full border px-4 py-2
                    text-sm font-medium transition
                    {{ !request('category')
                        ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700' }}"
                >
                    ทั้งหมด
                </a>


                {{-- CATEGORIES --}}
                @foreach ($categories ?? [] as $category)

                    <a
                        href="{{ route('home', ['category' => $category->id]) }}"
                        class="shrink-0 rounded-full border px-4 py-2
                        text-sm font-medium transition
                        {{ request('category') == $category->id
                            ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm'
                            : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700' }}"
                    >
                        {{ $category->category_name }}
                    </a>

                @endforeach

            </div>

        </section>


        {{-- =====================================================
            FEATURED WORKS
        ====================================================== --}}
        @if (($featuredWorks ?? collect())->isNotEmpty())

            <section class="mt-10">

                <div class="mb-5">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9 items-center
                            justify-center rounded-xl
                            bg-yellow-100 text-yellow-700"
                        >

                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M8 21h8"/>
                                <path d="M12 17v4"/>
                                <path d="M7 4h10v5a5 5 0 01-10 0V4Z"/>
                                <path d="M7 6H4a1 1 0 00-1 1v1a4 4 0 004 4"/>
                                <path d="M17 6h3a1 1 0 011 1v1a4 4 0 01-4 4"/>
                            </svg>

                        </div>

                        <div>

                            <p
                                class="text-xs font-medium
                                text-yellow-600"
                            >
                                ผลงานที่ได้รับรางวัล
                            </p>

                            <h2
                                class="text-xl font-bold
                                text-slate-900"
                            >
                                ผลงานเด่น
                            </h2>

                        </div>

                    </div>

                </div>


                <div
                    class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
                >

                    @foreach ($featuredWorks as $work)

                        @php

                            $image = $work->files->first();

                            $imageUrl = $image
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url(
                                    $image->file_path ?? $image->path
                                )
                                : null;

                            $award = $work->awards->first();

                        @endphp


                        <article
                            class="group overflow-hidden rounded-2xl
                            border border-slate-200 bg-white
                            shadow-sm transition duration-300
                            hover:-translate-y-1 hover:shadow-lg"
                        >

                            <a href="#" class="block">

                                <div
                                    class="relative aspect-[16/10]
                                    overflow-hidden bg-slate-100"
                                >

                                    @if ($imageUrl)

                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $work->project_title }}"
                                            class="h-full w-full object-cover
                                            transition duration-500
                                            group-hover:scale-105"
                                            loading="lazy"
                                        >

                                    @else

                                        <div
                                            class="flex h-full items-center
                                            justify-center text-sm
                                            text-slate-400"
                                        >
                                            ไม่มีรูปภาพ
                                        </div>

                                    @endif


                                    <div
                                        class="absolute left-3 top-3"
                                    >

                                        <span
                                            class="inline-flex items-center
                                            gap-1.5 rounded-full
                                            bg-yellow-400 px-2.5 py-1
                                            text-xs font-bold
                                            text-yellow-950 shadow-sm"
                                        >
                                            🏆
                                            {{ $award->name ?? 'ผลงานรางวัล' }}
                                        </span>

                                    </div>

                                </div>


                                <div class="p-4">

                                    <p
                                        class="mb-1.5 line-clamp-1 text-xs
                                        text-slate-400"
                                    >
                                        {{ $work->competition?->title ?? 'ไม่ระบุการแข่งขัน' }}
                                    </p>

                                    <h3
                                        class="line-clamp-2 text-base
                                        font-bold text-slate-800
                                        transition
                                        group-hover:text-emerald-700"
                                    >
                                        {{ $work->project_title }}
                                    </h3>

                                    @if ($work->project_description)

                                        <p
                                            class="mt-1.5 line-clamp-2
                                            text-sm leading-6
                                            text-slate-500"
                                        >
                                            {{ $work->project_description }}
                                        </p>

                                    @endif


                                    <div
                                        class="mt-3 flex items-center
                                        justify-between border-t
                                        border-slate-100 pt-3"
                                    >

                                        <span
                                            class="text-xs text-slate-400"
                                        >
                                            คะแนน
                                        </span>

                                        <span
                                            class="text-sm font-bold
                                            text-emerald-600"
                                        >
                                            {{ $work->final_score ?? '-' }}
                                        </span>

                                    </div>

                                </div>

                            </a>

                        </article>

                    @endforeach

                </div>

            </section>

        @endif


        {{-- =====================================================
            ALL WORKS
        ====================================================== --}}
        <section class="mt-10">

            <div
                class="mb-5 flex flex-col gap-3
                sm:flex-row sm:items-end
                sm:justify-between"
            >

                <div>

                    <p
                        class="text-xs font-medium
                        text-emerald-600"
                    >
                        คลังผลงาน
                    </p>

                    <h2
                        class="mt-1 text-xl font-bold
                        text-slate-900"
                    >
                        ผลงานทั้งหมด
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        ผลงานที่ผ่านการตรวจสอบและเผยแพร่แล้ว
                    </p>

                </div>


            {{-- SORT --}}
                <form
                    method="GET"
                    action="{{ route('home') }}"
                    id="sort-form"
                >

                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    @if (request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif

                    <input type="hidden" name="sort" id="sort-value" value="{{ request('sort', 'latest') }}">

                    {{-- Custom Dropdown --}}
                    <div class="relative" id="sort-dropdown">

                        @php
                            $sortOptions = [
                                'latest' => ['label' => 'ล่าสุด', 'icon' => 'clock'],
                                'score'  => ['label' => 'คะแนนสูงสุด', 'icon' => 'star'],
                                'title'  => ['label' => 'ชื่อผลงาน', 'icon' => 'text'],
                            ];
                            $currentSort = request('sort', 'latest');
                        @endphp

                        {{-- Trigger button --}}
                        <button
                            type="button"
                            id="sort-trigger"
                            class="flex w-44 items-center justify-between rounded-2xl
                            bg-white px-4 py-2.5 text-sm font-bold text-slate-700
                            shadow-md transition hover:shadow-lg
                            focus:outline-none focus:ring-4 focus:ring-emerald-500/10"
                        >
                            <span id="sort-label">{{ $sortOptions[$currentSort]['label'] }}</span>

                            <svg
                                id="sort-chevron"
                                class="h-4 w-4 text-slate-400 transition-transform duration-200"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                            >
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>

                        {{-- Panel --}}
                        <div
                            id="sort-panel"
                            class="absolute right-0 z-20 mt-2 hidden w-44 overflow-hidden
                            rounded-2xl bg-white p-1.5 shadow-xl ring-1 ring-slate-100"
                        >

                            @foreach ($sortOptions as $value => $option)

                                <button
                                    type="button"
                                    data-value="{{ $value }}"
                                    data-label="{{ $option['label'] }}"
                                    class="sort-option flex w-full items-center justify-between
                                    rounded-xl px-3 py-2.5 text-left text-sm font-semibold
                                    text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700
                                    {{ $currentSort === $value ? 'bg-emerald-50 text-emerald-700' : '' }}"
                                >
                                    {{ $option['label'] }}

                                    @if ($option['icon'] === 'clock')
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="9"/>
                                            <path d="M12 7v5l3 3"/>
                                        </svg>
                                    @elseif ($option['icon'] === 'star')
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2 9.5 8.5 2 9.5l5.5 5-1.5 7.5 6-4 6 4-1.5-7.5 5.5-5-7.5-1L12 2Z"/>
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M4 6h16M4 12h10M4 18h7"/>
                                        </svg>
                                    @endif
                                </button>

                            @endforeach

                        </div>

                    </div>

                </form>

            </div>


            {{-- WORKS --}}
            @if (($works ?? collect())->isNotEmpty())

                <div
                    class="grid gap-4 sm:grid-cols-2
                    lg:grid-cols-3 xl:grid-cols-4"
                >

                    @foreach ($works as $work)

                        @php

                            $image = $work->files->first();

                            $imageUrl = $image
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url(
                                    $image->file_path ?? $image->path
                                )
                                : null;

                        @endphp


                        <article
                            class="group overflow-hidden rounded-2xl
                            border border-slate-200 bg-white
                            shadow-sm transition duration-300
                            hover:-translate-y-1 hover:shadow-md"
                        >

                            <a href="#" class="block">

                                <div
                                    class="aspect-[4/3] overflow-hidden
                                    bg-slate-100"
                                >

                                    @if ($imageUrl)

                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $work->project_title }}"
                                            class="h-full w-full object-cover
                                            transition duration-500
                                            group-hover:scale-105"
                                            loading="lazy"
                                        >

                                    @else

                                        <div
                                            class="flex h-full items-center
                                            justify-center text-sm
                                            text-slate-400"
                                        >
                                            ไม่มีรูปภาพ
                                        </div>

                                    @endif

                                </div>


                                <div class="p-3">

                                    <p
                                        class="line-clamp-1 text-xs
                                        text-emerald-600"
                                    >
                                        {{ $work->competition?->title ?? 'ไม่ระบุการแข่งขัน' }}
                                    </p>


                                    <h3
                                        class="mt-1 line-clamp-2 text-sm
                                        font-semibold text-slate-800
                                        transition
                                        group-hover:text-emerald-700"
                                    >
                                        {{ $work->project_title }}
                                    </h3>


                                    @if ($work->project_description)

                                        <p
                                            class="mt-1.5 line-clamp-2
                                            text-xs leading-5
                                            text-slate-500"
                                        >
                                            {{ $work->project_description }}
                                        </p>

                                    @endif


                                    <div
                                        class="mt-3 flex items-center
                                        justify-between"
                                    >

                                        <span
                                            class="text-xs
                                            text-slate-400"
                                        >
                                            คะแนนรวม
                                        </span>

                                        <span
                                            class="text-sm font-bold
                                            text-slate-700"
                                        >
                                            {{ $work->final_score ?? '-' }}
                                        </span>

                                    </div>

                                </div>

                            </a>

                        </article>

                    @endforeach

                </div>


                {{-- Pagination --}}
                @if (method_exists($works, 'links'))

                    <div class="mt-6">
                        {{ $works->withQueryString()->links() }}
                    </div>

                @endif


            @else

                <div
                    class="rounded-2xl border border-dashed
                    border-slate-300 bg-white px-6 py-12
                    text-center"
                >

                    <div
                        class="mx-auto flex h-12 w-12
                        items-center justify-center
                        rounded-2xl bg-emerald-50
                        text-emerald-600"
                    >

                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect
                                x="3"
                                y="4"
                                width="18"
                                height="16"
                                rx="2"
                            />
                            <circle
                                cx="8.5"
                                cy="9"
                                r="1.5"
                            />
                            <path d="m4 17 5-5 4 4 2-2 5 4"/>
                        </svg>

                    </div>


                    <h3
                        class="mt-3 font-semibold text-sm
                        text-slate-700"
                    >
                        ยังไม่มีผลงาน
                    </h3>

                    <p
                        class="mt-1 text-sm text-slate-400"
                    >
                        ยังไม่มีผลงานที่ตรงกับเงื่อนไขที่ค้นหา
                    </p>

                </div>

            @endif

        </section>

    </main>


    {{-- =========================================================
        FOOTER
    ========================================================== --}}
    <footer class="border-t border-slate-200 bg-white">

        <div class="mx-auto max-w-7xl px-4 py-6
             text-center text-sm text-slate-400
             sm:px-6 lg:px-8">
             คลังผลงานการประกวดและองค์ความรู้
        </div>

    </footer>

</div>
<script>
        document.addEventListener('DOMContentLoaded', () => {
            const trigger = document.getElementById('sort-trigger');
            const panel   = document.getElementById('sort-panel');
            const chevron = document.getElementById('sort-chevron');
            const label   = document.getElementById('sort-label');
            const hidden  = document.getElementById('sort-value');

            if (!trigger || !panel) {
                console.error('sort-trigger หรือ sort-panel หาไม่เจอ');
                return;
            }

            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                panel.classList.toggle('hidden');
                chevron.classList.toggle('rotate-180');
            });

            document.querySelectorAll('.sort-option').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const form = btn.closest('form');

                    if (!form) {
                        console.error('ปุ่มนี้ไม่ได้อยู่ใน <form>:', btn);
                        return;
                    }

                    hidden.value = btn.dataset.value;
                    label.textContent = btn.dataset.label;
                    form.submit();
                });
            });

            document.addEventListener('click', (e) => {
                if (!trigger.contains(e.target) && !panel.contains(e.target)) {
                    panel.classList.add('hidden');
                    chevron.classList.remove('rotate-180');
                }
            });
        });
    </script>

@endsection