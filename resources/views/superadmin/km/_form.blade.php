@php
    $editing = isset($knowledgeItem);

    $inputClass = 'mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10';
@endphp

<div class="space-y-5">

    {{-- =========================================================
        BASIC INFORMATION
    ========================================================== --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-800">ข้อมูลองค์ความรู้</h2>
                    <p class="mt-0.5 text-xs leading-5 text-slate-500">
                        ระบุชื่อและหมวดหมู่เพื่อให้ค้นหาและจัดกลุ่มข้อมูลได้ง่าย
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-5 p-5 lg:grid-cols-2">
            {{-- Title --}}
            <div class="lg:col-span-2">
                <label for="title" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 6h16M4 12h10M4 18h7"/>
                    </svg>
                    ชื่อองค์ความรู้
                    <span class="text-red-500">*</span>
                </label>

                <input
                    id="title"
                    name="title"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('title', $knowledgeItem->title ?? '') }}"
                    placeholder="เช่น แนวทางการพัฒนาระบบจัดการองค์ความรู้"
                    class="{{ $inputClass }} h-11 @error('title') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                >

                <div class="mt-1.5 flex items-start justify-between gap-3">
                    <p class="text-xs text-slate-400">ใช้ชื่อที่สั้น กระชับ และสื่อถึงเนื้อหาหลัก</p>

                    @error('title')
                        <p class="shrink-0 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Category --}}
            <div class="lg:col-span-2">
                <label for="category_id" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    หมวดหมู่
                    <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="{{ $inputClass }} h-11 appearance-none pr-10 @error('category_id') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                    >
                        <option value="">เลือกหมวดหมู่</option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected((string) old('category_id', $knowledgeItem->category_id ?? '') === (string) $category->id)
                            >
                                {{ $category->category_name }}{{ $category->is_active ? '' : ' (ปิดใช้งาน)' }}
                            </option>
                        @endforeach
                    </select>

                    <svg
                        class="pointer-events-none absolute right-3 top-1/2 mt-1 h-4 w-4 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </div>

                @error('category_id')
                    <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>


    {{-- =========================================================
        CONTENT
    ========================================================== --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M4 5h16M4 12h16M4 19h10"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-800">รายละเอียดเนื้อหา</h2>
                    <p class="mt-0.5 text-xs leading-5 text-slate-500">
                        บทสรุปใช้สำหรับอธิบายภาพรวม ส่วนเนื้อหาใช้เก็บรายละเอียดฉบับเต็ม
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-5 p-5">
            {{-- Summary --}}
            <div>
                <div class="flex items-center justify-between gap-3">
                    <label for="summary" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M8 6h13M8 12h13M8 18h9"/>
                            <path d="M3 6h.01M3 12h.01M3 18h.01"/>
                        </svg>
                        บทสรุป
                    </label>

                    <span class="text-[11px] text-slate-400">ไม่บังคับ</span>
                </div>

                <textarea
                    id="summary"
                    name="summary"
                    rows="4"
                    placeholder="สรุปสาระสำคัญขององค์ความรู้แบบสั้น ๆ"
                    class="{{ $inputClass }} min-h-[110px] resize-y py-3 leading-6 @error('summary') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                >{{ old('summary', $knowledgeItem->summary ?? '') }}</textarea>

                @error('summary')
                    <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Content --}}
            <div>
                <div class="flex items-center justify-between gap-3">
                    <label for="content" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M6 3h9l3 3v15H6z"/>
                            <path d="M14 3v4h4M9 11h6M9 15h6"/>
                        </svg>
                        เนื้อหา
                    </label>

                    <span class="text-[11px] text-slate-400">รายละเอียดฉบับเต็ม</span>
                </div>

                <textarea
                    id="content"
                    name="content"
                    rows="10"
                    placeholder="เขียนรายละเอียดองค์ความรู้..."
                    class="{{ $inputClass }} min-h-[240px] resize-y py-3 leading-6 @error('content') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                >{{ old('content', $knowledgeItem->content ?? '') }}</textarea>

                @error('content')
                    <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>


    {{-- =========================================================
        FILES
    ========================================================== --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21.4 11.6 12 21l-9-9 9-9 5.4 5.4"/>
                        <circle cx="15" cy="9" r="1"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-800">รูปปกและไฟล์แนบ</h2>
                    <p class="mt-0.5 text-xs leading-5 text-slate-500">
                        เพิ่มสื่อประกอบเพื่อให้ผู้ใช้งานเข้าใจและเข้าถึงข้อมูลได้ง่ายขึ้น
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-5 p-5 lg:grid-cols-2">
            {{-- Cover Image --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm ring-1 ring-slate-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="16" rx="2"/>
                            <circle cx="8.5" cy="9" r="1.5"/>
                            <path d="m4 17 5-5 4 4 2-2 5 4"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <label for="cover_image" class="block text-sm font-semibold text-slate-700">
                            รูปปก
                        </label>
                        <p class="mt-0.5 text-xs leading-5 text-slate-500">
                            JPG, JPEG, PNG หรือ WEBP ไม่เกิน 10 MB
                        </p>
                    </div>
                </div>

                <input
                    id="cover_image"
                    name="cover_image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="mt-4 block w-full cursor-pointer rounded-xl border border-dashed border-slate-300 bg-white p-2 text-xs text-slate-500 file:mr-3 file:h-8 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-0 file:text-xs file:font-semibold file:text-emerald-700 hover:border-emerald-300 hover:file:bg-emerald-100 focus:outline-none focus:ring-4 focus:ring-emerald-500/10"
                >

                @error('cover_image')
                    <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror

                @if ($editing && $knowledgeItem->cover_image)
                    @php
                        $coverUrl = $knowledgeItem->cover_image_url;
                    @endphp

                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <div class="border-b border-slate-100 px-3 py-2">
                            <p class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                                <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m5 12 4 4L19 6"/>
                                </svg>
                                รูปปกปัจจุบัน
                            </p>
                        </div>

                        <div class="flex h-44 items-center justify-center bg-slate-50 p-3">
                            <img
                                src="{{ $coverUrl }}"
                                alt="รูปปกปัจจุบัน"
                                class="max-h-full max-w-full rounded-lg object-contain"
                            >
                        </div>

                        <label class="flex cursor-pointer items-center gap-2 border-t border-slate-100 px-3 py-2.5 text-xs font-medium text-red-600 transition hover:bg-red-50">
                            <input
                                type="checkbox"
                                name="remove_cover_image"
                                value="1"
                                @checked(old('remove_cover_image'))
                                class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                            >
                            ลบรูปปกปัจจุบัน
                        </label>
                    </div>
                @endif
            </div>

            {{-- Attachment --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm ring-1 ring-slate-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m21.4 11.6-8.9 8.9a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7L9.7 17.7a2 2 0 0 1-2.8-2.8l8.5-8.5"/>
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <label for="attachment" class="block text-sm font-semibold text-slate-700">
                            ไฟล์แนบ
                        </label>
                        <p class="mt-0.5 text-xs leading-5 text-slate-500">
                            รูปภาพ, PDF, Word, PowerPoint หรือ ZIP ไม่เกิน 10 MB
                        </p>
                    </div>
                </div>

                <input
                    id="attachment"
                    name="attachment"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.ppt,.pptx,.zip"
                    class="mt-4 block w-full cursor-pointer rounded-xl border border-dashed border-slate-300 bg-white p-2 text-xs text-slate-500 file:mr-3 file:h-8 file:cursor-pointer file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-0 file:text-xs file:font-semibold file:text-blue-700 hover:border-blue-300 hover:file:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                >

                @error('attachment')
                    <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror

                @if ($editing && $knowledgeItem->attachment_path)
                    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <div class="flex items-center gap-3 p-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M6 2h9l3 3v17H6z"/>
                                    <path d="M14 2v4h4"/>
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] font-medium text-slate-400">ไฟล์ปัจจุบัน</p>
                                <p class="mt-0.5 truncate text-xs font-semibold text-slate-700" title="{{ $knowledgeItem->attachment_original_name ?: 'ไฟล์แนบ' }}">
                                    {{ $knowledgeItem->attachment_original_name ?: 'ไฟล์แนบ' }}
                                </p>
                            </div>
                        </div>

                        <label class="flex cursor-pointer items-center gap-2 border-t border-slate-100 px-3 py-2.5 text-xs font-medium text-red-600 transition hover:bg-red-50">
                            <input
                                type="checkbox"
                                name="remove_attachment"
                                value="1"
                                @checked(old('remove_attachment'))
                                class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"
                            >
                            ลบไฟล์แนบปัจจุบัน
                        </label>
                    </div>
                @endif
            </div>
        </div>
    </section>

</div>
