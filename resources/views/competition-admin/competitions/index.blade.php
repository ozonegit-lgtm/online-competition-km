@extends('layouts.app')

@section('title', 'จัดการการแข่งขัน')

@section('header')
    <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900">
            จัดการการแข่งขัน
        </h1>

        <p class="mt-1 text-xs text-slate-500">
            สร้าง ติดตาม และจัดการการแข่งขันทั้งหมดของคุณ
        </p>
    </div>
@endsection


@section('content')
    @php
        $competitionCount = method_exists($competitions, 'total')
            ? $competitions->total()
            : $competitions->count();
    @endphp

    <div class="mx-auto w-full max-w-7xl">

        {{-- ========================================================= --}}
        {{-- Page Toolbar --}}
        {{-- ========================================================= --}}
        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">

                {{-- Left --}}
                <div class="min-w-0">
                    <div class="flex items-center gap-3">

                        <div
                            class="
                                flex h-11 w-11 shrink-0 items-center justify-center
                                rounded-xl bg-blue-50 text-blue-600
                                ring-1 ring-inset ring-blue-100
                            "
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8 21h8m-4-4v4M7 4h10v3a5 5 0 0 1-10 0V4Zm0 1H4v2a4 4 0 0 0 4 4m9-6h3v2a4 4 0 0 1-4 4"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-slate-900">
                                การแข่งขันของคุณ
                            </h2>

                            <p class="mt-0.5 text-sm text-slate-500">
                                ทั้งหมด
                                <span class="font-semibold text-slate-700">
                                    {{ number_format($competitionCount) }}
                                </span>
                                รายการ
                            </p>
                        </div>
                    </div>
                </div>


                {{-- Create button --}}
                <a
                    href="{{ route('competition-admin.competitions.create') }}"
                    class="
                        inline-flex h-10 shrink-0 items-center justify-center gap-2
                        rounded-xl bg-blue-600 px-4
                        text-sm font-semibold text-white
                        shadow-sm shadow-blue-600/20
                        transition
                        hover:bg-blue-700
                        focus:outline-none focus:ring-4 focus:ring-blue-100
                    "
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
                            d="M12 5v14M5 12h14"
                        />
                    </svg>

                    สร้างการแข่งขัน
                </a>

            </div>


            {{-- Search --}}
            <div class="border-t border-slate-100 bg-slate-50/50 p-3 sm:p-4">
                <form
                    action="{{ route('competition-admin.competitions.index') }}"
                    method="GET"
                >
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                        <div class="relative min-w-0 flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg
                                    class="h-[18px] w-[18px]"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <circle cx="11" cy="11" r="7"/>
                                    <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                                </svg>
                            </div>

                            <input
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="ค้นหาการแข่งขัน..."
                                autocomplete="off"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition placeholder:font-normal placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >
                        </div>

                        <div class="flex items-center gap-2">
                            @if (request()->filled('q'))
                                <a
                                    href="{{ route('competition-admin.competitions.index') }}"
                                    title="ล้างการค้นหา"
                                    aria-label="ล้างการค้นหา"
                                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-100"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>
                                    </svg>
                                </a>
                            @endif

                            <button
                                type="submit"
                                class="inline-flex h-11 flex-1 shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 sm:flex-none"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <circle cx="11" cy="11" r="7"/>
                                    <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                                </svg>

                                ค้นหา
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        @if (request()->filled('q'))
                            <p class="truncate text-xs text-slate-500">
                                ผลการค้นหาสำหรับ
                                <span class="font-semibold text-slate-800">
                                    “{{ request('q') }}”
                                </span>
                            </p>
                        @else
                            <p class="text-xs text-slate-500">
                                ค้นหาและเข้าถึงการแข่งขันของคุณได้อย่างรวดเร็ว
                            </p>
                        @endif

                        <span class="inline-flex w-fit items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                            {{ number_format($competitionCount) }} การแข่งขัน
                        </span>
                    </div>
                </form>
            </div>

        </section>



        {{-- ========================================================= --}}
        {{-- Competition Grid --}}
        {{-- ========================================================= --}}
        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">

            @forelse ($competitions as $competition)

                @php
                    $status = $competition->display_status ?? 'draft';

                    $statusLabels = [
                        'draft' => 'ฉบับร่าง',
                        'published' => 'เผยแพร่แล้ว',
                        'open' => 'เปิดรับผลงาน',
                        'closed' => 'ปิดรับผลงาน',
                        'judging' => 'กำลังตัดสิน',
                        'completed' => 'เสร็จสิ้น',
                        'upcoming' => 'ยังไม่เปิดรับผลงาน',
                        'waiting_result' => 'รอประกาศผล',
                    ];

                    $statusClasses = [
                        'draft' => 'bg-slate-100 text-slate-600 ring-slate-200',
                        'published' => 'bg-blue-50 text-blue-700 ring-blue-200',
                        'open' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        'closed' => 'bg-rose-50 text-rose-700 ring-rose-200',
                        'judging' => 'bg-violet-50 text-violet-700 ring-violet-200',
                        'completed' => 'bg-green-50 text-green-700 ring-green-200',
                        'upcoming' => 'bg-amber-50 text-amber-700 ring-amber-200',
                        'waiting_result' => 'bg-slate-100 text-slate-700 ring-slate-200',
                    ];

                    $statusDotClasses = [
                        'draft' => 'bg-slate-400',
                        'published' => 'bg-blue-500',
                        'open' => 'bg-emerald-500',
                        'closed' => 'bg-rose-500',
                        'judging' => 'bg-violet-500',
                        'completed' => 'bg-green-500',
                        'upcoming' => 'bg-amber-500',
                        'waiting_result' => 'bg-slate-500',
                    ];

                    $coverImage = $competition->cover_image;
                    $coverUrl = null;

                    if ($coverImage) {
                        $isRemoteCover = \Illuminate\Support\Str::startsWith(
                            $coverImage,
                            ['http://', 'https://']
                        );

                        if (
                            $isRemoteCover ||
                            \Illuminate\Support\Facades\Storage::disk('public')
                                ->exists($coverImage)
                        ) {
                            $coverUrl = $isRemoteCover
                                ? $coverImage
                                : \Illuminate\Support\Facades\Storage::disk('public')
                                    ->url($coverImage);
                        }
                    }
                @endphp


                <article
                    id="competition-card-{{ $competition->id }}"
                    class="
                        group flex h-full flex-col overflow-hidden
                        rounded-2xl border border-slate-200
                        bg-white
                        shadow-sm
                        transition duration-200
                        hover:-translate-y-0.5
                        hover:border-slate-300
                        hover:shadow-lg hover:shadow-slate-200/60
                    "
                >

                    {{-- ================================================= --}}
                    {{-- Cover --}}
                    {{-- ================================================= --}}
                    <div class="relative aspect-[16/8] overflow-hidden bg-slate-100">

                        @if ($coverUrl)

                            <img
                                src="{{ $coverUrl }}"
                                alt="ภาพปก {{ $competition->title }}"
                                class="
                                    h-full w-full object-cover
                                    transition duration-500
                                    group-hover:scale-[1.02]
                                "
                                loading="lazy"
                            >

                        @else

                            <div
                                class="
                                    flex h-full w-full
                                    flex-col items-center justify-center
                                    bg-gradient-to-br
                                    from-slate-50 to-slate-100
                                    text-slate-400
                                "
                            >
                                <div
                                    class="
                                        flex h-12 w-12 items-center justify-center
                                        rounded-2xl
                                        bg-white
                                        shadow-sm
                                        ring-1 ring-slate-200
                                    "
                                >
                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="14"
                                            rx="2"
                                        />

                                        <circle cx="9" cy="10" r="1.5" />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m5 17 4.5-4 3 2.5 2.5-2 4 3.5"
                                        />
                                    </svg>
                                </div>

                                <span class="mt-2 text-xs font-medium">
                                    ไม่มีภาพปก
                                </span>
                            </div>

                        @endif


                        {{-- dark gradient --}}
                        <div
                            class="
                                pointer-events-none absolute inset-x-0 top-0 h-20
                                bg-gradient-to-b
                                from-slate-900/25 to-transparent
                            "
                        ></div>


                        {{-- Status --}}
                        <div class="absolute left-3 top-3">

                            <span
                                class="
                                    inline-flex items-center gap-1.5
                                    rounded-full px-2.5 py-1
                                    text-[11px] font-semibold
                                    shadow-sm
                                    ring-1 ring-inset
                                    backdrop-blur
                                    {{ $statusClasses[$status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}
                                "
                            >
                                <span
                                    class="
                                        h-1.5 w-1.5 rounded-full
                                        {{ $statusDotClasses[$status] ?? 'bg-slate-400' }}
                                    "
                                ></span>

                                {{ $statusLabels[$status] ?? $status }}
                            </span>

                        </div>


                        {{-- Visibility --}}
                        <div class="absolute right-3 top-3">

                            @if ($competition->visibility === 'public')

                                <span
                                    class="
                                        inline-flex items-center gap-1.5
                                        rounded-full
                                        bg-white/95 px-2.5 py-1
                                        text-[11px] font-semibold text-blue-700
                                        shadow-sm
                                        ring-1 ring-inset ring-blue-100
                                        backdrop-blur
                                    "
                                >
                                    <svg
                                        class="h-3 w-3"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18" />
                                    </svg>

                                    Public
                                </span>

                            @else

                                <span
                                    class="
                                        inline-flex items-center gap-1.5
                                        rounded-full
                                        bg-white/95 px-2.5 py-1
                                        text-[11px] font-semibold text-slate-600
                                        shadow-sm
                                        ring-1 ring-inset ring-slate-200
                                        backdrop-blur
                                    "
                                >
                                    <svg
                                        class="h-3 w-3"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <rect
                                            x="5"
                                            y="10"
                                            width="14"
                                            height="10"
                                            rx="2"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="M8 10V7a4 4 0 0 1 8 0v3"
                                        />
                                    </svg>

                                    Private
                                </span>

                            @endif

                        </div>

                    </div>



                    {{-- ================================================= --}}
                    {{-- Content --}}
                    {{-- ================================================= --}}
                    <div class="flex flex-1 flex-col p-4 sm:p-5">

                        <div class="flex-1">

                            <h3
                                class="
                                    line-clamp-2
                                    text-base font-semibold leading-6
                                    text-slate-900
                                "
                            >
                                {{ $competition->title }}
                            </h3>


                            <p
                                class="
                                    mt-1.5 line-clamp-2 min-h-10
                                    text-xs leading-5
                                    text-slate-500
                                "
                            >
                                {{ $competition->description ?: 'ไม่มีรายละเอียดการแข่งขัน' }}
                            </p>



                            {{-- Metadata --}}
                            <div
                                class="
                                    mt-4 overflow-hidden
                                    rounded-xl border border-slate-100
                                    bg-slate-50/70
                                "
                            >

                                {{-- Category --}}
                                <div
                                    class="
                                        flex items-center gap-3
                                        border-b border-slate-100
                                        px-3 py-2.5
                                    "
                                >
                                    <div
                                        class="
                                            flex h-7 w-7 shrink-0 items-center justify-center
                                            rounded-lg bg-white
                                            text-slate-400
                                            ring-1 ring-slate-200
                                        "
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M4 6.5A2.5 2.5 0 0 1 6.5 4H10l2 2h5.5A2.5 2.5 0 0 1 20 8.5v9A2.5 2.5 0 0 1 17.5 20h-11A2.5 2.5 0 0 1 4 17.5v-11Z"
                                            />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="
                                                text-[10px] font-medium uppercase
                                                tracking-wide text-slate-400
                                            "
                                        >
                                            ประเภทการแข่งขัน
                                        </p>

                                        <p
                                            class="
                                                mt-0.5 truncate
                                                text-xs font-semibold
                                                text-slate-700
                                            "
                                        >
                                            {{ $competition->category?->category_name ?? '-' }}
                                        </p>
                                    </div>
                                </div>


                                {{-- Template --}}
                                <div
                                    class="
                                        flex items-center gap-3
                                        px-3 py-2.5
                                    "
                                >
                                    <div
                                        class="
                                            flex h-7 w-7 shrink-0 items-center justify-center
                                            rounded-lg bg-white
                                            text-slate-400
                                            ring-1 ring-slate-200
                                        "
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <rect
                                                x="4"
                                                y="3"
                                                width="16"
                                                height="18"
                                                rx="2"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                d="M8 8h8M8 12h8M8 16h5"
                                            />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="
                                                text-[10px] font-medium uppercase
                                                tracking-wide text-slate-400
                                            "
                                        >
                                            Template
                                        </p>

                                        <p
                                            class="
                                                mt-0.5 truncate
                                                text-xs font-semibold
                                                text-slate-700
                                            "
                                        >
                                            {{ $competition->template?->template_name ?? 'ไม่ใช้ Template' }}
                                        </p>
                                    </div>
                                </div>

                            </div>

                        </div>



                        {{-- ================================================= --}}
                        {{-- Actions --}}
                        {{-- ================================================= --}}
                        <div
                            class="
                                mt-4 flex items-center gap-2
                                border-t border-slate-100 pt-4
                            "
                        >

                            {{-- Detail --}}
                            <a
                                href="{{ route('competition-admin.competitions.show', $competition) }}"
                                class="
                                    inline-flex h-9 min-w-0 flex-1
                                    items-center justify-center gap-1.5
                                    rounded-lg bg-blue-600 px-3
                                    text-xs font-semibold text-white
                                    shadow-sm
                                    transition
                                    hover:bg-blue-700
                                    focus:outline-none focus:ring-4 focus:ring-blue-100
                                "
                            >
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                                    />

                                    <circle cx="12" cy="12" r="2.5" />
                                </svg>

                                ดูรายละเอียด
                            </a>


                            {{-- Edit --}}
                            <a
                                href="{{ route('competition-admin.competitions.edit', $competition) }}"
                                title="แก้ไขการแข่งขัน"
                                aria-label="แก้ไขการแข่งขัน"
                                class="
                                    inline-flex h-9 w-9 shrink-0
                                    items-center justify-center
                                    rounded-lg
                                    border border-slate-200 bg-white
                                    text-slate-500
                                    transition
                                    hover:border-blue-200
                                    hover:bg-blue-50
                                    hover:text-blue-600
                                    focus:outline-none focus:ring-4 focus:ring-blue-100
                                "
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m14.5 5.5 4 4M5 19l3.5-.7L19 7.8a1.8 1.8 0 0 0 0-2.6l-.2-.2a1.8 1.8 0 0 0-2.6 0L5.7 15.5 5 19Z"
                                    />
                                </svg>
                            </a>


                            {{-- Delete --}}
                            <form
                                action="{{ route('competition-admin.competitions.destroy', $competition) }}"
                                method="POST"
                                onsubmit="return confirm('ต้องการลบการแข่งขันนี้หรือไม่?')"
                                class="shrink-0"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    title="ลบการแข่งขัน"
                                    aria-label="ลบการแข่งขัน"
                                    class="
                                        inline-flex h-9 w-9
                                        items-center justify-center
                                        rounded-lg
                                        border border-slate-200 bg-white
                                        text-slate-400
                                        transition
                                        hover:border-rose-200
                                        hover:bg-rose-50
                                        hover:text-rose-600
                                        focus:outline-none focus:ring-4 focus:ring-rose-100
                                    "
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"
                                        />
                                    </svg>
                                </button>
                            </form>

                        </div>

                    </div>

                </article>


            @empty

                {{-- ===================================================== --}}
                {{-- Empty State --}}
                {{-- ===================================================== --}}
                <div
                    class="
                        flex min-h-[360px] flex-col items-center justify-center
                        rounded-2xl border border-slate-200
                        bg-white px-6 py-12
                        text-center shadow-sm
                        sm:col-span-2 xl:col-span-3
                    "
                >

                    <div
                        class="
                            flex h-14 w-14 items-center justify-center
                            rounded-2xl
                            bg-slate-50
                            text-slate-400
                            ring-1 ring-slate-200
                        "
                    >
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 21h8m-4-4v4M7 4h10v3a5 5 0 0 1-10 0V4Zm0 1H4v2a4 4 0 0 0 4 4m9-6h3v2a4 4 0 0 1-4 4"
                            />
                        </svg>
                    </div>


                    <h3 class="mt-4 text-base font-semibold text-slate-900">
                        {{ request()->filled('q')
                            ? 'ไม่พบการแข่งขันที่ค้นหา'
                            : 'ยังไม่มีการแข่งขัน' }}
                    </h3>


                    <p class="mt-1.5 max-w-md text-sm leading-6 text-slate-500">
                        {{ request()->filled('q')
                            ? 'ไม่พบรายการที่ตรงกับคำค้นหา ลองใช้คำค้นหาอื่นหรือล้างการค้นหา'
                            : 'เริ่มสร้างการแข่งขันแรกของคุณ และกำหนด Template สำหรับการรับผลงาน' }}
                    </p>


                    @if (request()->filled('q'))

                        <a
                            href="{{ route('competition-admin.competitions.index') }}"
                            class="
                                mt-5 inline-flex h-10 items-center justify-center
                                rounded-xl border border-slate-200
                                bg-white px-4
                                text-sm font-semibold text-slate-700
                                shadow-sm
                                transition
                                hover:bg-slate-50
                            "
                        >
                            ล้างการค้นหา
                        </a>

                    @else

                        <a
                            href="{{ route('competition-admin.competitions.create') }}"
                            class="
                                mt-5 inline-flex h-10 items-center justify-center gap-2
                                rounded-xl bg-blue-600 px-4
                                text-sm font-semibold text-white
                                shadow-sm
                                transition
                                hover:bg-blue-700
                            "
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
                                    d="M12 5v14M5 12h14"
                                />
                            </svg>

                            สร้างการแข่งขัน
                        </a>

                    @endif

                </div>

            @endforelse



            {{-- ========================================================= --}}
            {{-- Create New Card --}}
            {{-- ========================================================= --}}
            @if ($competitions->count() > 0)

                <a
                    href="{{ route('competition-admin.competitions.create') }}"
                    class="
                        group flex min-h-[360px]
                        flex-col items-center justify-center
                        rounded-2xl
                        border-2 border-dashed border-slate-200
                        bg-slate-50/50
                        p-6 text-center
                        transition
                        hover:border-blue-300
                        hover:bg-blue-50/50
                        focus:outline-none focus:ring-4 focus:ring-blue-100
                    "
                >

                    <div
                        class="
                            flex h-12 w-12 items-center justify-center
                            rounded-2xl
                            bg-white
                            text-blue-600
                            shadow-sm
                            ring-1 ring-slate-200
                            transition
                            group-hover:scale-105
                            group-hover:ring-blue-200
                        "
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                d="M12 5v14M5 12h14"
                            />
                        </svg>
                    </div>


                    <h3
                        class="
                            mt-4 text-sm font-semibold
                            text-slate-800
                            transition
                            group-hover:text-blue-700
                        "
                    >
                        สร้างการแข่งขันใหม่
                    </h3>


                    <p class="mt-1.5 max-w-[230px] text-xs leading-5 text-slate-500">
                        เลือกประเภทการแข่งขันและ Template เพื่อเริ่มต้น
                    </p>

                </a>

            @endif

        </div>



        {{-- ========================================================= --}}
        {{-- Pagination --}}
        {{-- ========================================================= --}}
        @if (method_exists($competitions, 'links') && $competitions->hasPages())

            <div
                class="
                    mt-6 rounded-2xl
                    border border-slate-200
                    bg-white px-4 py-3
                    shadow-sm
                "
            >
                {{ $competitions->withQueryString()->links() }}
            </div>

        @endif

    </div>
@endsection