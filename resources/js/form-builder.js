    function initFormBuilder() {
    let fields = [];

    const fileSettingsBox = document.getElementById('fileSettingsBox');
    const fileTypesSelect = document.getElementById('field_file_types');
    const maxFileSizeSelect = document.getElementById('field_max_file_size');
    const allowMultipleInput = document.getElementById('field_allow_multiple');
    const addButton = document.getElementById('addField');
    const typeSelect = document.getElementById('field_type');
    const optionsBox = document.getElementById('optionsBox');
    const previewContainer = document.getElementById('previewContainer');
    const emptyPreview = document.getElementById('emptyPreview');
    const templateForm = document.getElementById('templateForm');
    const fieldsInput = document.getElementById('fieldsInput');

        if (
            !templateForm ||
            !fieldsInput ||
            !addButton ||
            !typeSelect ||
            !optionsBox ||
            !fileSettingsBox ||
            !fileTypesSelect ||
            !maxFileSizeSelect ||
            !allowMultipleInput ||
            !previewContainer ||
            !emptyPreview
        ) {
            return;
        }

    const optionTypes = ['select', 'radio', 'checkbox'];

    addButton.addEventListener('click', addField);
    typeSelect.addEventListener('change', toggleOptionsBox);
    templateForm.addEventListener('submit', submitForm);

    toggleOptionsBox();

    function submitForm(event) {
        if (fields.length === 0) {
            event.preventDefault();
            alert('กรุณาเพิ่มช่องกรอกข้อมูลอย่างน้อย 1 ช่อง');
            return;
        }

        fieldsInput.value = JSON.stringify(fields);
    }

    function toggleOptionsBox() {
        const showOptions = optionTypes.includes(typeSelect.value);
        const showFileSettings = typeSelect.value === 'file';

        optionsBox.classList.toggle('hidden', !showOptions);
        fileSettingsBox.classList.toggle('hidden', !showFileSettings);

        if (!showOptions) {
            document.getElementById('field_options').value = '';
        }

        if (!showFileSettings) {
            fileTypesSelect.value = '';
            maxFileSizeSelect.value = '10240';
            allowMultipleInput.checked = false;
        }
    }

    function addField(event) {
        event.preventDefault();

        const label = document.getElementById('field_label').value.trim();
        const type = document.getElementById('field_type').value;
        const systemField = document.getElementById('field_system').value;
        const placeholder = document.getElementById('field_placeholder').value.trim();
        const help = document.getElementById('field_help').value.trim();
        const options = parseOptions(
            document.getElementById('field_options').value.trim()
        );
        const required = document.getElementById('field_required').checked;
        const active = document.getElementById('field_active').checked;
        const acceptedFileTypes = type === 'file'
            ? fileTypesSelect.value
            : '';
        const maxFileSize = type === 'file'
            ? Number(maxFileSizeSelect.value)
            : null;
        const allowMultiple = type === 'file'
            ? allowMultipleInput.checked
            : false;

        if (label === '') {
            alert('กรุณากรอกชื่อช่อง');
            document.getElementById('field_label').focus();
            return;
        }

        if (optionTypes.includes(type) && options.length === 0) {
            alert('กรุณากรอกตัวเลือกอย่างน้อย 1 รายการ');
            document.getElementById('field_options').focus();
            return;
        }

        if (systemField !== '') {
            const duplicate = fields.find(field => field.system_field === systemField);
            if (duplicate) {
                alert('ฟิลด์ระบบนี้ถูกเลือกแล้ว กรุณาเลือกฟิลด์ระบบอื่น');
                document.getElementById('field_system').focus();
                return;
            }
        }

        fields.push({
            id: Date.now(),
            label,
            type,
            system_field: systemField,
            placeholder,
            help,
            options,
            required,
            active,
            accepted_file_types: acceptedFileTypes,
            max_file_size: maxFileSize,
            allow_multiple: allowMultiple
        });

        renderPreview();
        clearForm();
    }

    function clearForm() {
        document.getElementById('field_label').value = '';
        document.getElementById('field_placeholder').value = '';
        document.getElementById('field_help').value = '';
        document.getElementById('field_options').value = '';
        document.getElementById('field_type').value = 'text';
        document.getElementById('field_system').value = '';
        document.getElementById('field_required').checked = false;
        document.getElementById('field_active').checked = true;
        toggleOptionsBox();
        document.getElementById('field_label').focus();
    }

    function renderPreview() {
        previewContainer.innerHTML = '';

        if (fields.length === 0) {
            emptyPreview.style.display = '';
            previewContainer.appendChild(emptyPreview);
            syncFieldsInput();
            return;
        }

        emptyPreview.style.display = 'none';

        fields.forEach((field, index) => {
            const card = document.createElement('div');
            card.className = [
                'rounded-2xl',
                'border',
                'border-slate-200',
                'bg-white',
                'p-5',
                'shadow-sm',
                !field.active ? 'opacity-60' : ''
            ].filter(Boolean).join(' ');

            const header = document.createElement('div');
            header.className = 'mb-4 flex items-start justify-between gap-4';

            const titleArea = document.createElement('div');
            titleArea.className = 'min-w-0';

            const title = document.createElement('label');
            title.className = 'block text-sm font-semibold text-slate-700';
            title.textContent = `${index + 1}. ${field.label}`;

            if (field.required) {
                const requiredMark = document.createElement('span');
                requiredMark.className = 'ml-1 text-red-500';
                requiredMark.textContent = '*';
                title.appendChild(requiredMark);
            }

            const meta = document.createElement('p');
            meta.className = 'mt-1 text-xs text-slate-400';
            meta.textContent =
                `ชนิด: ${getTypeLabel(field.type)}`
                + (field.system_field
                    ? ` • System: ${field.system_field}`
                    : '')
                + (field.active ? '' : ' • ปิดใช้งาน');

            titleArea.append(title, meta);

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = [
                'shrink-0',
                'rounded-lg',
                'bg-red-50',
                'px-3',
                'py-2',
                'text-xs',
                'font-semibold',
                'text-red-600',
                'transition',
                'hover:bg-red-100'
            ].join(' ');
            deleteButton.textContent = 'ลบ';
            deleteButton.addEventListener('click', () => removeField(field.id));

            header.append(titleArea, deleteButton);
            card.appendChild(header);
            card.appendChild(createFieldPreview(field));

            if (field.help) {
                const helpText = document.createElement('p');
                helpText.className = 'mt-2 text-xs text-slate-500';
                helpText.textContent = field.help;
                card.appendChild(helpText);
            }

            previewContainer.appendChild(card);
        });

        syncFieldsInput();
    }

    function createFieldPreview(field) {
        const options = field.options;
        let element;

        if (field.type === 'file') {
            const wrapper = document.createElement('div');
            wrapper.className = 'space-y-2';

            const input = document.createElement('input');
            input.type = 'file';
            input.accept = field.accepted_file_types || '';
            input.multiple = Boolean(field.allow_multiple);
            input.disabled = !field.active;
            input.className = inputClasses();

            const fileInfo = document.createElement('p');
            fileInfo.className = 'text-xs text-slate-500';

            const maxSizeMb = field.max_file_size
                ? field.max_file_size / 1024
                : 10;

            fileInfo.textContent =
                `ขนาดสูงสุด ${maxSizeMb} MB` +
                (field.allow_multiple
                    ? ' • อัปโหลดได้หลายไฟล์'
                    : '');

            wrapper.append(input, fileInfo);

            return wrapper;
        } else if (field.type === 'textarea') {
            element = document.createElement('textarea');
            element.rows = 3;
            element.placeholder = field.placeholder;
            element.className = inputClasses();
        } else if (field.type === 'select') {
            element = document.createElement('select');
            element.className = inputClasses();

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = field.placeholder || 'กรุณาเลือก';
            element.appendChild(defaultOption);

            options.forEach(optionText => {
                const option = document.createElement('option');
                option.value = optionText;
                option.textContent = optionText;
                element.appendChild(option);
            });
        } else if (field.type === 'radio' || field.type === 'checkbox') {
            element = document.createElement('div');
            element.className = 'space-y-2';

            options.forEach((optionText, optionIndex) => {
                const optionLabel = document.createElement('label');
                optionLabel.className = 'flex items-center gap-2 text-sm text-slate-700';

                const input = document.createElement('input');
                input.type = field.type;
                input.name = field.type === 'radio'
                    ? `preview_field_${field.id}`
                    : `preview_field_${field.id}_${optionIndex}`;
                input.disabled = !field.active;
                input.className = 'h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500';

                const text = document.createElement('span');
                text.textContent = optionText;

                optionLabel.append(input, text);
                element.appendChild(optionLabel);
            });
        } else {
            element = document.createElement('input');
            element.type = supportedInputType(field.type);
            element.placeholder = field.placeholder;
            element.className = inputClasses();
        }

        if ('disabled' in element) {
            element.disabled = !field.active;
        }

        return element;
    }

    function removeField(id) {
        fields = fields.filter(field => field.id !== id);
        renderPreview();
        syncFieldsInput();
    }

    function parseOptions(value) {
        return value
            .split(/\r?\n|,/)
            .map(option => option.trim())
            .filter(Boolean);
    }

    function supportedInputType(type) {
        const supportedTypes = [
            'text',
            'number',
            'email',
            'tel',
            'date',
            'file',
            'url'
        ];

        if (type === 'phone') {
            return 'tel';
        }

        return supportedTypes.includes(type) ? type : 'text';
    }

    function getTypeLabel(type) {
        const labels = {
            text: 'ข้อความสั้น',
            textarea: 'ข้อความยาว',
            number: 'ตัวเลข',
            email: 'อีเมล',
            phone: 'เบอร์โทรศัพท์',
            tel: 'เบอร์โทรศัพท์',
            date: 'วันที่',
            file: 'ไฟล์',
            url: 'ลิงก์',
            select: 'รายการตัวเลือก',
            radio: 'ตัวเลือกเดียว',
            checkbox: 'หลายตัวเลือก'
        };

        return labels[type] || type;
    }

    function inputClasses() {
        return [
            'w-full',
            'rounded-xl',
            'border',
            'border-slate-300',
            'bg-slate-50',
            'px-4',
            'py-3',
            'text-sm',
            'text-slate-700',
            'outline-none',
            'transition',
            'placeholder:text-slate-400',
            'focus:border-blue-500',
            'focus:bg-white',
            'focus:ring-4',
            'focus:ring-blue-100',
            'disabled:cursor-not-allowed',
            'disabled:bg-slate-100'
        ].join(' ');
    }

    function syncFieldsInput() {
        fieldsInput.value = JSON.stringify(fields);
    }

}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFormBuilder);
} else {
    initFormBuilder();
}
