@php
    $editing = isset($ebook);
    $inputClass = 'mt-1.5 h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20';
    $textareaClass = 'mt-1.5 w-full resize-y rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20';
    $labelClass = 'text-sm font-medium text-slate-700';
    $fileClass = 'mt-2 block w-full rounded-lg border border-slate-300 bg-white p-2 text-xs text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200';
@endphp

<div class="space-y-5">
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-5">
            {{-- ข้อมูลพื้นฐาน --}}
            <section class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                    <h3 class="text-sm font-semibold text-slate-900">ข้อมูลพื้นฐาน</h3>
                    <p class="mt-0.5 text-xs text-slate-500">ข้อมูลหลักที่ใช้แสดงในรายการ E-Book</p>
                </div>
                <div class="grid gap-4 p-4 sm:p-5 md:grid-cols-2">
                    <label class="md:col-span-2">
                        <span class="{{ $labelClass }}">ชื่อ E-Book <span class="text-red-500">*</span></span>
                        <input name="title" value="{{ old('title', $ebook->title ?? '') }}" required placeholder="กรอกชื่อ E-Book" class="{{ $inputClass }}">
                        @error('title')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="{{ $labelClass }}">หมวดหมู่ <span class="text-red-500">*</span></span>
                        <select name="knowledge_category_id" required class="{{ $inputClass }}">
                            <option value="">เลือกหมวดหมู่</option>
                            @if ($editing && $ebook->knowledgeCategory && ! $categories->contains('id', $ebook->knowledge_category_id))
                                <option value="{{ $ebook->knowledge_category_id }}" selected>{{ $ebook->knowledgeCategory->name }} (ปิดใช้งาน)</option>
                            @endif
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('knowledge_category_id', $ebook->knowledge_category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('knowledge_category_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="{{ $labelClass }}">ปีเผยแพร่</span>
                        <input type="number" min="1" max="65535" name="publication_year" value="{{ old('publication_year', $ebook->publication_year ?? '') }}" placeholder="เช่น 2026" class="{{ $inputClass }}">
                        @error('publication_year')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="{{ $labelClass }}">เล่ม (Volume)</span>
                        <input name="volume" maxlength="50" value="{{ old('volume', $ebook->volume ?? '') }}" placeholder="เช่น 1" class="{{ $inputClass }}">
                        @error('volume')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="{{ $labelClass }}">ฉบับ (Issue)</span>
                        <input name="issue" maxlength="50" value="{{ old('issue', $ebook->issue ?? '') }}" placeholder="เช่น 2" class="{{ $inputClass }}">
                        @error('issue')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="md:max-w-xs">
                        <span class="{{ $labelClass }}">ลำดับการแสดง</span>
                        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $ebook->sort_order ?? 0) }}" class="{{ $inputClass }}">
                        <span class="mt-1 block text-[11px] text-slate-400">เลขน้อยจะแสดงก่อน</span>
                        @error('sort_order')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>

            {{-- รายละเอียด --}}
            <section class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                    <h3 class="text-sm font-semibold text-slate-900">รายละเอียดเนื้อหา</h3>
                    <p class="mt-0.5 text-xs text-slate-500">ข้อความสรุปและเนื้อหาที่ใช้ในหน้ารายละเอียด E-Book</p>
                </div>
                <div class="space-y-4 p-4 sm:p-5">
                    <label class="block">
                        <span class="{{ $labelClass }}">รายละเอียดสั้น</span>
                        <textarea name="summary" rows="4" placeholder="สรุปเนื้อหาแบบสั้นสำหรับแสดงในรายการ" class="{{ $textareaClass }}">{{ old('summary', $ebook->summary ?? '') }}</textarea>
                        @error('summary')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="{{ $labelClass }}">เนื้อหา</span>
                        <textarea name="content" rows="9" placeholder="กรอกรายละเอียดหรือเนื้อหาเพิ่มเติมของ E-Book" class="{{ $textareaClass }}">{{ old('content', $ebook->content ?? '') }}</textarea>
                        @error('content')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
            </section>
        </div>

        <aside class="space-y-5">
            {{-- รูปปก --}}
            <section class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-slate-900">รูปปก</h3>
                    <p class="mt-0.5 text-xs text-slate-500">JPEG, PNG หรือ WebP</p>
                </div>
                <div class="p-4">
                    @if ($editing && $ebook->cover_image_url)
                        <div class="flex justify-center rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <img src="{{ $ebook->cover_image_url }}" alt="ปกปัจจุบัน" class="max-h-56 max-w-full rounded-md object-contain">
                        </div>
                    @else
                        <div class="flex h-40 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 text-center">
                            <div class="px-4">
                                <svg class="mx-auto h-8 w-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <path d="m21 15-5-5L5 21"></path>
                                </svg>
                                <p class="mt-2 text-xs text-slate-400">ยังไม่มีรูปปก</p>
                            </div>
                        </div>
                    @endif

                    <input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="{{ $fileClass }}">
                    @if ($editing && $ebook->cover_image)
                        <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                            <input type="checkbox" name="remove_cover_image" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                            ลบรูปปกเดิม
                        </label>
                    @endif
                    @error('cover_image')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </div>
            </section>

            {{-- ช่องทางอ่าน --}}
            <section class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-4 py-3">
                    <h3 class="text-sm font-semibold text-slate-900">ช่องทางอ่าน</h3>
                    <p class="mt-0.5 text-xs leading-5 text-slate-500">ต้องมี PDF หรือ External URL อย่างน้อยหนึ่งรายการ</p>
                </div>
                <div class="space-y-4 p-4">
                    <div>
                        <label class="{{ $labelClass }}" for="attachment">ไฟล์ PDF</label>

                        @if ($editing && $ebook->attachment_path)
                            <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-start gap-2">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 2h9l5 5v15H6z"></path>
                                            <path d="M14 2v6h6"></path>
                                        </svg>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-medium text-slate-700">{{ $ebook->attachment_original_name ?: 'ebook.pdf' }}</p>
                                        <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-xs">
                                            <a href="{{ route('knowledge-items.attachment.inline', $ebook) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-blue-600 hover:text-blue-700">เปิดอ่าน</a>
                                            <a href="{{ route('knowledge-items.attachment', $ebook) }}" class="font-medium text-emerald-600 hover:text-emerald-700">ดาวน์โหลด</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <input id="attachment" type="file" name="attachment" accept="application/pdf,.pdf" class="{{ $fileClass }}">
                        @if ($editing && $ebook->attachment_path)
                            <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                                <input type="checkbox" name="remove_attachment" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                ลบไฟล์ PDF เดิม
                            </label>
                        @endif
                        @error('attachment')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <label class="{{ $labelClass }}">External URL</label>
                        <input type="url" name="external_url" value="{{ old('external_url', $ebook->external_url ?? '') }}" placeholder="https://example.org/book" class="{{ $inputClass }}">
                        <span class="mt-1 block text-[11px] leading-4 text-slate-400">ใช้สำหรับลิงก์ไปยังหนังสือหรือเอกสารภายนอก</span>
                        @error('external_url')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ route('superadmin.knowledge-page.books.index') }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50">ยกเลิก</a>
        <button
            type="submit"
            @disabled($categories->isEmpty() && ! $editing)
            class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-50"
        >
            {{ $editing ? 'บันทึกการแก้ไข' : 'สร้าง E-Book' }}
        </button>
    </div>
</div>
