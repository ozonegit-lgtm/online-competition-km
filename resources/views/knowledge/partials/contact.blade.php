@if ($settings->contact_enabled)
    @php
        $contactHasFields = collect(['name', 'phone', 'email', 'message'])
            ->contains(fn ($field) => $settings->{"contact_{$field}_enabled"});
    @endphp

    <section id="contact" class="scroll-mt-[60px] bg-white lg:scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">

            {{-- Section Header --}}
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-teal-600">
                    CONTACT US
                </p>

                <h2 class="mt-2 text-xl font-bold leading-tight text-[#123b5d] sm:text-2xl lg:text-3xl">
                    {{ $settings->contact_title }}
                </h2>

                <div class="mx-auto mt-3 h-0.5 w-10 bg-amber-400"></div>

                @if ($settings->contact_description)
                    <p class="mx-auto mt-3 max-w-2xl whitespace-pre-line text-sm leading-6 text-slate-600 sm:text-base sm:leading-7">
                        {{ $settings->contact_description }}
                    </p>
                @endif
            </div>

            {{-- Success Message --}}
            @if (session('success'))
                <div
                    class="mx-auto mt-6 max-w-5xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    role="status"
                >
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="m8 12 2.5 2.5L16 9"></path>
                        </svg>

                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            {{-- Main Content --}}
            <div class="mt-8 grid gap-6 lg:grid-cols-2 lg:gap-8">

                {{-- Contact Information --}}
                <div class="min-w-0">

                    <div class="border-t-[3px] border-teal-500 bg-[#123b5d] p-5 text-white sm:p-6">
                        <div class="mb-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-300">
                                CONTACT INFORMATION
                            </p>

                            @if ($settings->organization_name)
                                <h3 class="mt-2 text-lg font-bold leading-snug sm:text-xl">
                                    {{ $settings->organization_name }}
                                </h3>
                            @endif
                        </div>

                        <dl class="space-y-4">

                            @if ($settings->address)
                                <div class="flex gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center bg-white/10 text-teal-300">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                                            <circle cx="12" cy="10" r="2.5"></circle>
                                        </svg>
                                    </div>

                                    <div class="min-w-0">
                                        <dt class="text-sm font-semibold text-white">
                                            ที่อยู่
                                        </dt>

                                        <dd class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-300">
                                            {{ $settings->address }}
                                        </dd>
                                    </div>
                                </div>
                            @endif

                            @if ($settings->phone)
                                <div class="flex gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center bg-white/10 text-teal-300">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"></path>
                                        </svg>
                                    </div>

                                    <div class="min-w-0">
                                        <dt class="text-sm font-semibold text-white">
                                            โทรศัพท์
                                        </dt>

                                        <dd class="mt-1 text-sm text-slate-300">
                                            <a
                                                href="tel:{{ $settings->phone }}"
                                                class="transition hover:text-teal-300"
                                            >
                                                {{ $settings->phone }}
                                            </a>
                                        </dd>
                                    </div>
                                </div>
                            @endif

                            @if ($settings->email)
                                <div class="flex gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center bg-white/10 text-teal-300">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                            <path d="m3 7 9 6 9-6"></path>
                                        </svg>
                                    </div>

                                    <div class="min-w-0">
                                        <dt class="text-sm font-semibold text-white">
                                            อีเมล
                                        </dt>

                                        <dd class="mt-1 break-all text-sm text-slate-300">
                                            <a
                                                href="mailto:{{ $settings->email }}"
                                                class="transition hover:text-teal-300"
                                            >
                                                {{ $settings->email }}
                                            </a>
                                        </dd>
                                    </div>
                                </div>
                            @endif

                        </dl>
                    </div>

                    {{-- Map --}}
                    @if ($settings->map_embed_url)
                        <div class="mt-4 overflow-hidden border border-slate-200 bg-slate-100 shadow-sm">
                            <div class="aspect-video">
                                <iframe
                                    src="{{ $settings->map_embed_url }}"
                                    title="แผนที่ {{ $settings->organization_name ?: $settings->site_name }}"
                                    class="h-full w-full border-0"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Contact Form --}}
                @if ($settings->contact_form_enabled && $contactHasFields)
                    <div class="min-w-0">

                        <div class="mb-4">
                            <div class="flex items-center gap-3">
                                <span class="h-px w-8 bg-teal-500"></span>

                                <p class="text-sm font-semibold text-teal-700">
                                    ส่งข้อความถึงเรา
                                </p>
                            </div>

                            <h3 class="mt-2 text-lg font-bold text-[#123b5d] sm:text-xl">
                                ติดต่อสอบถามข้อมูล
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                กรอกข้อมูลด้านล่างเพื่อส่งข้อความถึงเจ้าหน้าที่
                            </p>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('knowledge.contact.store') }}#contact"
                            class="border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
                        >
                            @csrf

                            @if ($errors->any())
                                <div
                                    class="mb-5 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                                    role="alert"
                                    aria-labelledby="contact-error-title"
                                >
                                    <p id="contact-error-title" class="font-semibold">
                                        ไม่สามารถส่งข้อความได้ กรุณาตรวจสอบข้อมูล
                                    </p>

                                    <ul class="mt-2 list-disc space-y-1 pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Honeypot --}}
                            <input
                                type="text"
                                name="website"
                                value=""
                                tabindex="-1"
                                autocomplete="off"
                                class="absolute -left-[9999px] h-px w-px overflow-hidden"
                                aria-hidden="true"
                            >

                            <div class="grid gap-4 sm:grid-cols-2">

                                @if ($settings->contact_name_enabled)
                                    <label class="block">
                                        <span class="text-sm font-semibold text-[#123b5d]">
                                            {{ $settings->contact_name_label }}

                                            @if ($settings->contact_name_required)
                                                <span class="text-red-600">*</span>
                                            @endif
                                        </span>

                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ old('name') }}"
                                            @required($settings->contact_name_required)
                                            class="mt-1.5 h-10 w-full border bg-white px-3 text-sm text-slate-700 outline-none transition
                                                @error('name')
                                                    border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100
                                                @else
                                                    border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100
                                                @enderror"
                                        >

                                        @error('name')
                                            <span class="mt-1.5 block text-xs text-red-600">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </label>
                                @endif

                                @if ($settings->contact_phone_enabled)
                                    <label class="block">
                                        <span class="text-sm font-semibold text-[#123b5d]">
                                            {{ $settings->contact_phone_label }}

                                            @if ($settings->contact_phone_required)
                                                <span class="text-red-600">*</span>
                                            @endif
                                        </span>

                                        <input
                                            type="tel"
                                            name="phone"
                                            value="{{ old('phone') }}"
                                            @required($settings->contact_phone_required)
                                            class="mt-1.5 h-10 w-full border bg-white px-3 text-sm text-slate-700 outline-none transition
                                                @error('phone')
                                                    border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100
                                                @else
                                                    border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100
                                                @enderror"
                                        >

                                        @error('phone')
                                            <span class="mt-1.5 block text-xs text-red-600">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </label>
                                @endif

                                @if ($settings->contact_email_enabled)
                                    <label class="block sm:col-span-2">
                                        <span class="text-sm font-semibold text-[#123b5d]">
                                            {{ $settings->contact_email_label }}

                                            @if ($settings->contact_email_required)
                                                <span class="text-red-600">*</span>
                                            @endif
                                        </span>

                                        <input
                                            type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            @required($settings->contact_email_required)
                                            class="mt-1.5 h-10 w-full border bg-white px-3 text-sm text-slate-700 outline-none transition
                                                @error('email')
                                                    border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100
                                                @else
                                                    border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100
                                                @enderror"
                                        >

                                        @error('email')
                                            <span class="mt-1.5 block text-xs text-red-600">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </label>
                                @endif

                                @if ($settings->contact_message_enabled)
                                    <label class="block sm:col-span-2">
                                        <span class="text-sm font-semibold text-[#123b5d]">
                                            {{ $settings->contact_message_label }}

                                            @if ($settings->contact_message_required)
                                                <span class="text-red-600">*</span>
                                            @endif
                                        </span>

                                        <textarea
                                            name="message"
                                            rows="5"
                                            @required($settings->contact_message_required)
                                            class="mt-1.5 w-full resize-y border bg-white px-3 py-2.5 text-sm leading-6 text-slate-700 outline-none transition
                                                @error('message')
                                                    border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100
                                                @else
                                                    border-slate-300 focus:border-teal-500 focus:ring-2 focus:ring-teal-100
                                                @enderror"
                                        >{{ old('message') }}</textarea>

                                        @error('message')
                                            <span class="mt-1.5 block text-xs text-red-600">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </label>
                                @endif

                            </div>

                            @error('website')
                                <p class="mt-4 border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                    ไม่สามารถส่งแบบฟอร์มได้ กรุณาลองใหม่
                                </p>
                            @enderror

                            <div class="mt-5">
                                <button
                                    type="submit"
                                    class="inline-flex h-10 w-full items-center justify-center gap-2 bg-teal-600 px-5 text-sm font-semibold text-white transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-300 sm:w-auto"
                                >
                                    {{ $settings->contact_submit_label }}

                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m22 2-7 20-4-9-9-4Z"></path>
                                        <path d="M22 2 11 13"></path>
                                    </svg>
                                </button>
                            </div>

                        </form>
                    </div>
                @endif

            </div>
        </div>
    </section>
@endif
