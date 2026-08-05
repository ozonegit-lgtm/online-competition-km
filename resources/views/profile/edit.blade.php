@extends('layouts.app')

@section('title', 'โปรไฟล์ของฉัน')

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-slate-900">
            โปรไฟล์ของฉัน
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            จัดการข้อมูลส่วนตัว รูปโปรไฟล์ และข้อมูลการติดต่อ
        </p>
    </div>
@endsection

@section('content')
    @php
        $profile = $user->adminProfile;

        $profileImageUrl = $profile?->avatar
            ? asset('storage/' . $profile->avatar)
            : null;

        $displayName = trim(
            ($profile?->first_name ?? '') . ' ' .
            ($profile?->last_name ?? '')
        );

        $displayName = $displayName !== ''
            ? $displayName
            : $user->username;

        $initial = mb_strtoupper(
            mb_substr(
                $profile?->first_name ?: $user->username,
                0,
                1
            )
        );
    @endphp

    <form
        method="POST"
        action="{{ route('profile.update') }}"
        enctype="multipart/form-data"
        class="mx-auto max-w-4xl"
    >
        @csrf
        @method('PUT')

        <div class="space-y-6">

            {{-- การ์ดรูปโปรไฟล์ --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200
                            bg-white shadow-sm">

                <div class="h-24 bg-gradient-to-r from-blue-600 to-sky-500"></div>

                <div class="px-6 pb-6 sm:px-8">

                    {{-- รูปโปรไฟล์ --}}
                    <div class="-mt-16 flex justify-center">
                        <div class="rounded-full bg-white p-1.5 shadow-md">

                            <div
                                id="profileImageFallback"
                                class="{{ $profileImageUrl ? 'hidden' : 'flex' }}
                                       h-32 w-32 items-center justify-center
                                       overflow-hidden rounded-full bg-blue-100
                                       text-4xl font-bold text-blue-700"
                            >
                                {{ $initial }}
                            </div>

                            <img
                                id="profileImagePreview"
                                src="{{ $profileImageUrl ?? '' }}"
                                alt="รูปโปรไฟล์ของ {{ $displayName }}"
                                class="{{ $profileImageUrl ? 'block' : 'hidden' }}
                                       h-32 w-32 rounded-full object-cover"
                            >
                        </div>
                    </div>

                    {{-- ชื่อและบทบาท --}}
                    <div class="mt-4 text-center">
                        <h2 class="text-xl font-bold text-slate-900">
                            {{ $displayName }}
                        </h2>

                        <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                            <span class="rounded-full border border-blue-200 bg-blue-50
                                         px-3 py-1 text-xs font-semibold text-blue-700">
                                {{ $user->role?->display_name ?? 'ไม่ระบุบทบาท' }}
                            </span>

                            @if ($profile?->position)
                                <span class="rounded-full border border-slate-200 bg-slate-50
                                             px-3 py-1 text-xs text-slate-600">
                                    {{ $profile->position }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- อัปโหลดรูป --}}
                    <div class="mx-auto mt-6 max-w-xl border-t border-slate-200 pt-5">

                        <input
                            type="file"
                            id="profile_image"
                            name="profile_image"
                            accept=".jpg,.jpeg,.png,.webp"
                            class="hidden"
                        >

                        <label
                            for="profile_image"
                            class="flex w-full cursor-pointer items-center
                                   justify-center gap-2 rounded-xl border
                                   border-blue-200 bg-blue-50 px-4 py-3
                                   text-sm font-semibold text-blue-700
                                   transition hover:border-blue-300
                                   hover:bg-blue-100"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M12 16V4"/>
                                <path d="M7 9l5-5 5 5"/>
                                <path d="M5 20h14a2 2 0 002-2v-5"/>
                                <path d="M3 13v5a2 2 0 002 2"/>
                            </svg>

                            เลือกรูปโปรไฟล์
                        </label>

                        <p
                            id="selectedFileName"
                            class="mt-3 truncate text-center text-xs
                                   font-medium text-slate-500"
                        >
                            ยังไม่ได้เลือกไฟล์ใหม่
                        </p>

                        <p class="mt-2 text-center text-xs leading-5 text-slate-400">
                            รองรับ JPG, JPEG, PNG และ WEBP
                            ขนาดไฟล์ไม่เกิน 5 MB
                        </p>

                        @error('profile_image')
                            <p class="mt-3 rounded-lg bg-red-50 px-3 py-2
                                      text-center text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- การ์ดข้อมูลบัญชี --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200
                            bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5 sm:px-8">
                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 shrink-0 items-center
                                    justify-center rounded-xl bg-slate-100
                                    text-slate-600">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M8 12h8"/>
                                <path d="M12 8v8"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                ข้อมูลบัญชี
                            </h2>

                            <p class="text-sm text-slate-500">
                                ชื่อผู้ใช้งานและอีเมลสำหรับเข้าสู่ระบบ
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-6 sm:p-8">

                    {{-- ชื่อผู้ใช้งาน --}}
                    <div>
                        <label
                            for="username"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            ชื่อผู้ใช้งาน
                        </label>

                        <input
                            type="text"
                            id="username"
                            value="{{ $user->username }}"
                            disabled
                            class="mt-2 w-full cursor-not-allowed rounded-xl
                                   border border-slate-200 bg-slate-100
                                   px-4 py-3 text-sm text-slate-500"
                        >

                        <p class="mt-2 text-xs text-slate-400">
                            ไม่สามารถแก้ไขชื่อผู้ใช้งานจากหน้านี้ได้
                        </p>
                    </div>

                    {{-- อีเมล --}}
                    <div>
                        <label
                            for="account_email"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            อีเมลบัญชี
                        </label>

                        <input
                            type="email"
                            id="account_email"
                            value="{{ $user->email }}"
                            disabled
                            class="mt-2 w-full cursor-not-allowed rounded-xl
                                   border border-slate-200 bg-slate-100
                                   px-4 py-3 text-sm text-slate-500"
                        >

                        <p class="mt-2 text-xs text-slate-400">
                            ไม่สามารถแก้ไขอีเมลจากหน้านี้ได้
                        </p>
                    </div>
                </div>
            </section>

            {{-- การ์ดข้อมูลส่วนตัว --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200
                            bg-white shadow-sm">

                <div class="border-b border-slate-200 px-6 py-5 sm:px-8">
                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 shrink-0 items-center
                                    justify-center rounded-xl bg-blue-100
                                    text-blue-700">
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M20 21a8 8 0 00-16 0"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                ข้อมูลส่วนตัวและการติดต่อ
                            </h2>

                            <p class="text-sm text-slate-500">
                                ข้อมูลนี้ใช้แสดงตัวตนภายในระบบ
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-6 sm:p-8">

                    {{-- ชื่อ --}}
                    <div>
                        <label
                            for="first_name"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            ชื่อ
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="{{ old('first_name', $profile?->first_name) }}"
                            placeholder="กรอกชื่อ"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-300
                                   px-4 py-3 text-sm outline-none transition
                                   focus:border-blue-500 focus:ring-4
                                   focus:ring-blue-100"
                        >

                        @error('first_name')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- นามสกุล --}}
                    <div>
                        <label
                            for="last_name"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            นามสกุล
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="{{ old('last_name', $profile?->last_name) }}"
                            placeholder="กรอกนามสกุล"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-300
                                   px-4 py-3 text-sm outline-none transition
                                   focus:border-blue-500 focus:ring-4
                                   focus:ring-blue-100"
                        >

                        @error('last_name')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- เบอร์โทรศัพท์ --}}
                    <div>
                        <label
                            for="phone"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            เบอร์โทรศัพท์
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $profile?->phone) }}"
                            placeholder="เช่น 0812345678"
                            class="mt-2 w-full rounded-xl border border-slate-300
                                   px-4 py-3 text-sm outline-none transition
                                   focus:border-blue-500 focus:ring-4
                                   focus:ring-blue-100"
                        >

                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- ตำแหน่ง --}}
                    <div>
                        <label
                            for="position"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            ตำแหน่ง
                        </label>

                        <input
                            type="text"
                            id="position"
                            name="position"
                            value="{{ old('position', $profile?->position) }}"
                            placeholder="เช่น นักวิชาการคอมพิวเตอร์"
                            class="mt-2 w-full rounded-xl border border-slate-300
                                   px-4 py-3 text-sm outline-none transition
                                   focus:border-blue-500 focus:ring-4
                                   focus:ring-blue-100"
                        >

                        @error('position')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- หน่วยงาน --}}
                    <div>
                        <label
                            for="organization"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            หน่วยงาน
                        </label>

                        <input
                            type="text"
                            id="organization"
                            name="organization"
                            value="{{ old('organization', $profile?->organization) }}"
                            placeholder="เช่น สำนักวิทยบริการและเทคโนโลยีสารสนเทศ"
                            class="mt-2 w-full rounded-xl border border-slate-300
                                   px-4 py-3 text-sm outline-none transition
                                   focus:border-blue-500 focus:ring-4
                                   focus:ring-blue-100"
                        >

                        @error('organization')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- ปุ่มบันทึก --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200
                            bg-slate-50 px-6 py-5 sm:flex-row sm:items-center
                            sm:justify-between sm:px-8">

                    <p class="text-xs text-slate-500">
                        <span class="text-red-500">*</span>
                        จำเป็นต้องกรอก
                    </p>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl bg-blue-600 px-6 py-3 text-sm
                               font-semibold text-white shadow-sm transition
                               hover:bg-blue-700 focus:outline-none
                               focus:ring-4 focus:ring-blue-200"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M5 3h12l2 2v16H5z"/>
                            <path d="M8 3v6h8V3"/>
                            <path d="M8 21v-7h8v7"/>
                        </svg>

                        บันทึกข้อมูลโปรไฟล์
                    </button>
                </div>
            </section>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('profile_image');
            const preview = document.getElementById('profileImagePreview');
            const fallback = document.getElementById('profileImageFallback');
            const fileName = document.getElementById('selectedFileName');

            if (!input || !preview || !fallback || !fileName) {
                return;
            }

            input.addEventListener('change', function () {
                const file = input.files[0];

                if (!file) {
                    fileName.textContent = 'ยังไม่ได้เลือกไฟล์ใหม่';
                    return;
                }

                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ];

                if (!allowedTypes.includes(file.type)) {
                    alert('รองรับเฉพาะไฟล์ JPG, JPEG, PNG และ WEBP');

                    input.value = '';
                    fileName.textContent = 'ยังไม่ได้เลือกไฟล์ใหม่';

                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('รูปโปรไฟล์ต้องมีขนาดไม่เกิน 5 MB');

                    input.value = '';
                    fileName.textContent = 'ยังไม่ได้เลือกไฟล์ใหม่';

                    return;
                }

                const imageUrl = URL.createObjectURL(file);

                preview.src = imageUrl;
                preview.classList.remove('hidden');
                preview.classList.add('block');

                fallback.classList.add('hidden');
                fallback.classList.remove('flex');

                fileName.textContent = file.name;
            });
        });
    </script>
@endpush