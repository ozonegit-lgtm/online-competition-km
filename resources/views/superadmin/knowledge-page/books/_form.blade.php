@php
    $editing = isset($ebook);
    $inputClass = 'mt-1.5 h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
    $textareaClass = 'mt-1.5 w-full resize-y rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100';
@endphp

<div class="space-y-5">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">ข้อมูล E-Book</h2>
        <div class="mt-4 grid gap-5 md:grid-cols-2">
            <label class="md:col-span-2"><span class="font-semibold text-slate-700">ชื่อ E-Book *</span><input name="title" value="{{ old('title', $ebook->title ?? '') }}" required class="{{ $inputClass }}">@error('title')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label><span class="font-semibold text-slate-700">หมวดหมู่ *</span><select name="knowledge_category_id" required class="{{ $inputClass }}"><option value="">เลือกหมวดหมู่</option>@if ($editing && $ebook->knowledgeCategory && ! $categories->contains('id', $ebook->knowledge_category_id))<option value="{{ $ebook->knowledge_category_id }}" selected>{{ $ebook->knowledgeCategory->name }} (ปิดใช้งาน)</option>@endif @foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('knowledge_category_id', $ebook->knowledge_category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>@error('knowledge_category_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label><span class="font-semibold text-slate-700">ปีเผยแพร่</span><input type="number" min="1" max="65535" name="publication_year" value="{{ old('publication_year', $ebook->publication_year ?? '') }}" class="{{ $inputClass }}">@error('publication_year')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label><span class="font-semibold text-slate-700">เล่ม (Volume)</span><input name="volume" maxlength="50" value="{{ old('volume', $ebook->volume ?? '') }}" class="{{ $inputClass }}"></label>
            <label><span class="font-semibold text-slate-700">ฉบับ (Issue)</span><input name="issue" maxlength="50" value="{{ old('issue', $ebook->issue ?? '') }}" class="{{ $inputClass }}"></label>
            <label><span class="font-semibold text-slate-700">ลำดับแสดงผล</span><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $ebook->sort_order ?? 0) }}" class="{{ $inputClass }}"></label>
            <label class="md:col-span-2"><span class="font-semibold text-slate-700">รายละเอียดสั้น</span><textarea name="summary" rows="4" class="{{ $textareaClass }}">{{ old('summary', $ebook->summary ?? '') }}</textarea>@error('summary')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <label class="md:col-span-2"><span class="font-semibold text-slate-700">เนื้อหา</span><textarea name="content" rows="10" class="{{ $textareaClass }}">{{ old('content', $ebook->content ?? '') }}</textarea>@error('content')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">ปกและช่องทางอ่าน</h2>
        <p class="mt-1 text-sm text-slate-500">ต้องมีไฟล์ PDF หรือ External URL อย่างน้อยหนึ่งรายการ และสามารถมีทั้งสองอย่างได้</p>
        <div class="mt-5 grid gap-6 md:grid-cols-2">
            <div>
                <label class="font-semibold text-slate-700" for="cover_image">รูปปก</label>
                @if ($editing && $ebook->cover_image_url)<img src="{{ $ebook->cover_image_url }}" alt="ปกปัจจุบัน" class="mt-2 h-44 max-w-full rounded-xl border border-slate-200 object-contain">@endif
                <input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-2 text-sm">
                @if ($editing && $ebook->cover_image)<label class="mt-2 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_cover_image" value="1" class="rounded border-slate-300 text-red-600"> ลบรูปปกเดิม</label>@endif
                @error('cover_image')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </div>
            <div>
                <label class="font-semibold text-slate-700" for="attachment">ไฟล์ PDF</label>
                @if ($editing && $ebook->attachment_path)
                    <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm"><p class="truncate font-semibold text-slate-700">{{ $ebook->attachment_original_name ?: 'ebook.pdf' }}</p><div class="mt-2 flex gap-2"><a href="{{ route('knowledge-items.attachment.inline', $ebook) }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:underline">อ่านไฟล์เดิม</a><a href="{{ route('knowledge-items.attachment', $ebook) }}" class="text-emerald-700 hover:underline">ดาวน์โหลด</a></div></div>
                @endif
                <input id="attachment" type="file" name="attachment" accept="application/pdf,.pdf" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-2 text-sm">
                @if ($editing && $ebook->attachment_path)<label class="mt-2 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_attachment" value="1" class="rounded border-slate-300 text-red-600"> ลบไฟล์ PDF เดิม</label>@endif
                @error('attachment')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </div>
            <label class="md:col-span-2"><span class="font-semibold text-slate-700">External URL</span><input type="url" name="external_url" value="{{ old('external_url', $ebook->external_url ?? '') }}" placeholder="https://example.org/book" class="{{ $inputClass }}">@error('external_url')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('superadmin.knowledge-page.books.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 hover:bg-slate-50">ยกเลิก</a>
        <button type="submit" @disabled($categories->isEmpty() && ! $editing) class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-6 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">{{ $editing ? 'บันทึกการแก้ไข' : 'สร้าง E-Book' }}</button>
    </div>
</div>
