@extends('layouts.app')

@section('title', 'ตั้งค่าหน้า E-Book KM')

@section('header')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">ตั้งค่าหน้า E-Book KM</h1>
        <p class="mt-1 text-sm text-slate-500">
            จัดการข้อมูลและส่วนประกอบที่แสดงบนหน้า E-Book KM
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
    $inputClass = 'mt-1.5 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20';
    $textareaClass = 'mt-1.5 w-full resize-y rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20';
    $fileClass = 'mt-2 block w-full rounded-lg border border-slate-300 bg-white p-2 text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200';
    $labelClass = 'text-sm font-medium text-slate-700';
    $helpClass = 'mt-1 text-xs leading-5 text-slate-500';
@endphp

<div class="mx-auto w-full max-w-6xl">

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            กรุณาตรวจสอบข้อมูลที่กรอกอีกครั้ง
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('superadmin.knowledge-page.settings.update') }}"
        enctype="multipart/form-data"
        class="space-y-5"
    >
        @csrf
        @method('PUT')

        {{-- Brand --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-base font-semibold text-slate-900">ข้อมูลเว็บไซต์</h2>
                <p class="mt-1 text-sm text-slate-500">ชื่อเว็บไซต์และโลโก้ที่ใช้บน Navbar</p>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <label class="block">
                    <span class="{{ $labelClass }}">ชื่อเว็บไซต์ <span class="text-red-500">*</span></span>
                    <input
                        name="site_name"
                        value="{{ old('site_name', $settings->site_name) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                    @error('site_name')
                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <div>
                    <span class="{{ $labelClass }}">โลโก้ Navbar</span>

                    @if ($settings->site_logo_path)
                        <div class="mt-2 flex h-24 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <img
                                src="{{ route('knowledge.assets.show', 'logo') }}"
                                alt="โลโก้ปัจจุบัน"
                                class="max-h-16 max-w-full object-contain"
                            >
                        </div>
                    @endif

                    <input
                        id="site_logo"
                        type="file"
                        name="site_logo"
                        accept="image/jpeg,image/png,image/webp"
                        class="{{ $fileClass }}"
                    >

                    @if ($settings->site_logo_path)
                        <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                            <input
                                type="checkbox"
                                name="remove_site_logo"
                                value="1"
                                class="rounded border-slate-300 text-red-600 focus:ring-red-500"
                            >
                            ลบโลโก้ปัจจุบัน
                        </label>
                    @endif

                    @error('site_logo')
                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Hero --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Hero / Banner</h2>
                    <p class="mt-1 text-sm text-slate-500">ส่วนหัวหลักของหน้า E-Book KM</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="hero_enabled" value="0">
                    <input
                        type="checkbox"
                        name="hero_enabled"
                        value="1"
                        @checked(old('hero_enabled', $settings->hero_enabled))
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    เปิดใช้งาน
                </label>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <label>
                    <span class="{{ $labelClass }}">หัวข้อ <span class="text-red-500">*</span></span>
                    <input
                        name="hero_title"
                        value="{{ old('hero_title', $settings->hero_title) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                    @error('hero_title')
                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label>
                    <span class="{{ $labelClass }}">ลำดับ Section</span>
                    <input
                        type="number"
                        min="0"
                        name="hero_sort_order"
                        value="{{ old('hero_sort_order', $settings->hero_sort_order) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label class="md:col-span-2">
                    <span class="{{ $labelClass }}">คำอธิบาย</span>
                    <textarea
                        name="hero_description"
                        rows="4"
                        class="{{ $textareaClass }}"
                    >{{ old('hero_description', $settings->hero_description) }}</textarea>
                </label>

                <div>
                    <span class="{{ $labelClass }}">ภาพ Hero</span>

                    @if ($settings->hero_image_path)
                        <img
                            src="{{ route('knowledge.assets.show', 'hero') }}"
                            alt="ภาพ Hero ปัจจุบัน"
                            class="mt-2 aspect-[16/7] w-full rounded-lg border border-slate-200 object-cover"
                        >
                    @endif

                    <input
                        id="hero_image"
                        type="file"
                        name="hero_image"
                        accept="image/jpeg,image/png,image/webp"
                        class="{{ $fileClass }}"
                    >

                    @if ($settings->hero_image_path)
                        <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                            <input
                                type="checkbox"
                                name="remove_hero_image"
                                value="1"
                                class="rounded border-slate-300 text-red-600 focus:ring-red-500"
                            >
                            ลบภาพปัจจุบัน
                        </label>
                    @endif
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="{{ $labelClass }}">ปุ่ม Hero</p>
                        <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
                            <input type="hidden" name="hero_button_enabled" value="0">
                            <input
                                type="checkbox"
                                name="hero_button_enabled"
                                value="1"
                                @checked(old('hero_button_enabled', $settings->hero_button_enabled))
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >
                            แสดงปุ่ม
                        </label>
                    </div>

                    <label class="block">
                        <span class="{{ $labelClass }}">ข้อความปุ่ม</span>
                        <input
                            name="hero_button_label"
                            value="{{ old('hero_button_label', $settings->hero_button_label) }}"
                            class="{{ $inputClass }}"
                        >
                    </label>

                    <label class="block">
                        <span class="{{ $labelClass }}">URL ปุ่ม</span>
                        <input
                            name="hero_button_url"
                            value="{{ old('hero_button_url', $settings->hero_button_url) }}"
                            placeholder="#books หรือ https://..."
                            class="{{ $inputClass }}"
                        >
                        @error('hero_button_url')
                            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </label>
                </div>
            </div>
        </section>

        {{-- About --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">เกี่ยวกับเรา</h2>
                    <p class="mt-1 text-sm text-slate-500">ข้อมูลแนะนำหน่วยงานหรือระบบ KM</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="about_enabled" value="0">
                    <input
                        type="checkbox"
                        name="about_enabled"
                        value="1"
                        @checked(old('about_enabled', $settings->about_enabled))
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    เปิดใช้งาน
                </label>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <label>
                    <span class="{{ $labelClass }}">หัวข้อ <span class="text-red-500">*</span></span>
                    <input
                        name="about_title"
                        value="{{ old('about_title', $settings->about_title) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                </label>

                <label>
                    <span class="{{ $labelClass }}">ลำดับ Section</span>
                    <input
                        type="number"
                        min="0"
                        name="about_sort_order"
                        value="{{ old('about_sort_order', $settings->about_sort_order) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label class="md:col-span-2">
                    <span class="{{ $labelClass }}">เนื้อหา</span>
                    <textarea
                        name="about_content"
                        rows="6"
                        class="{{ $textareaClass }}"
                    >{{ old('about_content', $settings->about_content) }}</textarea>
                </label>

                <div class="md:col-span-2">
                    <span class="{{ $labelClass }}">ภาพ About</span>

                    @if ($settings->about_image_path)
                        <img
                            src="{{ route('knowledge.assets.show', 'about') }}"
                            alt="ภาพ About ปัจจุบัน"
                            class="mt-2 h-40 max-w-md rounded-lg border border-slate-200 object-cover"
                        >
                    @endif

                    <input
                        id="about_image"
                        type="file"
                        name="about_image"
                        accept="image/jpeg,image/png,image/webp"
                        class="{{ $fileClass }}"
                    >

                    @if ($settings->about_image_path)
                        <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                            <input
                                type="checkbox"
                                name="remove_about_image"
                                value="1"
                                class="rounded border-slate-300 text-red-600 focus:ring-red-500"
                            >
                            ลบภาพปัจจุบัน
                        </label>
                    @endif
                </div>
            </div>
        </section>

        {{-- E-Book --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">E-Book Section</h2>
                    <p class="mt-1 text-sm text-slate-500">หัวข้อและคำอธิบายของคลัง E-Book</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="books_enabled" value="0">
                    <input
                        type="checkbox"
                        name="books_enabled"
                        value="1"
                        @checked(old('books_enabled', $settings->books_enabled))
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    เปิดใช้งาน
                </label>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <label>
                    <span class="{{ $labelClass }}">หัวข้อ <span class="text-red-500">*</span></span>
                    <input
                        name="books_title"
                        value="{{ old('books_title', $settings->books_title) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                </label>

                <label>
                    <span class="{{ $labelClass }}">ลำดับ Section</span>
                    <input
                        type="number"
                        min="0"
                        name="books_sort_order"
                        value="{{ old('books_sort_order', $settings->books_sort_order) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label class="md:col-span-2">
                    <span class="{{ $labelClass }}">คำอธิบาย</span>
                    <textarea
                        name="books_description"
                        rows="3"
                        class="{{ $textareaClass }}"
                    >{{ old('books_description', $settings->books_description) }}</textarea>
                </label>
            </div>
        </section>

        {{-- Contact --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">ข้อมูลติดต่อ</h2>
                    <p class="mt-1 text-sm text-slate-500">ข้อมูลหน่วยงาน ที่อยู่ โทรศัพท์ อีเมล และแผนที่</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="contact_enabled" value="0">
                    <input
                        type="checkbox"
                        name="contact_enabled"
                        value="1"
                        @checked(old('contact_enabled', $settings->contact_enabled))
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    เปิดใช้งาน
                </label>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <label>
                    <span class="{{ $labelClass }}">หัวข้อ <span class="text-red-500">*</span></span>
                    <input
                        name="contact_title"
                        value="{{ old('contact_title', $settings->contact_title) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                </label>

                <label>
                    <span class="{{ $labelClass }}">ลำดับ Section</span>
                    <input
                        type="number"
                        min="0"
                        name="contact_sort_order"
                        value="{{ old('contact_sort_order', $settings->contact_sort_order) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label class="md:col-span-2">
                    <span class="{{ $labelClass }}">คำอธิบาย</span>
                    <textarea
                        name="contact_description"
                        rows="3"
                        class="{{ $textareaClass }}"
                    >{{ old('contact_description', $settings->contact_description) }}</textarea>
                </label>

                <label>
                    <span class="{{ $labelClass }}">ชื่อหน่วยงาน</span>
                    <input
                        name="organization_name"
                        value="{{ old('organization_name', $settings->organization_name) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label>
                    <span class="{{ $labelClass }}">โทรศัพท์</span>
                    <input
                        name="phone"
                        value="{{ old('phone', $settings->phone) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label>
                    <span class="{{ $labelClass }}">อีเมล</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $settings->email) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label>
                    <span class="{{ $labelClass }}">Google Maps Embed URL</span>
                    <input
                        name="map_embed_url"
                        value="{{ old('map_embed_url', $settings->map_embed_url) }}"
                        placeholder="https://www.google.com/maps/embed?..."
                        class="{{ $inputClass }}"
                    >
                    @error('map_embed_url')
                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="md:col-span-2">
                    <span class="{{ $labelClass }}">ที่อยู่</span>
                    <textarea
                        name="address"
                        rows="3"
                        class="{{ $textareaClass }}"
                    >{{ old('address', $settings->address) }}</textarea>
                </label>
            </div>
        </section>

        {{-- Contact form --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">แบบฟอร์มติดต่อ</h2>
                    <p class="mt-1 text-sm text-slate-500">กำหนดช่องข้อมูลที่แสดงและช่องที่บังคับกรอก</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="contact_form_enabled" value="0">
                    <input
                        type="checkbox"
                        name="contact_form_enabled"
                        value="1"
                        @checked(old('contact_form_enabled', $settings->contact_form_enabled))
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    เปิดแบบฟอร์ม
                </label>
            </div>

            <div class="p-5 sm:p-6">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-medium uppercase tracking-wide text-slate-500">
                                <th class="pb-3 pr-4">ฟิลด์</th>
                                <th class="pb-3 pr-4">Label</th>
                                <th class="w-28 pb-3 text-center">เปิดใช้</th>
                                <th class="w-28 pb-3 text-center">บังคับกรอก</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach (['name' => 'ชื่อ', 'phone' => 'โทรศัพท์', 'email' => 'อีเมล', 'message' => 'ข้อความ'] as $field => $fallbackLabel)
                                <tr>
                                    <td class="py-3 pr-4 font-medium text-slate-700">
                                        {{ $fallbackLabel }}
                                    </td>

                                    <td class="py-3 pr-4">
                                        <input
                                            name="contact_{{ $field }}_label"
                                            value="{{ old("contact_{$field}_label", $settings->{"contact_{$field}_label"}) }}"
                                            required
                                            class="h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                        >
                                    </td>

                                    <td class="py-3 text-center">
                                        <input type="hidden" name="contact_{{ $field }}_enabled" value="0">
                                        <input
                                            type="checkbox"
                                            name="contact_{{ $field }}_enabled"
                                            value="1"
                                            @checked(old("contact_{$field}_enabled", $settings->{"contact_{$field}_enabled"}))
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        >
                                    </td>

                                    <td class="py-3 text-center">
                                        <input type="hidden" name="contact_{{ $field }}_required" value="0">
                                        <input
                                            type="checkbox"
                                            name="contact_{{ $field }}_required"
                                            value="1"
                                            @checked(old("contact_{$field}_required", $settings->{"contact_{$field}_required"}))
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <label class="mt-5 block max-w-md">
                    <span class="{{ $labelClass }}">ข้อความปุ่มส่ง <span class="text-red-500">*</span></span>
                    <input
                        name="contact_submit_label"
                        value="{{ old('contact_submit_label', $settings->contact_submit_label) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                </label>
            </div>
        </section>

        {{-- Footer --}}
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Footer</h2>
                    <p class="mt-1 text-sm text-slate-500">ข้อมูลที่แสดงบริเวณส่วนท้ายของเว็บไซต์</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="footer_enabled" value="0">
                    <input
                        type="checkbox"
                        name="footer_enabled"
                        value="1"
                        @checked(old('footer_enabled', $settings->footer_enabled))
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    เปิดใช้งาน
                </label>
            </div>

            <div class="grid gap-6 p-5 sm:p-6 md:grid-cols-2">
                <div>
                    <span class="{{ $labelClass }}">โลโก้ Footer</span>

                    @if ($settings->footer_logo_path)
                        <div class="mt-2 flex h-24 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <img
                                src="{{ route('knowledge.assets.show', 'footer-logo') }}"
                                alt="โลโก้ Footer ปัจจุบัน"
                                class="max-h-16 max-w-full object-contain"
                            >
                        </div>
                    @endif

                    <input
                        id="footer_logo"
                        type="file"
                        name="footer_logo"
                        accept="image/jpeg,image/png,image/webp"
                        class="{{ $fileClass }}"
                    >

                    @if ($settings->footer_logo_path)
                        <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                            <input
                                type="checkbox"
                                name="remove_footer_logo"
                                value="1"
                                class="rounded border-slate-300 text-red-600 focus:ring-red-500"
                            >
                            ลบโลโก้ปัจจุบัน
                        </label>
                    @endif
                </div>

                <label>
                    <span class="{{ $labelClass }}">ชื่อหน่วยงาน</span>
                    <input
                        name="footer_organization_name"
                        value="{{ old('footer_organization_name', $settings->footer_organization_name) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label class="md:col-span-2">
                    <span class="{{ $labelClass }}">คำอธิบาย</span>
                    <textarea
                        name="footer_description"
                        rows="3"
                        class="{{ $textareaClass }}"
                    >{{ old('footer_description', $settings->footer_description) }}</textarea>
                </label>

                <label class="md:col-span-2">
                    <span class="{{ $labelClass }}">ที่อยู่</span>
                    <textarea
                        name="footer_address"
                        rows="2"
                        class="{{ $textareaClass }}"
                    >{{ old('footer_address', $settings->footer_address) }}</textarea>
                </label>

                <label>
                    <span class="{{ $labelClass }}">โทรศัพท์</span>
                    <input
                        name="footer_phone"
                        value="{{ old('footer_phone', $settings->footer_phone) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label>
                    <span class="{{ $labelClass }}">อีเมล</span>
                    <input
                        type="email"
                        name="footer_email"
                        value="{{ old('footer_email', $settings->footer_email) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <label class="md:col-span-2">
                    <span class="{{ $labelClass }}">Copyright</span>
                    <input
                        name="footer_copyright"
                        value="{{ old('footer_copyright', $settings->footer_copyright) }}"
                        class="{{ $inputClass }}"
                    >
                </label>

                <div class="md:col-span-2">
                    <p class="{{ $labelClass }}">ข้อมูลที่ต้องการแสดง</p>

                    <div class="mt-2 flex flex-wrap gap-x-6 gap-y-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                        @foreach ([
                            'footer_show_logo' => 'แสดงโลโก้',
                            'footer_show_description' => 'แสดงคำอธิบาย',
                            'footer_show_address' => 'แสดงที่อยู่',
                            'footer_show_phone' => 'แสดงโทรศัพท์',
                            'footer_show_email' => 'แสดงอีเมล'
                        ] as $field => $label)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="hidden" name="{{ $field }}" value="0">
                                <input
                                    type="checkbox"
                                    name="{{ $field }}"
                                    value="1"
                                    @checked(old($field, $settings->{$field}))
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                >
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Actions --}}
        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
            <a
                href="{{ route('superadmin.dashboard') }}"
                class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                ยกเลิก
            </a>

            <button
                type="submit"
                class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
            >
                บันทึกการตั้งค่า
            </button>
        </div>
    </form>
</div>
@endsection
