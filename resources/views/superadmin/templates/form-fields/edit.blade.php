@extends('layouts.app')

@section('title', 'แก้ไขแบบฟอร์ม Template')

@section('header')
<div>
    <h1 class="text-2xl font-bold text-slate-800">
        แก้ไขแบบฟอร์ม Template
    </h1>

    <p class="mt-1 text-sm text-slate-500">
        แม่แบบ: {{ $template->template_name }}
    </p>
</div>
@endsection

@section('content')
    <div class="mx-auto max-w-5xl">
            {{-- ขั้นตอนการสร้าง Template --}}
        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid sm:grid-cols-2">

                {{-- ขั้นตอนที่ 1 --}}
                <div class="flex items-center gap-4 px-6 py-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 font-bold text-slate-500">
                        1
                    </span>

                    <div>
                        <p class="font-semibold text-slate-600">
                            ข้อมูล Template
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            แก้ไขข้อมูลพื้นฐานแล้ว
                        </p>
                    </div>
                </div>

                {{-- ขั้นตอนที่ 2 --}}
                <div class="flex items-center gap-4 border-b border-slate-200 bg-blue-50 px-6 py-5 sm:border-b-0 sm:border-r">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 font-bold text-white">
                        2
                    </span>

                    <div>
                        <p class="font-semibold text-blue-700">
                            แก้ไขแบบฟอร์ม
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            แก้ไขช่องกรอกข้อมูลที่มีอยู่ หรือเพิ่มใหม่
                        </p>
                    </div>
                </div>
            </div>
        </div>

    <form
        id="templateForm"
        action="{{ route('superadmin.templates.form-fields.update', $template) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        {{-- ส่งคำถามทั้งหมดเป็น JSON --}}
        <input type="hidden" name="fields" id="fieldsInput">

        {{-- หัวแบบฟอร์ม --}}
        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-3 bg-violet-600"></div>

            <div class="p-6">
                <h2 class="text-xl font-bold text-slate-800">
                    {{ $template->template_name }}
                </h2>

                @if ($template->description)
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $template->description }}
                    </p>
                @endif

                <p class="mt-4 text-xs text-slate-400">
                    แก้ไขคำถามที่ต้องการให้ผู้ส่งผลงานกรอก
                </p>
            </div>
        </div>

        {{-- รายการคำถาม --}}
        <div id="fieldsContainer" class="space-y-4"></div>

        {{-- ปุ่มเพิ่มคำถาม --}}
        <button
            type="button"
            id="addFieldButton"
            class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl
                   border-2 border-dashed border-violet-300 bg-violet-50
                   px-5 py-4 font-semibold text-violet-700 transition
                   hover:border-violet-400 hover:bg-violet-100"
        >
            <span class="text-xl">+</span>
            เพิ่มคำถาม
        </button>

        {{-- Footer --}}
        <div class="mt-8 flex items-center justify-between gap-4">
            <a
                href="{{ route('superadmin.templates.index') }}"
                class="rounded-xl border border-slate-300 px-5 py-3
                       font-semibold text-slate-600 transition
                       hover:bg-slate-100"
            >
                ยกเลิก
            </a>

            <button
                type="submit"
                class="rounded-xl bg-green-600 px-6 py-3
                       font-semibold text-white transition
                       hover:bg-green-700">
                บันทึกการแก้ไข
            </button>
        </div>
    </form>
</div>

<template id="fieldTemplate">
    <div
        class="field-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        data-field-id=""
    >
        <div class="mb-4 flex items-center justify-between gap-4">
            <span class="field-number text-sm font-semibold text-slate-400"></span>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="duplicate-field rounded-lg px-3 py-2 text-sm
                           text-slate-500 hover:bg-slate-100"
                >
                    ทำสำเนา
                </button>

                <button
                    type="button"
                    class="delete-field rounded-lg px-3 py-2 text-sm
                           text-red-500 hover:bg-red-50"
                >
                    ลบ
                </button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-[1fr_220px]">
            {{-- ชื่อคำถาม --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    คำถาม
                </label>

                <input
                    type="text"
                    class="field-label w-full rounded-xl border border-slate-300
                           px-4 py-3 outline-none transition
                           focus:border-violet-500 focus:ring-2 focus:ring-violet-100"
                    placeholder="ระบุคำถาม"
                >
            </div>

            {{-- ประเภทคำตอบ --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    ประเภทคำตอบ
                </label>

                <select
                    class="field-type w-full rounded-xl border border-slate-300
                           px-4 py-3 outline-none focus:border-violet-500"
                >
                    <option value="text">ข้อความสั้น</option>
                    <option value="textarea">ข้อความยาว</option>
                    <option value="number">ตัวเลข</option>
                    <option value="email">อีเมล</option>
                    <option value="phone">เบอร์โทรศัพท์</option>
                    <option value="date">วันที่</option>
                    <option value="select">Dropdown</option>
                    <option value="radio">ตัวเลือกเดียว</option>
                    <option value="checkbox">หลายตัวเลือก</option>
                    <option value="file">อัปโหลดไฟล์</option>
                </select>
            </div>
        </div>

        {{-- คำอธิบาย --}}
        <div class="mt-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">
                คำอธิบายเพิ่มเติม
            </label>

            <input
                type="text"
                class="field-help w-full rounded-xl border border-slate-300
                       px-4 py-3 outline-none focus:border-violet-500"
                placeholder="ข้อความช่วยอธิบายคำถาม (ไม่บังคับ)"
            >
        </div>

        {{-- ตัวเลือก --}}
        <div class="options-section mt-4 hidden">
            <div class="mb-2 flex items-center justify-between">
                <label class="text-sm font-medium text-slate-700">
                    ตัวเลือก
                </label>

                <button
                    type="button"
                    class="add-option text-sm font-semibold text-violet-600"
                >
                    + เพิ่มตัวเลือก
                </button>
            </div>

            <div class="options-container space-y-2"></div>
        </div>

        {{-- ตั้งค่าไฟล์ --}}
        <div class="file-section mt-4 hidden">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        ประเภทไฟล์ที่อนุญาต
                    </label>

                    <input
                        type="text"
                        class="accepted-file-types w-full rounded-xl
                               border border-slate-300 px-4 py-3"
                        placeholder=".pdf,.doc,.docx,.jpg,.png"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        ขนาดสูงสุด (MB)
                    </label>

                    <input
                        type="number"
                        min="1"
                        class="max-file-size w-full rounded-xl
                               border border-slate-300 px-4 py-3"
                        placeholder="10"
                    >
                </div>
            </div>
        </div>

        {{-- Required --}}
        <div class="mt-5 flex items-center justify-end border-t border-slate-100 pt-4">
            <label class="flex cursor-pointer items-center gap-2">
                <input
                    type="checkbox"
                    class="field-required h-4 w-4 rounded border-slate-300
                           text-violet-600 focus:ring-violet-500"
                >

                <span class="text-sm font-medium text-slate-600">
                    จำเป็นต้องตอบ
                </span>
            </label>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('templateForm');
    const fieldsInput = document.getElementById('fieldsInput');
    const fieldsContainer = document.getElementById('fieldsContainer');
    const fieldTemplate = document.getElementById('fieldTemplate');
    const addFieldButton = document.getElementById('addFieldButton');

    // ค่าคำถามเดิมจากฐานข้อมูล ส่งมาจาก Controller ผ่านตัวแปร $fields
    // โครงสร้างที่คาดหวัง: [{ label, type, help, options: [], required, accepted_file_types, max_file_size }, ...]
    const existingFields = @json($fields ?? []);

    let fields = [];

    function createId() {
        return window.crypto?.randomUUID
            ? crypto.randomUUID()
            : `${Date.now()}-${Math.random()}`;
    }

    function createEmptyField() {
        return {
            id: createId(),
            label: '',
            type: 'text',
            help: '',
            options: [],
            required: false,
            active: true,
            accepted_file_types: '',
            max_file_size: null
        };
    }

    // แปลงข้อมูลเดิมจาก DB ให้เป็นรูปแบบเดียวกับที่ฟอร์มใช้งาน
    function normalizeExistingField(raw) {
        return {
            id: raw.id ? String(raw.id) : createId(),
            label: raw.label ?? '',
            type: raw.type ?? 'text',
            help: raw.help ?? '',
            options: Array.isArray(raw.options) ? raw.options : [],
            required: Boolean(raw.required),
            active: raw.active ?? true,
            accepted_file_types: raw.accepted_file_types ?? '',
            max_file_size: raw.max_file_size ?? null
        };
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value ?? '';
        return element.innerHTML;
    }

    function renderFields() {
        fieldsContainer.innerHTML = '';

        fields.forEach((field, index) => {
            const fragment = fieldTemplate.content.cloneNode(true);
            const card = fragment.querySelector('.field-card');

            card.dataset.fieldId = field.id;
            card.querySelector('.field-number').textContent = `คำถามที่ ${index + 1}`;
            card.querySelector('.field-label').value = field.label;
            card.querySelector('.field-type').value = field.type;
            card.querySelector('.field-help').value = field.help;
            card.querySelector('.field-required').checked = field.required;
            card.querySelector('.accepted-file-types').value =
                field.accepted_file_types ?? '';
            card.querySelector('.max-file-size').value =
                field.max_file_size ?? '';

            updateConditionalSections(card, field);
            bindCardEvents(card, field.id);

            fieldsContainer.appendChild(fragment);
        });
    }

    function updateConditionalSections(card, field) {
        const optionTypes = ['select', 'radio', 'checkbox'];
        const optionsSection = card.querySelector('.options-section');
        const fileSection = card.querySelector('.file-section');

        optionsSection.classList.toggle(
            'hidden',
            !optionTypes.includes(field.type)
        );

        fileSection.classList.toggle(
            'hidden',
            field.type !== 'file'
        );

        renderOptions(card, field);
    }

    function renderOptions(card, field) {
        const container = card.querySelector('.options-container');
        container.innerHTML = '';

        field.options.forEach((option, optionIndex) => {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2';

            row.innerHTML = `
                <input
                    type="text"
                    value="${escapeHtml(option)}"
                    data-option-index="${optionIndex}"
                    class="option-input w-full rounded-xl border
                           border-slate-300 px-4 py-2"
                    placeholder="ตัวเลือกที่ ${optionIndex + 1}"
                >

                <button
                    type="button"
                    data-option-index="${optionIndex}"
                    class="delete-option rounded-lg px-3 py-2
                           text-red-500 hover:bg-red-50"
                >
                    ลบ
                </button>
            `;

            container.appendChild(row);
        });
    }

    function bindCardEvents(card, fieldId) {
        const getField = () => fields.find(field => field.id === fieldId);

        card.querySelector('.field-label').addEventListener('input', event => {
            getField().label = event.target.value;
        });

        card.querySelector('.field-help').addEventListener('input', event => {
            getField().help = event.target.value;
        });

        card.querySelector('.field-required').addEventListener('change', event => {
            getField().required = event.target.checked;
        });

        card.querySelector('.field-type').addEventListener('change', event => {
            const field = getField();
            field.type = event.target.value;

            if (!['select', 'radio', 'checkbox'].includes(field.type)) {
                field.options = [];
            } else if (field.options.length === 0) {
                field.options = [''];
            }

            renderFields();
        });

        card.querySelector('.accepted-file-types').addEventListener('input', event => {
            getField().accepted_file_types = event.target.value;
        });

        card.querySelector('.max-file-size').addEventListener('input', event => {
            getField().max_file_size = event.target.value
                ? Number(event.target.value)
                : null;
        });

        card.querySelector('.add-option').addEventListener('click', () => {
            getField().options.push('');
            renderFields();
        });

        card.querySelector('.options-container').addEventListener('input', event => {
            if (!event.target.classList.contains('option-input')) {
                return;
            }

            getField().options[Number(event.target.dataset.optionIndex)] =
                event.target.value;
        });

        card.querySelector('.options-container').addEventListener('click', event => {
            const deleteButton = event.target.closest('.delete-option');

            if (!deleteButton) {
                return;
            }

            getField().options.splice(
                Number(deleteButton.dataset.optionIndex),
                1
            );

            renderFields();
        });

        card.querySelector('.duplicate-field').addEventListener('click', () => {
            const index = fields.findIndex(field => field.id === fieldId);

            fields.splice(index + 1, 0, {
                ...structuredClone(getField()),
                id: createId()
            });

            renderFields();
        });

        card.querySelector('.delete-field').addEventListener('click', () => {
            fields = fields.filter(field => field.id !== fieldId);

            if (fields.length === 0) {
                fields.push(createEmptyField());
            }

            renderFields();
        });
    }

    addFieldButton.addEventListener('click', function () {
        fields.push(createEmptyField());
        renderFields();
    });

    form.addEventListener('submit', function (event) {
        const invalidField = fields.find(field => !field.label.trim());

        if (invalidField) {
            event.preventDefault();
            alert('กรุณาระบุคำถามให้ครบทุกข้อ');
            return;
        }

        fieldsInput.value = JSON.stringify(
            fields.map((field, index) => ({
                label: field.label.trim(),
                type: field.type,
                placeholder: '',
                help: field.help.trim(),
                options: field.options
                    .map(option => option.trim())
                    .filter(Boolean),
                required: field.required,
                active: field.active,
                accepted_file_types: field.accepted_file_types,
                max_file_size: field.max_file_size,
                sort_order: index + 1
            }))
        );
    });

    // โหลดค่าคำถามเดิมจากฐานข้อมูล ถ้ามี — ถ้าไม่มีให้เริ่มด้วยคำถามว่างหนึ่งข้อ
    if (Array.isArray(existingFields) && existingFields.length > 0) {
        fields = existingFields.map(normalizeExistingField);
    } else {
        fields.push(createEmptyField());
    }

    renderFields();
});
</script>
@endsection