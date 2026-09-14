@extends('layouts.app')

@section('title', 'รายละเอียดข้อความติดต่อ')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">รายละเอียดข้อความติดต่อ</h1>
        <p class="mt-1 text-sm text-slate-500">
            รับเมื่อ {{ $contactMessage->created_at?->format('d/m/Y H:i') }}
        </p>
    </div>

    <a
        href="{{ route('superadmin.contact-messages.index') }}"
        class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
    >
        กลับรายการข้อความ
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
        'unread' => 'bg-blue-50 text-blue-700 border-blue-200',
        'read' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'archived' => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
@endphp

<div class="mx-auto w-full max-w-6xl" data-contact-message="{{ $contactMessage->id }}">
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_300px]">

        {{-- Main --}}
        <main class="min-w-0 space-y-5">

            {{-- Sender --}}
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-start sm:justify-between sm:px-6">
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">ผู้ส่ง</p>
                        <h2 class="mt-1 truncate text-xl font-semibold text-slate-900">
                            {{ $contactMessage->name ?: 'ไม่ระบุชื่อ' }}
                        </h2>
                    </div>

                    <span
                        class="inline-flex w-fit items-center rounded-md border px-2.5 py-1 text-xs font-medium
                            {{ $statusClasses[$contactMessage->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}"
                    >
                        {{ $statusLabels[$contactMessage->status] ?? $contactMessage->status }}
                    </span>
                </div>

                <div class="grid sm:grid-cols-2">
                    <div class="border-b border-slate-100 px-5 py-4 sm:border-b-0 sm:border-r sm:px-6">
                        <p class="text-xs font-medium text-slate-500">อีเมล</p>

                        @if ($contactMessage->email)
                            <a
                                href="mailto:{{ $contactMessage->email }}"
                                class="mt-1.5 block break-all text-sm text-slate-800 hover:text-blue-700 hover:underline"
                            >
                                {{ $contactMessage->email }}
                            </a>
                        @else
                            <p class="mt-1.5 text-sm text-slate-400">-</p>
                        @endif
                    </div>

                    <div class="px-5 py-4 sm:px-6">
                        <p class="text-xs font-medium text-slate-500">โทรศัพท์</p>

                        @if ($contactMessage->phone)
                            <a
                                href="tel:{{ $contactMessage->phone }}"
                                class="mt-1.5 block text-sm text-slate-800 hover:text-blue-700 hover:underline"
                            >
                                {{ $contactMessage->phone }}
                            </a>
                        @else
                            <p class="mt-1.5 text-sm text-slate-400">-</p>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Message --}}
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-900">ข้อความ</h2>
                </div>

                <div class="px-5 py-5 sm:px-6 sm:py-6">
                    <div class="whitespace-pre-line break-words text-[15px] leading-7 text-slate-700">
                        {{ $contactMessage->message ?: '-' }}
                    </div>
                </div>
            </section>
        </main>

        {{-- Sidebar --}}
        <aside class="space-y-5">

            {{-- Message info --}}
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3.5">
                    <h2 class="text-sm font-semibold text-slate-900">ข้อมูลข้อความ</h2>
                </div>

                <dl class="divide-y divide-slate-100">
                    <div class="px-4 py-3.5">
                        <dt class="text-xs text-slate-500">วันที่รับ</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">
                            {{ $contactMessage->created_at?->format('d/m/Y') ?: '-' }}
                        </dd>
                    </div>

                    <div class="px-4 py-3.5">
                        <dt class="text-xs text-slate-500">เวลา</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">
                            {{ $contactMessage->created_at?->format('H:i') ?: '-' }} น.
                        </dd>
                    </div>

                    <div class="px-4 py-3.5">
                        <dt class="text-xs text-slate-500">สถานะปัจจุบัน</dt>
                        <dd class="mt-2">
                            <span
                                class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium
                                    {{ $statusClasses[$contactMessage->status] ?? 'border-slate-200 bg-slate-100 text-slate-600' }}"
                            >
                                {{ $statusLabels[$contactMessage->status] ?? $contactMessage->status }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Status --}}
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3.5">
                    <h2 class="text-sm font-semibold text-slate-900">เปลี่ยนสถานะ</h2>
                    <p class="mt-1 text-xs text-slate-500">เลือกสถานะของข้อความนี้</p>
                </div>

                <div class="space-y-2 p-4">
                    @foreach ([
                        'read' => 'อ่านแล้ว',
                        'unread' => 'ยังไม่อ่าน',
                        'archived' => 'เก็บถาวร',
                    ] as $status => $label)
                        <form
                            method="POST"
                            action="{{ route('superadmin.contact-messages.status', $contactMessage) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="status" value="{{ $status }}">

                            <button
                                type="submit"
                                class="flex h-10 w-full items-center justify-between rounded-lg border px-3 text-sm font-medium transition
                                    {{ $contactMessage->status === $status
                                        ? 'border-slate-900 bg-slate-900 text-white'
                                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                    }}"
                            >
                                <span>{{ $label }}</span>

                                @if ($contactMessage->status === $status)
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m6 12 4 4 8-8"/>
                                    </svg>
                                @endif
                            </button>
                        </form>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
