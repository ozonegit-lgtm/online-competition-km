@php($editing = isset($knowledgeItem))
<div class="grid gap-6 lg:grid-cols-2">
    <div class="lg:col-span-2">
        <label for="title" class="block text-sm font-semibold text-slate-700">ชื่อองค์ความรู้</label>
        <input id="title" name="title" required maxlength="255" value="{{ old('title', $knowledgeItem->title ?? '') }}" class="mt-2 w-full rounded-xl border-slate-300">
        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="lg:col-span-2">
        <label for="category_id" class="block text-sm font-semibold text-slate-700">หมวดหมู่</label>
        <select id="category_id" name="category_id" required class="mt-2 w-full rounded-xl border-slate-300">
            <option value="">เลือกหมวดหมู่</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $knowledgeItem->category_id ?? '') === (string) $category->id)>
                    {{ $category->category_name }}{{ $category->is_active ? '' : ' (ปิดใช้งาน)' }}
                </option>
            @endforeach
        </select>
        @error('category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="lg:col-span-2">
        <label for="summary" class="block text-sm font-semibold text-slate-700">บทสรุป</label>
        <textarea id="summary" name="summary" rows="4" class="mt-2 w-full rounded-xl border-slate-300">{{ old('summary', $knowledgeItem->summary ?? '') }}</textarea>
        @error('summary') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="lg:col-span-2">
        <label for="content" class="block text-sm font-semibold text-slate-700">เนื้อหา</label>
        <textarea id="content" name="content" rows="10" class="mt-2 w-full rounded-xl border-slate-300">{{ old('content', $knowledgeItem->content ?? '') }}</textarea>
        @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="cover_image" class="block text-sm font-semibold text-slate-700">รูปปก</label>
        <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-slate-300 p-2 text-sm">
        <p class="mt-1 text-xs text-slate-500">JPG, JPEG, PNG หรือ WEBP ไม่เกิน 10 MB</p>
        @error('cover_image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @if ($editing && $knowledgeItem->cover_image)
            <div class="mt-3 rounded-xl border p-3">
                <img src="{{ Storage::disk('public')->url($knowledgeItem->cover_image) }}" alt="รูปปกปัจจุบัน" class="h-32 w-full rounded-lg object-cover">
                <label class="mt-3 flex gap-2 text-sm"><input type="checkbox" name="remove_cover_image" value="1" @checked(old('remove_cover_image'))> ลบรูปปกปัจจุบัน</label>
            </div>
        @endif
    </div>
    <div>
        <label for="attachment" class="block text-sm font-semibold text-slate-700">ไฟล์แนบ</label>
        <input id="attachment" name="attachment" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.ppt,.pptx,.zip" class="mt-2 block w-full rounded-xl border border-slate-300 p-2 text-sm">
        <p class="mt-1 text-xs text-slate-500">รูปภาพ, PDF, Word, PowerPoint หรือ ZIP ไม่เกิน 10 MB</p>
        @error('attachment') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @if ($editing && $knowledgeItem->attachment_path)
            <div class="mt-3 rounded-xl border p-3 text-sm">
                <p class="break-all">ไฟล์ปัจจุบัน: {{ $knowledgeItem->attachment_original_name ?: 'ไฟล์แนบ' }}</p>
                <label class="mt-3 flex gap-2"><input type="checkbox" name="remove_attachment" value="1" @checked(old('remove_attachment'))> ลบไฟล์แนบปัจจุบัน</label>
            </div>
        @endif
    </div>
</div>
