@extends('layouts.app')

@section('title', 'สร้างแบบฟอร์ม Template')

@section('header')
<div>
    <h1 class="text-2xl font-bold text-slate-800">
        สร้างแบบฟอร์ม Template
    </h1>

    <p class="mt-1 text-sm text-slate-500">
        แม่แบบ: {{ $template->template_name }}
    </p>
</div>
@endsection

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">

    {{-- แจ้งบันทึกสำเร็จ --}}
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- แจ้งข้อผิดพลาด --}}
    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4">
            <p class="font-semibold text-red-700">
                กรุณาตรวจสอบข้อมูลอีกครั้ง
            </p>

            <ul class="mt-2 list-inside list-disc text-sm text-red-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ขั้นตอนการสร้าง Template --}}
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="grid sm:grid-cols-2">

            {{-- ขั้นตอนที่ 1 สำเร็จแล้ว --}}
            <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5 sm:border-b-0 sm:border-r">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-600 font-bold text-white">
                    ✓
                </span>

                <div>
                    <p class="font-semibold text-green-700">
                        ข้อมูล Template
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        บันทึกข้อมูลเรียบร้อยแล้ว
                    </p>
                </div>
            </div>

            {{-- ขั้นตอนที่ 2 --}}
            <div class="flex items-center gap-4 bg-blue-50 px-6 py-5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 font-bold text-white">
                    2
                </span>

                <div>
                    <p class="font-semibold text-blue-700">
                        สร้างแบบฟอร์ม
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        กำลังเพิ่มช่องกรอกข้อมูล
                    </p>
                </div>
            </div>
        </div>
    </div>
<form
    id="templateForm"
    method="POST"
    action="{{ route('superadmin.templates.form-fields.store', $template) }}">

    @csrf

    <input
        type="hidden"
        name="fields"
        id="fieldsInput">
    {{-- Form Builder --}}
        <div class="grid gap-6 md:grid-cols-2">

            {{-- ซ้าย --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{-- Header --}}
                <div class="border-b border-slate-200 px-6 py-5">
                    <h2 class="text-xl font-bold text-slate-800">
                        Form Builder
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        เพิ่มช่องกรอกข้อมูลสำหรับ Template
                    </p>
                </div>
                {{-- Body --}}
                <div class="space-y-6 p-6">
                    {{-- ชื่อช่องกรอก --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            ชื่อช่องกรอก <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="field_label"
                            placeholder="เช่น ชื่อผลงาน"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3
                                focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                    {{-- ประเภทข้อมูล --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            ประเภทข้อมูล <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="field_type"
                                onchange="
                                    document
                                        .getElementById('fileSettingsBox')
                                        .classList
                                        .toggle('hidden', this.value !== 'file')
                                "
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3
                                focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            <option value="text">ข้อความสั้น (Text)</option>
                            <option value="textarea">ข้อความยาว (Textarea)</option>
                            <option value="number">ตัวเลข</option>
                            <option value="email">อีเมล</option>
                            <option value="phone">เบอร์โทรศัพท์</option>
                            <option value="date">วันที่</option>
                            <option value="file">อัปโหลดไฟล์</option>
                            <option value="select">Dropdown</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                        </select>
                    </div>
                    {{-- Placeholder --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            Placeholder
                        </label>
                        <input
                            type="text"
                            id="field_placeholder"
                            placeholder="ข้อความตัวอย่าง"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3
                                focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                    {{-- คำอธิบาย --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">
                            คำอธิบาย
                        </label>
                        <textarea
                            id="field_help"
                            rows="3"
                            placeholder="ข้อความแนะนำผู้ใช้งาน"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3
                                focus:border-blue-500 focus:ring-4 focus:ring-blue-100"></textarea>
                    </div>
                    {{-- ตัวเลือก --}}
                    <div id="optionsBox" class="hidden">
                        <label class="block text-sm font-semibold text-slate-700">
                            ตัวเลือก
                        </label>
                        <textarea
                            id="field_options"
                            rows="5"
                            placeholder="กรอก 1 ตัวเลือกต่อ 1 บรรทัด"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3
                                focus:border-blue-500 focus:ring-4 focus:ring-blue-100"></textarea>
                        <p class="mt-2 text-xs text-slate-500">
                            ใช้สำหรับ Dropdown, Radio และ Checkbox
                        </p>
                    </div>
                    {{-- ตั้งค่าไฟล์ --}}
                    <div id="fileSettingsBox" class="hidden space-y-5">

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                ประเภทไฟล์ที่อนุญาต
                            </label>

                            <select
                                id="field_file_types"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3
                                    focus:border-blue-500 focus:ring-4 focus:ring-blue-100">

                                <option value="">ไฟล์ทุกประเภท</option>
                                <option value="image/*">รูปภาพ</option>
                                <option value=".pdf">PDF</option>
                                <option value=".doc,.docx">Microsoft Word</option>
                                <option value=".xls,.xlsx">Microsoft Excel</option>
                                <option value=".ppt,.pptx">PowerPoint</option>
                                <option value="video/*">วิดีโอ</option>
                                <option value="audio/*">ไฟล์เสียง</option>
                                <option value=".zip,.rar,.7z">ZIP / RAR / 7Z</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">
                                ขนาดไฟล์สูงสุด
                            </label>

                            <select
                                id="field_max_file_size"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3
                                    focus:border-blue-500 focus:ring-4 focus:ring-blue-100">

                                <option value="2048">2 MB</option>
                                <option value="5120">5 MB</option>
                                <option value="10240" selected>10 MB</option>
                                <option value="20480">20 MB</option>
                                <option value="51200">50 MB</option>
                            </select>
                        </div>

                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                id="field_allow_multiple"
                                class="h-5 w-5 accent-blue-600">

                            <span class="text-sm text-slate-700">
                                อนุญาตให้อัปโหลดหลายไฟล์
                            </span>
                        </label>

                    </div>
                    {{-- ตั้งค่า --}}
                    <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                id="field_required"
                                class="h-5 w-5 accent-blue-600">
                            <span class="text-sm text-slate-700">
                                จำเป็นต้องกรอก
                            </span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                id="field_active"
                                checked
                                class="h-5 w-5 accent-green-600">
                            <span class="text-sm text-slate-700">
                                เปิดใช้งาน
                            </span>
                        </label>
                    </div>
                    {{-- ปุ่ม --}}
                    <button
                        type="button"
                        id="addField"
                        class="w-full rounded-xl bg-blue-600 px-6 py-3
                            font-semibold text-white transition
                            hover:bg-blue-700">
                        + เพิ่มเข้า Preview
                    </button>
                </div>
            </div>
        {{-- ขวา --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            {{-- Header --}}
            <div class="border-b border-slate-200 px-6 py-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Live Preview
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    ตัวอย่างแบบฟอร์มที่ผู้สมัครจะเห็น
                </p>
            </div>
            {{-- Preview --}}
            <div id="previewContainer" class="space-y-5 p-6">
                {{-- Empty State --}}
                <div id="emptyPreview" class="rounded-xl border-2 border-dashed border-slate-300 py-16 text-center">
                    <div class="text-5xl">
                        📝
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-700">
                        ยังไม่มีช่องกรอกข้อมูล
                    </h3>
                    <p class="mt-2 text-sm text-slate-500">
                        เพิ่มช่องกรอกจาก Form Builder ทางด้านซ้าย
                    </p>
                </div>
            </div>
            {{-- Footer --}}
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-5">
                <button
                    type="submit"
                    id="saveTemplate"
                    class="w-full rounded-xl bg-green-600 px-6 py-3
                        font-semibold text-white transition
                        hover:bg-green-700">
                     บันทึก Template ทั้งหมด
                </button>
            </div>
        </div>
    </div>
</form>

@endsection
