@extends('layouts.app')

@section('title', $ebook->title)

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div class="min-w-0">
        <h1 class="truncate text-2xl font-semibold tracking-tight text-slate-900">
            {{ $ebook->title }}
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            รายละเอียด E-Book ในระบบ
        </p>
    </div>

    <div class="flex shrink-0 gap-2">
        <a
            href="{{ route('superadmin.knowledge-page.books.index') }}"
            class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
        >
            กลับ
        </a>

        <a
            href="{{ route('superadmin.knowledge-page.books.edit', $ebook) }}"
            class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
        >
            แก้ไข
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="mx-auto w-full max-w-6xl">
    <article
        class="grid gap-5 lg:grid-cols-[220px_minmax(0,1fr)]"
        data-ebook-id="{{ $ebook->id }}"
    >

        {{-- Cover --}}
        <aside>
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                @if ($ebook->cover_image_url)
                    <img
                        src="{{ $ebook->cover_image_url }}"
                        alt="ปก {{ $ebook->title }}"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 object-contain"
                    >
                @else
                    <div class="flex aspect-[3/4] items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm text-slate-400">
                        ไม่มีรูปปก
                    </div>
                @endif
            </div>
        </aside>

        {{-- Content --}}
        <main class="min-w-0 space-y-5">

            {{-- Overview --}}
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-semibold text-slate-900">
                            {{ $ebook->title }}
                        </h2>

                        @if ($ebook->knowledgeCategory)
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $ebook->knowledgeCategory->name }}
                            </p>
                        @endif
                    </div>

                    <span
                        class="inline-flex w-fit items-center rounded-md px-2.5 py-1 text-xs font-medium
                            {{ $ebook->status === 'published'
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-slate-100 text-slate-600'
                            }}"
                    >
                        {{ $ebook->status === 'published' ? 'กำลังแสดง' : 'ถูกซ่อน' }}
                    </span>
                </div>

                @if ($ebook->summary)
                    <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                        <p class="whitespace-pre-line text-sm leading-7 text-slate-700">
                            {{ $ebook->summary }}
                        </p>
                    </div>
                @endif

                <dl class="grid sm:grid-cols-2 lg:grid-cols-4">
                    <div class="border-b border-slate-100 px-5 py-4 sm:border-r lg:border-b-0">
                        <dt class="text-xs font-medium text-slate-500">ปีเผยแพร่</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $ebook->publication_year ?: '-' }}
                        </dd>
                    </div>

                    <div class="border-b border-slate-100 px-5 py-4 lg:border-b-0 lg:border-r">
                        <dt class="text-xs font-medium text-slate-500">เล่ม</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $ebook->volume ?: '-' }}
                        </dd>
                    </div>

                    <div class="border-b border-slate-100 px-5 py-4 sm:border-b-0 sm:border-r">
                        <dt class="text-xs font-medium text-slate-500">ฉบับ</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $ebook->issue ?: '-' }}
                        </dd>
                    </div>

                    <div class="px-5 py-4">
                        <dt class="text-xs font-medium text-slate-500">ลำดับ</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $ebook->sort_order }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Content --}}
            @if ($ebook->content)
                <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                        <h2 class="text-base font-semibold text-slate-900">เนื้อหา</h2>
                    </div>

                    <div class="px-5 py-5 sm:px-6">
                        <div class="whitespace-pre-line text-[15px] leading-7 text-slate-700">
                            {{ $ebook->content }}
                        </div>
                    </div>
                </section>
            @endif

            {{-- Reading options --}}
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-900">ช่องทางอ่าน</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        เปิดอ่าน ดาวน์โหลด หรือไปยังแหล่งข้อมูลภายนอก
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 p-5 sm:p-6">
                    @if ($ebook->attachment_path)
                        <a
                            href="{{ route('knowledge-items.attachment.inline', $ebook) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            อ่าน PDF
                        </a>

                        <a
                            href="{{ route('knowledge-items.attachment', $ebook) }}"
                            class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            ดาวน์โหลด PDF
                        </a>
                    @endif

                    @if ($ebook->external_url)
                        <a
                            href="{{ $ebook->external_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            อ่านออนไลน์
                        </a>
                    @endif

                    @if (! $ebook->attachment_path && ! $ebook->external_url)
                        <p class="text-sm text-slate-500">
                            ยังไม่มีช่องทางอ่านสำหรับ E-Book รายการนี้
                        </p>
                    @endif
                </div>
            </section>
        </main>
    </article>
</div>
@endsection
