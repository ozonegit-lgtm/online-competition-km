<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionTemplate;
use App\Models\CompetitionTemplateFormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompetitionTemplateFormFieldController extends Controller
{
    public function index(CompetitionTemplate $template){
        $template->load('formFields');
        return view('superadmin.templates.form-fields.create', compact('template'));
    }

    public function store(Request $request, CompetitionTemplate $template)
    {
        $request->validate([
            'fields' => ['required', 'json'],
        ]);

        $fields = json_decode($request->fields, true);

        /**
         * ฟิลด์ระบบที่จำเป็นต้องมีอยู่ในทุก Template
         * เพื่อให้ Competition ที่สร้างจาก Template นี้
         * เปิดฟอร์มส่งผลงานได้จริง (ดู SubmissionController::ensureRequiredSystemFieldsExist)
         */
        $requiredSystemFields = [
            'contact_name',
            'contact_email',
            'contact_phone',
        ];

        Validator::make(

            ['fields' => $fields],

                [
                    'fields' => ['required', 'array', 'min:1'],
                    'fields.*.label' => ['required','string','max:255',],
                    'fields.*.type' => ['required','in:text,textarea,number,email,phone,date,file,select,radio,checkbox',],
                    'fields.*.system_field' => [
                        'nullable',
                        'string',
                        'in:contact_name,contact_email,contact_phone,project_title,project_description,project_file',
                    ],
                    'fields.*.placeholder' => ['nullable','string','max:255',],
                    'fields.*.help' => ['nullable','string',],
                    'fields.*.options' => ['nullable','array',],
                    'fields.*.options.*' => ['nullable','string','max:255',],
                    'fields.*.required' => ['required','boolean',],
                    'fields.*.active' => ['required','boolean',],
                ]
        )->after(function ($validator) use ($fields, $requiredSystemFields, $template) {

            /**
             * รวม system_field ของฟิลด์ที่มีอยู่แล้วในฐานข้อมูล (จากการ Submit
             * รอบก่อนหน้า) เข้ากับฟิลด์ชุดใหม่ที่กำลังจะบันทึก เพราะแอดมิน
             * สามารถทยอยเพิ่มฟิลด์เป็นหลายรอบได้ ไม่ใช่ต้องส่งครบในครั้งเดียว
             */
            $existingSystemFields = $template->formFields()
                ->whereNotNull('system_field')
                ->pluck('system_field');

            $systemFieldsUsed = collect($fields)
                ->pluck('system_field')
                ->filter(fn ($value) => filled($value))
                ->merge($existingSystemFields)
                ->values();

            /**
             * ต้องมีฟิลด์ระบบที่จำเป็นครบทั้ง 3 ตัว
             * มิฉะนั้น Competition ที่ใช้ Template นี้
             * จะเปิดฟอร์มส่งผลงานไม่ได้ (Error 422)
             */
            $missingFields = collect($requiredSystemFields)->diff($systemFieldsUsed);

            if ($missingFields->isNotEmpty()) {
                $validator->errors()->add(
                    'fields',
                    'กรุณากำหนดฟิลด์ระบบให้ครบ: '
                        . $missingFields->implode(', ')
                        . ' มิฉะนั้นผู้เข้าร่วมจะไม่สามารถเปิดฟอร์มส่งผลงานได้'
                );
            }

            /**
             * ห้ามกำหนด system_field ซ้ำกันมากกว่า 1 ช่อง
             * เพราะระบบจะไม่รู้ว่าควรใช้คำตอบจากช่องใด
             */
            $duplicatedFields = $systemFieldsUsed
                ->duplicates()
                ->unique()
                ->values();

            if ($duplicatedFields->isNotEmpty()) {
                $validator->errors()->add(
                    'fields',
                    'พบฟิลด์ระบบที่ถูกกำหนดซ้ำกันมากกว่า 1 ช่อง: '
                        . $duplicatedFields->implode(', ')
                );
            }
        })->validate();

        DB::transaction(function () use ($template, $fields) {
            foreach ($fields as $index => $field) {
                $fieldName = Str::snake($field['label']);

                if ($fieldName === '') {
                    $fieldName = 'field_' . ($index + 1);
                }

                $fieldName .= '_' . Str::lower(Str::random(6));

                $fieldName .= '_' . ($index + 1);

                CompetitionTemplateFormField::create([
                    'template_id' => $template->id,
                    'label' => $field['label'],
                    'field_name' => $fieldName,
                    'system_field' => $field['system_field'] ?? null,
                    'field_type' => $field['type'],
                    'placeholder' => $field['placeholder'] ?? null,
                    'help_text' => $field['help'] ?? null,

                    // ส่ง array ตรง ๆ ให้ Model cast 'options' => 'array' เป็นผู้ json_encode
                    // ให้เอง (ห้าม json_encode ซ้ำตรงนี้ มิฉะนั้นค่าที่บันทึกจะถูก
                    // encode ซ้อนกัน 2 ชั้น แล้วตอนอ่านกลับมาจะได้ string แทน array)
                    'options' => !empty($field['options'])
                        ? array_values($field['options'])
                        : null,

                    'is_required' => (bool) $field['required'],
                    'is_active' => (bool) $field['active'],
                ]);
            }
        });

        return redirect()
            ->route(
                'superadmin.templates.form-fields.create',
                ['template' => $template->id]
            )
            ->with('success', 'เพิ่มช่องกรอกข้อมูลสำเร็จ');
    }
}