document.addEventListener('DOMContentLoaded', function () {
    let fields = [];

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
            !previewContainer ||
            !emptyPreview
        ) {
            return;
        }
        
    const optionTypes = ['select', 'radio', 'checkbox'];

    typeSelect.addEventListener('change', toggleOptionsBox);
    addButton.addEventListener('click', addField);


    templateForm.addEventListener('submit', function (event) {
        if (fields.length === 0) {
            event.preventDefault();
            alert('กรุณาเพิ่มช่องกรอกข้อมูลอย่างน้อย 1 ช่อง');
            return;
        }

        fieldsInput.value = JSON.stringify(fields);
    });

    toggleOptionsBox();

    function toggleOptionsBox() {
        const showOptions = optionTypes.includes(typeSelect.value);
        optionsBox.classList.toggle('hidden', !showOptions);

        if (!showOptions) {
            document.getElementById('field_options').value = '';
        }
    }

    function addField() {
        const label = document.getElementById('field_label').value.trim();
        const type = typeSelect.value;
        const placeholder = document.getElementById('field_placeholder').value.trim();
        const help = document.getElementById('field_help').value.trim();
        const optionsText = document.getElementById('field_options').value.trim();
        const required = document.getElementById('field_required').checked;
        const active = document.getElementById('field_active').checked;
        const options = parseOptions(optionsText);

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

        fields.push({
            id: Date.now(),
            label,
            type,
            placeholder,
            help,
            options,
            required,
            active
        });

        renderPreview();
        clearForm();
    }

    function clearForm() {
        document.getElementById('field_label').value = '';
        document.getElementById('field_placeholder').value = '';
        document.getElementById('field_help').value = '';
        document.getElementById('field_options').value = '';
        document.getElementById('field_required').checked = false;
        document.getElementById('field_active').checked = true;
        typeSelect.value = 'text';

        toggleOptionsBox();
        document.getElementById('field_label').focus();
    }

    function renderPreview() {
        previewContainer.innerHTML = '';

        if (fields.length === 0) {
            previewContainer.appendChild(emptyPreview);
            emptyPreview.classList.remove('hidden');
            return;
        }

        fields.forEach(function (field, index) {
            const card = document.createElement('div');
            card.className = 'rounded-xl border border-slate-200 bg-white p-5 shadow-sm';

            if (!field.active) {
                card.classList.add('opacity-60');
            }

            const header = document.createElement('div');
            header.className = 'mb-3 flex items-start justify-between gap-4';

            const titleBox = document.createElement('div');

            const title = document.createElement('label');
            title.className = 'block text-sm font-semibold text-slate-700';
            title.textContent = `${index + 1}. ${field.label}`;

            if (field.required) {
                const requiredMark = document.createElement('span');
                requiredMark.className = 'ml-1 text-red-500';
                requiredMark.textContent = '*';
                title.appendChild(requiredMark);
            }

            const status = document.createElement('p');
            status.className = 'mt-1 text-xs text-slate-400';
            status.textContent = getTypeLabel(field.type);

            if (!field.active) {
                status.textContent += ' • ปิดใช้งาน';
            }

            titleBox.append(title, status);

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100';
            deleteButton.textContent = 'ลบ';
            deleteButton.addEventListener('click', function () {
                removeField(field.id);
            });

            header.append(titleBox, deleteButton);
            card.appendChild(header);
            card.appendChild(createPreviewInput(field));

            if (field.help !== '') {
                const helpText = document.createElement('p');
                helpText.className = 'mt-2 text-xs text-slate-500';
                helpText.textContent = field.help;
                card.appendChild(helpText);
            }

            previewContainer.appendChild(card);
        });
    }

    function createPreviewInput(field) {
        let element;

        if (field.type === 'textarea') {
            element = document.createElement('textarea');
            element.rows = 3;
            element.placeholder = field.placeholder;
            element.className = inputClasses();
        } else if (field.type === 'select') {
            element = document.createElement('select');
            element.className = inputClasses();

            const firstOption = document.createElement('option');
            firstOption.value = '';
            firstOption.textContent = field.placeholder || 'กรุณาเลือก';
            element.appendChild(firstOption);

            field.options.forEach(function (optionText) {
                const option = document.createElement('option');
                option.value = optionText;
                option.textContent = optionText;
                element.appendChild(option);
            });
        } else if (field.type === 'radio' || field.type === 'checkbox') {
            element = document.createElement('div');
            element.className = 'space-y-2';

            field.options.forEach(function (optionText, optionIndex) {
                const choiceLabel = document.createElement('label');
                choiceLabel.className = 'flex items-center gap-2 text-sm text-slate-700';

                const input = document.createElement('input');
                input.type = field.type;
                input.disabled = !field.active;
                input.className = 'h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500';
                input.name = field.type === 'radio'
                    ? `preview_${field.id}`
                    : `preview_${field.id}_${optionIndex}`;

                const text = document.createElement('span');
                text.textContent = optionText;

                choiceLabel.append(input, text);
                element.appendChild(choiceLabel);
            });
        } else {
            element = document.createElement('input');
            element.type = getInputType(field.type);
            element.placeholder = field.placeholder;
            element.className = inputClasses();
        }

        if ('disabled' in element) {
            element.disabled = !field.active;
        }

        return element;
    }

    function removeField(id) {
        fields = fields.filter(function (field) {
            return field.id !== id;
        });

        renderPreview();
    }

    function parseOptions(value) {
        return value
            .split(/\r?\n|,/)
            .map(function (option) {
                return option.trim();
            })
            .filter(Boolean);
    }

    function getInputType(type) {
        if (type === 'phone') {
            return 'tel';
        }

        const supportedTypes = ['text', 'number', 'email', 'date', 'file'];
        return supportedTypes.includes(type) ? type : 'text';
    }

    function getTypeLabel(type) {
        const labels = {
            text: 'ข้อความสั้น',
            textarea: 'ข้อความยาว',
            number: 'ตัวเลข',
            email: 'อีเมล',
            phone: 'เบอร์โทรศัพท์',
            date: 'วันที่',
            file: 'อัปโหลดไฟล์',
            select: 'Dropdown',
            radio: 'Radio',
            checkbox: 'Checkbox'
        };

        return labels[type] || type;
    }

    function inputClasses() {
        return 'w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100';
    }
});