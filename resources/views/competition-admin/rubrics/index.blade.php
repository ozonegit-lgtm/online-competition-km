@extends('layouts.app')

@section('title', 'จัดการเกณฑ์การให้คะแนน')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            จัดการเกณฑ์การให้คะแนน
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            การแข่งขัน: {{ $competition->title }}
        </p>
    </div>
@endsection

@section('content')
    <div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

    @error('rubric')
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-sm font-semibold text-red-700">
                {{ $message }}
            </p>
        </div>
    @enderror

    @if ($rubricsLocked)
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-bold text-amber-800">
                เกณฑ์การให้คะแนนถูกล็อกแล้ว
            </p>

            <p class="mt-1 text-sm leading-6 text-amber-700">
                การแข่งขันเริ่มตัดสินแล้ว
                ไม่สามารถเพิ่ม แก้ไข เปิด/ปิด หรือลบเกณฑ์ได้
            </p>
        </div>
    @endif

        {{-- สรุปคะแนน --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    จำนวนเกณฑ์ทั้งหมด
                </p>

                <p class="mt-2 text-3xl font-bold text-slate-800">
                    {{ $rubrics->count() }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    เกณฑ์ที่เปิดใช้งาน
                </p>

                <p class="mt-2 text-3xl font-bold text-green-600">
                    {{ $rubrics->where('is_active', true)->count() }}
                </p>
            </div>

            <div class="rounded-2xl border p-5 shadow-sm
                {{ $totalMaxScore == 100
                    ? 'border-green-200 bg-green-50'
                    : 'border-amber-200 bg-amber-50' }}">

                <p class="text-sm
                    {{ $totalMaxScore == 100
                        ? 'text-green-700'
                        : 'text-amber-700' }}">
                    คะแนนรวม
                </p>

                <div class="mt-2 flex items-end gap-2">
                    <p class="text-3xl font-bold
                        {{ $totalMaxScore == 100
                            ? 'text-green-700'
                            : 'text-amber-700' }}">
                        {{ number_format($totalMaxScore, 2) }}
                    </p>

                    <span class="pb-1 text-sm text-slate-500">
                        / 100 คะแนน
                    </span>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">

            {{-- ฟอร์มเพิ่มเกณฑ์ --}}
            <section class="h-fit rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-bold text-slate-800">
                        เพิ่มเกณฑ์ใหม่
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        กำหนดหัวข้อและคะแนนเต็ม
                    </p>
                </div>

@if (! $rubricsLocked)

            <form
                method="POST"
                action="{{ route(
                    'competition-admin.competitions.rubrics.store',
                    $competition
                ) }}"
                class="space-y-5 p-6">

                @csrf

                    <div>
                        <label
                            for="criteria_name"
                            class="block text-sm font-semibold text-slate-700">
                            ชื่อเกณฑ์
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            id="criteria_name"
                            name="criteria_name"
                            value="{{ old('criteria_name') }}"
                            placeholder="เช่น ความคิดสร้างสรรค์"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>

                    <div>
                        <label
                            for="description"
                            class="block text-sm font-semibold text-slate-700">
                            คำอธิบาย
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="อธิบายรายละเอียดของเกณฑ์"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label
                            for="max_score"
                            class="block text-sm font-semibold text-slate-700">
                            คะแนนเต็ม
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="number"
                            id="max_score"
                            name="max_score"
                            value="{{ old('max_score') }}"
                            min="1"
                            max="100"
                            step="0.01"
                            placeholder="เช่น 30"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">

                        <p class="mt-2 text-xs text-slate-500">
                            คะแนนรวมทุกเกณฑ์ต้องไม่เกิน 100 คะแนน
                        </p>
                    </div>

                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', true))
                            class="h-5 w-5 accent-green-600">

                        <span class="text-sm font-medium text-slate-700">
                            เปิดใช้งานเกณฑ์นี้
                        </span>
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-200">
                        + เพิ่มเกณฑ์
                    </button>
                    </form>
                    @else
                        <div class="p-6">
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="text-sm font-semibold text-amber-800">
                                    ไม่สามารถเพิ่มเกณฑ์ได้
                                </p>
                                <p class="mt-1 text-xs leading-5 text-amber-700">
                                    การแข่งขันเริ่มเข้าสู่กระบวนการตัดสินแล้ว
                                </p>
                            </div>
                        </div>

                    @endif

                    </section>

            {{-- รายการเกณฑ์ --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-lg font-bold text-slate-800">
                        เกณฑ์การให้คะแนน
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        จัดการเกณฑ์ทั้งหมดของการแข่งขันนี้
                    </p>
                </div>

                @if ($rubrics->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-2xl">
                            📋
                        </div>

                        <h3 class="mt-4 font-semibold text-slate-700">
                            ยังไม่มีเกณฑ์การให้คะแนน
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            เพิ่มเกณฑ์แรกจากแบบฟอร์มด้านซ้าย
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-slate-200">
                        @foreach ($rubrics as $index => $rubric)
                            <article class="p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700">
                                                {{ $index + 1 }}
                                            </span>

                                            <h3 class="font-bold text-slate-800">
                                                {{ $rubric->criteria_name }}
                                            </h3>

                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                                {{ $rubric->is_active
                                                    ? 'bg-green-50 text-green-700'
                                                    : 'bg-slate-100 text-slate-500' }}">
                                                {{ $rubric->is_active
                                                    ? 'เปิดใช้งาน'
                                                    : 'ปิดใช้งาน' }}
                                            </span>
                                        </div>

                                        @if ($rubric->description)
                                            <p class="mt-3 text-sm leading-6 text-slate-500">
                                                {{ $rubric->description }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="shrink-0 text-right">
                                        <p class="text-2xl font-bold text-blue-600">
                                            {{ number_format($rubric->max_score, 2) }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            คะแนน
                                        </p>
                                    </div>
                                </div>

                                @if (! $rubricsLocked)

                                <details class="mt-5 rounded-xl border border-slate-200 bg-slate-50">
                                    <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-slate-700">
                                        แก้ไขเกณฑ์
                                    </summary>
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'competition-admin.competitions.rubrics.update',
                                            [$competition, $rubric]
                                        ) }}"
                                        class="space-y-4 border-t border-slate-200 p-4">

                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700">
                                                ชื่อเกณฑ์
                                            </label>

                                            <input
                                                type="text"
                                                name="criteria_name"
                                                value="{{ $rubric->criteria_name }}"
                                                required
                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700">
                                                คำอธิบาย
                                            </label>

                                            <textarea
                                                name="description"
                                                rows="3"
                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ $rubric->description }}</textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-slate-700">
                                                คะแนนเต็ม
                                            </label>

                                            <input
                                                type="number"
                                                name="max_score"
                                                value="{{ $rubric->max_score }}"
                                                min="1"
                                                max="100"
                                                step="0.01"
                                                required
                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                        </div>

                                        <label class="flex items-center gap-3">
                                            <input
                                                type="checkbox"
                                                name="is_active"
                                                value="1"
                                                @checked($rubric->is_active)
                                                class="h-5 w-5 accent-green-600">

                                            <span class="text-sm text-slate-700">
                                                เปิดใช้งาน
                                            </span>
                                        </label>

                                        <button
                                            type="submit"
                                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                                            บันทึกการแก้ไข
                                        </button>
                                    </form>
                                </details>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'competition-admin.competitions.rubrics.destroy',
                                        [$competition, $rubric]
                                    ) }}"
                                    class="mt-4"
                                    onsubmit="return confirm('ยืนยันการลบเกณฑ์นี้หรือไม่?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                        ลบเกณฑ์
                                    </button>
                                </form>
                        @else
                            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs font-medium text-slate-500">
                                    เกณฑ์นี้อยู่ในโหมดอ่านอย่างเดียว
                                    เนื่องจากเริ่มการตัดสินแล้ว
                                </p>
                            </div>
                        @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <div class="mt-6">
            <a
                href="{{ route(
                    'competition-admin.competitions.show',
                    $competition
                ) }}"
                class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                ← กลับหน้ารายละเอียดการแข่งขัน
            </a>
        </div>
    </div>
@endsection