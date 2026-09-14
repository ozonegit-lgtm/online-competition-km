@extends('layouts.app')

@section('title', 'จัดการเมนูเว็บไซต์')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">จัดการเมนูเว็บไซต์</h1>
        <p class="mt-1 text-sm text-slate-500">
            จัดการเมนูที่แสดงบน Navbar, Footer และ Social links
        </p>
    </div>

    <a
        href="{{ route('knowledge.index') }}"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
    >
        เปิดหน้าเว็บไซต์
    </a>
</div>
@endsection

@section('content')
@php
    $inputClass = 'h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20';

    $destinationOptions = [
        '/knowledge' => 'หน้าแรก',
        '#about' => 'เกี่ยวกับ',
        '#books' => 'E-Book KM',
        '#contact' => 'ติดต่อ',
    ];

    $newItemUrl = old('url', '/knowledge');
    $newItemDestination = array_key_exists($newItemUrl, $destinationOptions)
        ? $newItemUrl
        : 'custom';
@endphp

<div class="mx-auto w-full max-w-6xl">

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            กรุณาตรวจสอบข้อมูลที่กรอกอีกครั้ง
        </div>
    @endif

    {{-- Add menu --}}
    <section class="rounded-xl border border-slate-200 bg-white shadow-sm" data-nav-items>
        <div class="px-5 py-4 sm:px-6">
            <h2 class="text-base font-semibold text-slate-900">เพิ่มเมนูใหม่</h2>
            <p class="mt-1 text-sm text-slate-500">
                เพิ่มลิงก์ใหม่สำหรับ Navbar, Footer หรือ Social
            </p>
        </div>

        <div class="border-t border-slate-200">
            <form
                method="POST"
                action="{{ route('superadmin.knowledge-page.nav-items.store') }}"
                class="p-5 sm:p-6"
            >
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <label>
                        <span class="text-sm font-medium text-slate-700">ตำแหน่ง</span>
                        <select name="placement" required class="mt-1.5 {{ $inputClass }}">
                            <option value="navbar" @selected(old('placement') === 'navbar')>Navbar</option>
                            <option value="footer" @selected(old('placement') === 'footer')>Footer</option>
                            <option value="social" @selected(old('placement') === 'social')>Social</option>
                        </select>
                    </label>

                    <label>
                        <span class="text-sm font-medium text-slate-700">ชื่อเมนู</span>
                        <input
                            name="label"
                            value="{{ old('label') }}"
                            required
                            placeholder="เช่น เกี่ยวกับเรา"
                            class="mt-1.5 {{ $inputClass }}"
                        >
                    </label>

                    <div
                        class="md:col-span-2"
                        data-nav-destination-field
                        data-selected-destination="{{ $newItemDestination }}"
                    >
                        <div class="grid gap-5 md:grid-cols-2">
                            <label>
                                <span class="text-sm font-medium text-slate-700">ปลายทาง</span>
                                <select data-nav-destination required class="mt-1.5 {{ $inputClass }}">
                                    @foreach ($destinationOptions as $url => $label)
                                        <option value="{{ $url }}" @selected($newItemDestination === $url)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                    <option value="custom" @selected($newItemDestination === 'custom')>
                                        ลิงก์กำหนดเอง
                                    </option>
                                </select>
                            </label>

                            <label
                                class="{{ $newItemDestination === 'custom' ? 'block' : 'hidden' }}"
                                data-nav-custom-container
                            >
                                <span class="text-sm font-medium text-slate-700">URL กำหนดเอง</span>
                                <input
                                    type="text"
                                    inputmode="url"
                                    value="{{ $newItemDestination === 'custom' ? $newItemUrl : '' }}"
                                    placeholder="#section, /page หรือ https://..."
                                    autocomplete="url"
                                    data-nav-custom-url
                                    class="mt-1.5 {{ $inputClass }}"
                                >
                            </label>
                        </div>

                        <input type="hidden" name="url" value="{{ $newItemUrl }}" data-nav-url>
                    </div>

                    <label>
                        <span class="text-sm font-medium text-slate-700">การเปิดลิงก์</span>
                        <select name="target" required class="mt-1.5 {{ $inputClass }}">
                            <option value="_self" @selected(old('target', '_self') === '_self')>เปิดหน้าเดิม</option>
                            <option value="_blank" @selected(old('target') === '_blank')>เปิดแท็บใหม่</option>
                        </select>
                    </label>

                    <label>
                        <span class="text-sm font-medium text-slate-700">Icon</span>
                        <select name="icon_key" class="mt-1.5 {{ $inputClass }}">
                            <option value="">ไม่ใช้ Icon</option>
                            @foreach (\App\Models\KnowledgePageNavItem::ICON_KEYS as $icon)
                                <option value="{{ $icon }}" @selected(old('icon_key') === $icon)>{{ $icon }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span class="text-sm font-medium text-slate-700">ลำดับ</span>
                        <input
                            type="number"
                            min="0"
                            name="sort_order"
                            value="{{ old('sort_order', 0) }}"
                            class="mt-1.5 {{ $inputClass }}"
                        >
                    </label>

                    <div>
                        <span class="text-sm font-medium text-slate-700">สถานะ</span>
                        <label class="mt-1.5 flex h-10 items-center gap-2">
                            <input type="hidden" name="is_visible" value="0">
                            <input
                                type="checkbox"
                                name="is_visible"
                                value="1"
                                @checked(old('is_visible', true))
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="text-sm text-slate-700">แสดงผลทันที</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end border-t border-slate-100 pt-5">
                    <button
                        type="submit"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        เพิ่มเมนู
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Menu list --}}
    <section class="mt-6">
        <div class="mb-3">
            <h2 class="text-base font-semibold text-slate-900">รายการเมนู</h2>
            <p class="mt-1 text-sm text-slate-500">
                กดที่รายการเพื่อดูและแก้ไขรายละเอียด
            </p>
        </div>

        <div class="space-y-3">
            @forelse ($items as $item)
                @php
                    $itemDestination = array_key_exists($item->url, $destinationOptions)
                        ? $item->url
                        : 'custom';

                    $placementLabel = match ($item->placement) {
                        'navbar' => 'Navbar',
                        'footer' => 'Footer',
                        'social' => 'Social',
                        default => ucfirst($item->placement),
                    };
                @endphp

                <details
                    class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                    data-nav-item="{{ $item->id }}"
                >
                    <summary class="cursor-pointer list-none px-5 py-4 sm:px-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-base font-semibold text-slate-900">
                                        {{ $item->label }}
                                    </h3>

                                    <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                        {{ $placementLabel }}
                                    </span>

                                    <span
                                        class="rounded-md px-2 py-1 text-xs font-medium
                                            {{ $item->is_visible
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'bg-slate-100 text-slate-500'
                                            }}"
                                    >
                                        {{ $item->is_visible ? 'กำลังแสดง' : 'ถูกซ่อน' }}
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                                    <span>
                                        ปลายทาง:
                                        <span class="font-medium text-slate-700">
                                            {{ $destinationOptions[$item->url] ?? $item->url }}
                                        </span>
                                    </span>

                                    <span>
                                        ลำดับ:
                                        <span class="font-medium text-slate-700">{{ $item->sort_order }}</span>
                                    </span>

                                    <span>
                                        เปิด:
                                        <span class="font-medium text-slate-700">
                                            {{ $item->target === '_blank' ? 'แท็บใหม่' : 'หน้าเดิม' }}
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-3 text-sm font-medium text-blue-600">
                                <span>แก้ไข</span>
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-4 w-4 transition group-open:rotate-180"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>
                            </div>
                        </div>
                    </summary>

                    <div class="border-t border-slate-200 bg-slate-50/40">
                        <form
                            method="POST"
                            action="{{ route('superadmin.knowledge-page.nav-items.update', $item) }}"
                            class="p-5 sm:p-6"
                        >
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="is_visible" value="{{ $item->is_visible ? 1 : 0 }}">

                            <div class="grid gap-5 md:grid-cols-2">
                                <label>
                                    <span class="text-sm font-medium text-slate-700">ตำแหน่ง</span>
                                    <select name="placement" class="mt-1.5 {{ $inputClass }}">
                                        <option value="navbar" @selected($item->placement === 'navbar')>Navbar</option>
                                        <option value="footer" @selected($item->placement === 'footer')>Footer</option>
                                        <option value="social" @selected($item->placement === 'social')>Social</option>
                                    </select>
                                </label>

                                <label>
                                    <span class="text-sm font-medium text-slate-700">ชื่อเมนู</span>
                                    <input
                                        name="label"
                                        value="{{ $item->label }}"
                                        required
                                        class="mt-1.5 {{ $inputClass }}"
                                    >
                                </label>

                                <div
                                    class="md:col-span-2"
                                    data-nav-destination-field
                                    data-selected-destination="{{ $itemDestination }}"
                                >
                                    <div class="grid gap-5 md:grid-cols-2">
                                        <label>
                                            <span class="text-sm font-medium text-slate-700">ปลายทาง</span>
                                            <select data-nav-destination class="mt-1.5 {{ $inputClass }}">
                                                @foreach ($destinationOptions as $url => $label)
                                                    <option value="{{ $url }}" @selected($itemDestination === $url)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                                <option value="custom" @selected($itemDestination === 'custom')>
                                                    ลิงก์กำหนดเอง
                                                </option>
                                            </select>
                                        </label>

                                        <label
                                            class="{{ $itemDestination === 'custom' ? 'block' : 'hidden' }}"
                                            data-nav-custom-container
                                        >
                                            <span class="text-sm font-medium text-slate-700">URL กำหนดเอง</span>
                                            <input
                                                type="text"
                                                inputmode="url"
                                                value="{{ $itemDestination === 'custom' ? $item->url : '' }}"
                                                placeholder="#section, /page หรือ https://..."
                                                autocomplete="url"
                                                data-nav-custom-url
                                                class="mt-1.5 {{ $inputClass }}"
                                            >
                                        </label>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="url"
                                        value="{{ $item->url }}"
                                        data-nav-url
                                    >
                                </div>

                                <label>
                                    <span class="text-sm font-medium text-slate-700">การเปิดลิงก์</span>
                                    <select name="target" class="mt-1.5 {{ $inputClass }}">
                                        <option value="_self" @selected($item->target === '_self')>เปิดหน้าเดิม</option>
                                        <option value="_blank" @selected($item->target === '_blank')>เปิดแท็บใหม่</option>
                                    </select>
                                </label>

                                <label>
                                    <span class="text-sm font-medium text-slate-700">Icon</span>
                                    <select name="icon_key" class="mt-1.5 {{ $inputClass }}">
                                        <option value="">ไม่ใช้ Icon</option>
                                        @foreach (\App\Models\KnowledgePageNavItem::ICON_KEYS as $icon)
                                            <option value="{{ $icon }}" @selected($item->icon_key === $icon)>
                                                {{ $icon }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <label>
                                    <span class="text-sm font-medium text-slate-700">ลำดับ</span>
                                    <input
                                        type="number"
                                        min="0"
                                        name="sort_order"
                                        value="{{ $item->sort_order }}"
                                        class="mt-1.5 {{ $inputClass }}"
                                    >
                                </label>
                            </div>

                            <div class="mt-6 flex justify-end border-t border-slate-200 pt-5">
                                <button
                                    type="submit"
                                    class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700"
                                >
                                    บันทึกการแก้ไข
                                </button>
                            </div>
                        </form>

                        <div class="flex flex-col gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <form
                                method="POST"
                                action="{{ route('superadmin.knowledge-page.nav-items.visibility', $item) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <input type="hidden" name="is_visible" value="{{ $item->is_visible ? 0 : 1 }}">

                                <button
                                    type="submit"
                                    class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    {{ $item->is_visible ? 'ซ่อนเมนู' : 'แสดงเมนู' }}
                                </button>
                            </form>

                            <form
                                method="POST"
                                action="{{ route('superadmin.knowledge-page.nav-items.destroy', $item) }}"
                                onsubmit="return confirm('ยืนยันการลบเมนูนี้?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="inline-flex h-9 items-center justify-center rounded-lg border border-red-200 bg-white px-3 text-sm font-medium text-red-700 hover:bg-red-50"
                                >
                                    ลบเมนู
                                </button>
                            </form>
                        </div>
                    </div>
                </details>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <p class="text-sm font-medium text-slate-700">ยังไม่มีเมนูเว็บไซต์</p>
                    <p class="mt-1 text-sm text-slate-500">เพิ่มเมนูแรกจากด้านบน</p>
                </div>
            @endforelse
        </div>

        @if ($items->hasPages())
            <div class="mt-5">
                {{ $items->links() }}
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-nav-destination-field]').forEach((field) => {
            const destination = field.querySelector('[data-nav-destination]');
            const customContainer = field.querySelector('[data-nav-custom-container]');
            const customUrl = field.querySelector('[data-nav-custom-url]');
            const url = field.querySelector('[data-nav-url]');

            const syncUrl = () => {
                const isCustom = destination.value === 'custom';

                customContainer.classList.toggle('hidden', !isCustom);
                customUrl.disabled = !isCustom;
                customUrl.required = isCustom;
                url.value = isCustom ? customUrl.value : destination.value;
            };

            destination.addEventListener('change', syncUrl);
            customUrl.addEventListener('input', syncUrl);

            syncUrl();
        });
    });
</script>
@endpush
