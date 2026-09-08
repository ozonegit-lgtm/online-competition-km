@extends('layouts.app')

@section('title', 'แดชบอร์ดผู้ดูแลระบบสูงสุด')

@section('header')
    <div>
        <h1 class="text-lg font-bold text-slate-900">ภาพรวมระบบ</h1>
        <p class="mt-0.5 text-[11px] text-slate-500">ติดตามผู้ใช้งาน การแข่งขัน ผลงาน และองค์ความรู้ภายในระบบ</p>
    </div>
@endsection

@section('content')
    @php
        $stats = $stats ?? [
            'users' => 0,
            'competitions' => 0,
            'submissions' => 0,
            'published_km' => 0,
            'open_competitions' => 0,
            'judging_competitions' => 0,
            'published_results' => 0,
            'draft_km' => 0,
        ];

        $roleStats = $roleStats ?? [
            'super_admin' => 0,
            'competition_admin' => 0,
            'judge' => 0,
        ];

        $recentCompetitions = $recentCompetitions ?? collect();
        $recentActivities = $recentActivities ?? collect();
        $totalRoles = max(1, array_sum($roleStats));
    @endphp

    <div class="w-full space-y-3">

        {{-- Welcome --}}
        <section class="flex flex-col gap-2 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-sm font-bold text-slate-900">สวัสดี, {{ auth()->user()->username }}</h2>
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">{{ auth()->user()->role->display_name }}</span>
                </div>
                <p class="mt-0.5 text-[11px] text-slate-500">ภาพรวมสถานะสำคัญและความเคลื่อนไหวภายในระบบ</p>
            </div>

            <div class="flex items-center gap-1.5 text-[10px] font-medium text-emerald-700">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                ระบบพร้อมใช้งาน
            </div>
        </section>

        {{-- Main Stats --}}
        <section class="grid grid-cols-2 gap-2.5 md:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <span class="text-[9px] font-semibold tracking-wide text-slate-400">USERS</span>
                </div>
                <p class="mt-2.5 text-xl font-bold text-slate-900">{{ number_format($stats['users']) }}</p>
                <p class="text-[11px] text-slate-500">ผู้ใช้งานทั้งหมด</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4ZM5 6H3v2a4 4 0 0 0 4 4M19 6h2v2a4 4 0 0 1-4 4"/></svg>
                    </div>
                    <span class="text-[9px] font-semibold tracking-wide text-slate-400">COMPETITION</span>
                </div>
                <p class="mt-2.5 text-xl font-bold text-slate-900">{{ number_format($stats['competitions']) }}</p>
                <p class="text-[11px] text-slate-500">การแข่งขันทั้งหมด</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
                    </div>
                    <span class="text-[9px] font-semibold tracking-wide text-slate-400">SUBMISSION</span>
                </div>
                <p class="mt-2.5 text-xl font-bold text-slate-900">{{ number_format($stats['submissions']) }}</p>
                <p class="text-[11px] text-slate-500">ผลงานที่ส่งทั้งหมด</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
                    </div>
                    <span class="text-[9px] font-semibold tracking-wide text-slate-400">KNOWLEDGE</span>
                </div>
                <p class="mt-2.5 text-xl font-bold text-slate-900">{{ number_format($stats['published_km']) }}</p>
                <p class="text-[11px] text-slate-500">KM ที่เผยแพร่แล้ว</p>
            </div>
        </section>

        {{-- Middle --}}
        <section class="grid gap-3 lg:grid-cols-12">

            {{-- Recent Competitions --}}
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-8">
                <div class="flex items-center justify-between border-b border-slate-100 px-3.5 py-2.5">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900">การแข่งขันล่าสุด</h3>
                        <p class="mt-0.5 text-[10px] text-slate-400">การแข่งขันที่มีความเคลื่อนไหวล่าสุด</p>
                    </div>
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[9px] font-semibold text-slate-500">ล่าสุด</span>
                </div>

                @forelse ($recentCompetitions->take(5) as $competition)
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-3.5 py-2.5 last:border-0 hover:bg-slate-50">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-blue-500"></span>
                                <p class="truncate text-xs font-semibold text-slate-800">{{ $competition->title ?? $competition->name ?? 'การแข่งขัน' }}</p>
                            </div>

                            <div class="mt-0.5 flex gap-3 pl-3.5 text-[10px] text-slate-400">
                                @if (isset($competition->created_at))
                                    <span>{{ $competition->created_at->format('d/m/Y') }}</span>
                                @endif

                                @if (isset($competition->submissions_count))
                                    <span>{{ number_format($competition->submissions_count) }} ผลงาน</span>
                                @endif
                            </div>
                        </div>

                        <span class="shrink-0 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700">{{ $competition->display_status ?? 'ไม่ระบุ' }}</span>
                    </div>
                @empty
                    <div class="flex h-36 flex-col items-center justify-center text-center">
                        <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/></svg>
                        <p class="mt-2 text-xs font-medium text-slate-500">ยังไม่มีข้อมูลการแข่งขัน</p>
                    </div>
                @endforelse
            </div>

            {{-- Workflow --}}
            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm lg:col-span-4">
                <h3 class="text-xs font-bold text-slate-900">สถานะการทำงาน</h3>
                <p class="mt-0.5 text-[10px] text-slate-400">ภาพรวม Workflow ปัจจุบัน</p>

                <div class="mt-3 space-y-1.5">
                    <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-2.5 py-2">
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            <span class="text-[11px] text-slate-700">เปิดรับผลงาน</span>
                        </div>
                        <span class="text-xs font-bold text-slate-900">{{ number_format($stats['open_competitions']) }}</span>
                    </div>

                    <div class="flex items-center justify-between rounded-lg bg-amber-50 px-2.5 py-2">
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            <span class="text-[11px] text-slate-700">กำลังตัดสิน</span>
                        </div>
                        <span class="text-xs font-bold text-slate-900">{{ number_format($stats['judging_competitions']) }}</span>
                    </div>

                    <div class="flex items-center justify-between rounded-lg bg-blue-50 px-2.5 py-2">
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            <span class="text-[11px] text-slate-700">เผยแพร่ผลแล้ว</span>
                        </div>
                        <span class="text-xs font-bold text-slate-900">{{ number_format($stats['published_results']) }}</span>
                    </div>

                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-2.5 py-2">
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                            <span class="text-[11px] text-slate-700">KM แบบร่าง</span>
                        </div>
                        <span class="text-xs font-bold text-slate-900">{{ number_format($stats['draft_km']) }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Bottom --}}
        <section class="grid gap-3 lg:grid-cols-2">

            {{-- Roles --}}
            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900">ผู้ใช้งานตามสิทธิ์</h3>
                        <p class="mt-0.5 text-[10px] text-slate-400">จำนวนบัญชีในแต่ละบทบาท</p>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-3 gap-2">
                    <div class="rounded-lg bg-blue-50 px-3 py-2">
                        <p class="text-[10px] text-blue-600">Super Admin</p>
                        <p class="mt-0.5 text-base font-bold text-slate-900">{{ number_format($roleStats['super_admin']) }}</p>
                    </div>

                    <div class="rounded-lg bg-violet-50 px-3 py-2">
                        <p class="text-[10px] text-violet-600">Competition Admin</p>
                        <p class="mt-0.5 text-base font-bold text-slate-900">{{ number_format($roleStats['competition_admin']) }}</p>
                    </div>

                    <div class="rounded-lg bg-cyan-50 px-3 py-2">
                        <p class="text-[10px] text-cyan-700">Judge</p>
                        <p class="mt-0.5 text-base font-bold text-slate-900">{{ number_format($roleStats['judge']) }}</p>
                    </div>
                </div>
            </div>

            {{-- Activity --}}
            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                <h3 class="text-xs font-bold text-slate-900">กิจกรรมล่าสุด</h3>
                <p class="mt-0.5 text-[10px] text-slate-400">ความเคลื่อนไหวล่าสุดภายในระบบ</p>

                <div class="mt-2">
                    @forelse ($recentActivities->take(3) as $activity)
                        <div class="flex items-center gap-2 border-b border-slate-100 py-2 last:border-0">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-blue-500"></span>
                            <p class="min-w-0 truncate text-[11px] text-slate-600">{{ data_get($activity, 'description', '-') }}</p>
                        </div>
                    @empty
                        <div class="flex h-20 items-center justify-center rounded-lg bg-slate-50">
                            <p class="text-[11px] text-slate-400">ยังไม่มีกิจกรรมล่าสุด</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

    </div>
@endsection
