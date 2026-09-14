@extends('layouts.app')

@section('title', 'ข้อความติดต่อ')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">ข้อความติดต่อ</h1>
        <p class="mt-1 text-sm text-slate-500">
            ข้อความที่ผู้ใช้งานส่งมาจากหน้า E-Book KM
        </p>
    </div>

    <a
        href="{{ route('knowledge.index') }}"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
    >
        เปิดหน้า E-Book KM
    </a>
</div>
@endsection

@section('content')
@php
    $statusLabels = [
        'unread' => 'ยังไม่อ่าน',
        'read' => 'อ่านแล้ว',
        'archived' => 'เก็บถาวร',
    ];

    $statusClasses = [
        'unread' => 'bg-blue-50 text-blue-700',
        'read' => 'bg-emerald-50 text-emerald-700',
        'archived' => 'bg-slate-100 text-slate-600',
    ];
@endphp

<div class="mx-auto w-full max-w-6xl">

    {{-- Filter + count --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <nav class="flex max-w-full gap-1 overflow-x-auto rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
            <a
                href="{{ route('superadmin.contact-messages.index') }}"
                class="inline-flex h-9 shrink-0 items-center rounded-md px-3 text-sm font-medium transition
                    {{ request('status')
                        ? 'text-slate-600 hover:bg-slate-50'
                        : 'bg-slate-900 text-white'
                    }}"
            >
                ทั้งหมด
            </a>

            @foreach ($statusLabels as $status => $label)
                <a
                    href="{{ route('superadmin.contact-messages.index', ['status' => $status]) }}"
                    class="inline-flex h-9 shrink-0 items-center rounded-md px-3 text-sm font-medium transition
                        {{ request('status') === $status
                            ? 'bg-slate-900 text-white'
                            : 'text-slate-600 hover:bg-slate-50'
                        }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <p class="text-sm text-slate-500">
            {{ $messages->total() }} ข้อความ
        </p>
    </div>

    {{-- Inbox --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" data-contact-messages>

        {{-- Desktop heading --}}
        <div class="hidden border-b border-slate-200 bg-slate-50 px-5 py-3 lg:grid lg:grid-cols-[minmax(180px,1.15fr)_minmax(220px,1.5fr)_150px_150px_120px] lg:gap-5">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">ผู้ส่ง</div>
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">ข้อมูลติดต่อ</div>
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">วันที่</div>
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">สถานะ</div>
            <div class="text-right text-xs font-medium uppercase tracking-wide text-slate-500">จัดการ</div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($messages as $message)
                <article
                    class="relative px-5 py-4 transition hover:bg-slate-50/70
                        {{ $message->status === 'unread' ? 'bg-blue-50/30' : 'bg-white' }}"
                >
                    <div class="grid gap-3 lg:grid-cols-[minmax(180px,1.15fr)_minmax(220px,1.5fr)_150px_150px_120px] lg:items-center lg:gap-5">

                        {{-- Sender --}}
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                @if ($message->status === 'unread')
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                                @else
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-transparent"></span>
                                @endif

                                <p class="truncate text-sm {{ $message->status === 'unread' ? 'font-semibold text-slate-900' : 'font-medium text-slate-800' }}">
                                    {{ $message->name ?: 'ไม่ระบุชื่อ' }}
                                </p>
                            </div>

                            @if ($message->status === 'unread')
                                <p class="mt-1 pl-4 text-xs font-medium text-blue-600">
                                    ข้อความใหม่
                                </p>
                            @endif
                        </div>

                        {{-- Contact --}}
                        <div class="min-w-0 pl-4 lg:pl-0">
                            @if ($message->email)
                                <a
                                    href="mailto:{{ $message->email }}"
                                    class="block truncate text-sm text-slate-700 hover:text-blue-700 hover:underline"
                                >
                                    {{ $message->email }}
                                </a>
                            @else
                                <p class="text-sm text-slate-400">ไม่มีอีเมล</p>
                            @endif

                            @if ($message->phone)
                                <a
                                    href="tel:{{ $message->phone }}"
                                    class="mt-0.5 block text-xs text-slate-500 hover:text-blue-700 hover:underline"
                                >
                                    {{ $message->phone }}
                                </a>
                            @endif
                        </div>

                        {{-- Date --}}
                        <div class="pl-4 lg:pl-0">
                            <p class="text-sm text-slate-700">
                                {{ $message->created_at?->format('d/m/Y') }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ $message->created_at?->format('H:i') }} น.
                            </p>
                        </div>

                        {{-- Status --}}
                        <div class="pl-4 lg:pl-0">
                            <span
                                class="inline-flex rounded-md px-2.5 py-1 text-xs font-medium
                                    {{ $statusClasses[$message->status] ?? 'bg-slate-100 text-slate-600' }}"
                            >
                                {{ $statusLabels[$message->status] ?? $message->status }}
                            </span>
                        </div>

                        {{-- Action --}}
                        <div class="pl-4 lg:pl-0 lg:text-right">
                            <a
                                href="{{ route('superadmin.contact-messages.show', $message) }}"
                                class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 transition hover:bg-slate-50"
                            >
                                ดูข้อความ
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="px-6 py-14 text-center">
                    <p class="text-sm font-medium text-slate-700">
                        {{ request('status') ? 'ไม่มีข้อความในสถานะนี้' : 'ยังไม่มีข้อความติดต่อ' }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        @if (request('status'))
                            ลองเลือกดูข้อความสถานะอื่น
                        @else
                            ข้อความจากแบบฟอร์มติดต่อจะแสดงที่นี่
                        @endif
                    </p>

                    @if (request('status'))
                        <a
                            href="{{ route('superadmin.contact-messages.index') }}"
                            class="mt-4 inline-flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            ดูข้อความทั้งหมด
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($messages->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">
                {{ $messages->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
