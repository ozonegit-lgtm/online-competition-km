<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่งผลงาน - {{ $competition->title }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    @php
        $coverImage = $competition->cover_image ?: $competition->template?->cover_image;
        $coverUrl = null;

        if ($coverImage) {
            $coverUrl = \Illuminate\Support\Str::startsWith($coverImage, ['http://', 'https://'])
                ? $coverImage
                : \Illuminate\Support\Facades\Storage::disk('public')->url($coverImage);
        }
        $typeLabels = [
            'text' => 'ข้อความสั้น',
            'textarea' => 'ข้อความหลายบรรทัด',
            'number' => 'ตัวเลข',
            'email' => 'อีเมล',
            'phone' => 'เบอร์โทรศัพท์',
            'date' => 'วันที่',
            'file' => 'แนบไฟล์',
            'select' => 'รายการแบบเลือก',
            'radio' => 'ตัวเลือกเดียว',
            'checkbox' => 'หลายตัวเลือก',
        ];
        $inputClass = 'mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-100';
    @endphp

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex w-full max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
            <div>
                <p class="font-bold text-slate-900">Online Competition &amp; KM</p>
                <p class="mt-0.5 text-xs text-slate-500">ระบบส่งผลงานเข้าประกวดออนไลน์</p>
            </div>

            <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                เปิดรับผลงาน
            </span>
        </div>
    </header>

    <main class="mx-auto w-full max-w-5xl space-y-6 px-4 py-6 sm:px-6 sm:py-10">
        {{-- ข้อมูลการแข่งขัน --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            {{-- ล็อกความสูงภาพปกให้เท่ากันทุกการแข่งขัน และครอบภาพให้อยู่ในแนวนอน --}}
            <div class="relative h-40 w-full overflow-hidden bg-slate-200 sm:h-44 lg:h-48">
                @if ($coverUrl)
                    <img src="{{ $coverUrl }}" alt="ภาพปก {{ $competition->title }}"
                        class="h-full w-full object-cover object-center">
                @else
                    <div class="flex h-full items-center justify-center text-sm font-medium text-slate-500">
                        ยังไม่มีภาพปกการแข่งขัน
                    </div>
                @endif
            </div>

            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($competition->category)
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ $competition->category->category_name }}
                        </span>
                    @endif

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ $competition->competition_type === 'team' ? 'ประเภททีม' : 'ประเภทบุคคล' }}
                    </span>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ $competition->visibility === 'private' ? 'ใช้รหัสเข้าร่วม' : 'สาธารณะ' }}
                    </span>
                </div>

                <h1 class="mt-4 text-2xl font-bold text-slate-900 sm:text-3xl">
                    {{ $competition->title }}
                </h1>

                @if ($competition->description)
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-600">
                        {{ $competition->description }}
                    </p>
                @endif

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium text-slate-500">เปิดรับผลงาน</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $competition->registration_start->format('d/m/Y H:i น.') }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium text-slate-500">ปิดรับผลงาน</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $competition->registration_end->format('d/m/Y H:i น.') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- แบบฟอร์ม --}}
        <form action="{{ route('competitions.submissions.store', $competition) }}" method="POST"
            enctype="multipart/form-data" class="space-y-6">
            @csrf

            @if ($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                    <p class="font-semibold text-red-800">กรุณาตรวจสอบข้อมูลอีกครั้ง</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($competition->visibility === 'private')
                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                    <label for="access_code" class="block text-sm font-semibold text-slate-800">
                        รหัสเข้าร่วมการแข่งขัน <span class="text-red-500">*</span>
                    </label>
                    <input id="access_code" type="text" name="access_code" value="{{ old('access_code') }}"
                        required maxlength="100" autocomplete="off" placeholder="กรอกรหัสที่ได้รับจากผู้จัดการแข่งขัน"
                        class="{{ $inputClass }}">
                    @error('access_code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </section>
            @endif

            @if ($competition->competition_type === 'team')
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-lg font-bold text-slate-900">ข้อมูลทีม</h2>

                    <div class="mt-5">
                        <label for="team_name" class="block text-sm font-semibold text-slate-700">
                            ชื่อทีม <span class="text-red-500">*</span>
                        </label>
                        <input id="team_name" type="text" name="team_name" value="{{ old('team_name') }}"
                            required maxlength="255" placeholder="กรอกชื่อทีม"
                            class="{{ $inputClass }}">
                        @error('team_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </section>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">กรอกข้อมูลผลงาน</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            กรุณากรอกข้อมูลให้ครบถ้วนก่อนส่งผลงาน
                        </p>
                    </div>

                    <span class="w-fit rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        {{ $fields->count() }} ช่อง
                    </span>
                </div>

                <div class="mt-7 space-y-6">
                    @foreach ($fields as $field)
                        @php
                            $fieldKey = "fields.{$field->id}";
                            $fieldName = "fields[{$field->id}]";
                            $options = $field->resolved_options ?? [];
                            $oldValue = old($fieldKey);
                        @endphp

                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <label for="field_{{ $field->id }}" class="block text-sm font-semibold text-slate-800">
                                    {{ $loop->iteration }}. {{ $field->label }}
                                    @if ($field->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ $typeLabels[$field->field_type] ?? $field->field_type }}
                                </span>
                            </div>

                            @switch($field->field_type)
                                @case('textarea')
                                    <textarea id="field_{{ $field->id }}" name="{{ $fieldName }}" rows="4"
                                        @required($field->is_required) placeholder="{{ $field->placeholder }}"
                                        class="{{ $inputClass }}">{{ $oldValue }}</textarea>
                                @break

                                @case('select')
                                    <select id="field_{{ $field->id }}" name="{{ $fieldName }}"
                                        @required($field->is_required) class="{{ $inputClass }}">
                                        <option value="">{{ $field->placeholder ?: '-- กรุณาเลือก --' }}</option>
                                        @foreach ($options as $option)
                                            <option value="{{ $option }}" @selected($oldValue === $option)>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                @break

                                @case('radio')
                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                        @foreach ($options as $option)
                                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 hover:border-blue-300">
                                                <input type="radio" name="{{ $fieldName }}" value="{{ $option }}"
                                                    @checked($oldValue === $option) @required($field->is_required)
                                                    class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500">
                                                <span>{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @break

                                @case('checkbox')
                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                        @foreach ($options as $option)
                                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 hover:border-blue-300">
                                                <input type="checkbox" name="{{ $fieldName }}[]" value="{{ $option }}"
                                                    @checked(in_array($option, (array) $oldValue, true))
                                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                <span>{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @break

                                @case('file')
                                <div class="mt-3 rounded-xl border border-dashed border-slate-300 bg-white p-4">
                                    <input
                                        id="field_{{ $field->id }}"
                                        type="file"
                                        name="{{ $fieldName }}"
                                        @required($field->is_required)
                                        accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.ppt,.pptx,.zip"
                                        class="js-file-input sr-only"
                                        data-file-name-target="file_name_{{ $field->id }}"
                                        data-file-preview-target="file_preview_{{ $field->id }}"
                                    >

                                    <div class="flex flex-col gap-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex min-w-0 items-center gap-3">
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                                    <svg
                                                        class="h-5 w-5"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        aria-hidden="true"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 16.5V9.75m0 0 2.625 2.625M12 9.75l-2.625 2.625M8.25 6.75h7.5A2.25 2.25 0 0 1 18 9v9a2.25 2.25 0 0 1-2.25 2.25h-7.5A2.25 2.25 0 0 1 6 18V9a2.25 2.25 0 0 1 2.25-2.25Z"
                                                        />
                                                    </svg>
                                                </span>

                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-slate-800">
                                                        แนบไฟล์ผลงาน
                                                    </p>

                                                    <p
                                                        id="file_name_{{ $field->id }}"
                                                        class="mt-0.5 truncate text-xs text-slate-500"
                                                    >
                                                        ยังไม่ได้เลือกไฟล์
                                                    </p>
                                                </div>
                                            </div>

                                            <label
                                                for="field_{{ $field->id }}"
                                                class="inline-flex min-h-[44px] w-full shrink-0 cursor-pointer items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 sm:w-auto"
                                            >
                                                เลือกไฟล์
                                            </label>
                                        </div>

                                        {{-- Image Preview --}}
                                        <div
                                            id="file_preview_{{ $field->id }}"
                                            class="hidden overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
                                        >
                                            <img
                                                src=""
                                                alt="ตัวอย่างรูปภาพ"
                                                class="max-h-80 w-full object-contain"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <p class="mt-2 text-xs leading-5 text-slate-500">
                                    รองรับ JPG, PNG, WEBP, PDF, Word, PowerPoint และ ZIP ขนาดไม่เกิน 10 MB
                                </p>
                            @break

                                @default
                                    @php
                                        $inputType = match ($field->field_type) {
                                            'number', 'email', 'date' => $field->field_type,
                                            'phone' => 'tel',
                                            default => 'text',
                                        };
                                    @endphp

                                    <input id="field_{{ $field->id }}" type="{{ $inputType }}"
                                        name="{{ $fieldName }}" value="{{ $oldValue }}"
                                        @required($field->is_required) placeholder="{{ $field->placeholder }}"
                                        class="{{ $inputClass }}">
                            @endswitch

                            @if ($field->help_text && $field->field_type !== 'file')
                                <p class="mt-2 text-xs leading-5 text-slate-500">{{ $field->help_text }}</p>
                            @endif

                            @error($fieldKey)
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" name="terms" value="1" required @checked(old('terms'))
                            class="mt-0.5 h-5 w-5 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                        <span class="min-w-0">
                            <span class="block text-sm font-semibold leading-6 text-slate-800">
                                ยืนยันความถูกต้องก่อนส่งผลงาน
                            </span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">
                                ข้าพเจ้ายืนยันว่าข้อมูลและผลงานที่กรอกเป็นข้อมูลที่ถูกต้อง
                            </span>
                        </span>
                    </label>

                    @error('terms')
                        <p class="mt-3 pl-8 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-2 text-xs leading-5 text-slate-500">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.25 11.25 12 10.5m0 0 .75.75M12 10.5v4.125m9-2.625a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p>
                            หลังส่งแล้วระบบจะสร้างรหัสผลงาน<br class="hidden sm:block">
                            สำหรับใช้อ้างอิงและติดตามผล
                        </p>
                    </div>

                    <button type="submit" style="min-height: 48px;"
                        class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 sm:w-auto sm:min-w-64">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m6 12 3 3 6-6m6 3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>ยืนยันและส่งผลงาน</span>
                    </button>
                </div>
            </section>
        </form>
    </main>

    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
        Online Competition &amp; Knowledge Management Platform
    </footer>

    <script>
        document.querySelectorAll('.js-file-input').forEach((input) => {
            input.addEventListener('change', function () {
                const file = this.files[0];
                const fileNameTarget = document.getElementById(
                    this.dataset.fileNameTarget
                );
                const previewTarget = document.getElementById(
                    this.dataset.filePreviewTarget
                );
                if (!file) {
                    fileNameTarget.textContent = 'ยังไม่ได้เลือกไฟล์';
                    previewTarget.classList.add('hidden');
                    const image = previewTarget.querySelector('img');
                    image.src = '';

                    return;
                }
                // แสดงชื่อไฟล์
                fileNameTarget.textContent = file.name;

                // ถ้าเป็นรูปภาพ → แสดง Preview
                if (file.type.startsWith('image/')) {
                    const image = previewTarget.querySelector('img');
                    image.src = URL.createObjectURL(file);
                    previewTarget.classList.remove('hidden');
                    image.onload = () => {
                        URL.revokeObjectURL(image.src);
                    };
                } else {
                    // ถ้าไม่ใช่รูป → ซ่อน Preview
                    previewTarget.classList.add('hidden');
                    const image = previewTarget.querySelector('img');
                    image.src = '';
                }
            });
        });
    </script>
</body>

</html>
