<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionTemplate;
use App\Models\CompetitionTemplateFormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompetitionTemplateFormFieldController extends Controller
{
    /**
     * แสดงหน้า Form Builder
     */
    public function create(CompetitionTemplate $template)
    {
        $template->load([
            'formFields' => fn ($query) => $query
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        return view(
            'superadmin.templates.form-fields.create',
            compact('template')
        );
    }

    /**
     * บันทึกคำถามทั้งหมดของ Template
     */
    public function store(
        Request $request,
        CompetitionTemplate $template
    ) {
        $request->validate([
            'fields' => ['required', 'json'],
        ]);

        $fields = json_decode(
            $request->input('fields'),
            true
        );

        $validator = Validator::make(
            ['fields' => $fields],
            [
                'fields' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'fields.*.label' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'fields.*.type' => [
                    'required',
                    Rule::in([
                        'text',
                        'textarea',
                        'number',
                        'email',
                        'phone',
                        'date',
                        'file',
                        'select',
                        'radio',
                        'checkbox',
                    ]),
                ],

                'fields.*.placeholder' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'fields.*.help' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'fields.*.options' => [
                    'nullable',
                    'array',
                ],

                'fields.*.options.*' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'fields.*.accepted_file_types' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'fields.*.max_file_size' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],

                'fields.*.required' => [
                    'required',
                    'boolean',
                ],

                'fields.*.active' => [
                    'required',
                    'boolean',
                ],
            ],
            [
                'fields.required' =>
                    'กรุณาเพิ่มคำถามอย่างน้อย 1 ข้อ',

                'fields.min' =>
                    'กรุณาเพิ่มคำถามอย่างน้อย 1 ข้อ',

                'fields.*.label.required' =>
                    'กรุณาระบุชื่อคำถามให้ครบ',

                'fields.*.type.required' =>
                    'กรุณาเลือกประเภทคำตอบ',

                'fields.*.type.in' =>
                    'พบประเภทคำตอบที่ระบบไม่รองรับ',
            ]
        );

        /*
         * ตรวจคำถามประเภทตัวเลือก
         */
        $validator->after(function ($validator) use ($fields) {
            foreach ($fields as $index => $field) {
                $type = $field['type'] ?? null;

                $options = collect($field['options'] ?? [])
                    ->map(fn ($option) => trim((string) $option))
                    ->filter()
                    ->values()
                    ->all();

                if (
                    in_array(
                        $type,
                        ['select', 'radio', 'checkbox'],
                        true
                    )
                    && empty($options)
                ) {
                    $validator->errors()->add(
                        "fields.{$index}.options",
                        'คำถามแบบตัวเลือกต้องมีอย่างน้อย 1 ตัวเลือก'
                    );
                }
            }
        });

        $validator->validate();

        DB::transaction(function () use ($template, $fields) {
            /*
             * หน้า Form Builder ส่งคำถามมาทั้งหมด
             * จึงลบชุดเดิมแล้วสร้างใหม่ตามลำดับล่าสุด
             */
            $template->formFields()->delete();

            foreach ($fields as $index => $field) {
                $baseName = Str::snake(
                    trim($field['label'])
                );

                if ($baseName === '') {
                    $baseName = 'field';
                }

                $fieldName = $baseName
                    . '_'
                    . Str::lower(Str::random(6));

                $optionTypes = [
                    'select',
                    'radio',
                    'checkbox',
                ];

                $options = in_array(
                    $field['type'],
                    $optionTypes,
                    true
                )
                    ? collect($field['options'] ?? [])
                        ->map(fn ($option) =>
                            trim((string) $option)
                        )
                        ->filter()
                        ->values()
                        ->all()
                    : null;

                $isFile = $field['type'] === 'file';

                CompetitionTemplateFormField::create([
                    'template_id' => $template->id,
                    'label' => trim($field['label']),
                    'field_name' => $fieldName,
                    'field_type' => $field['type'],

                    'placeholder' =>
                        $field['placeholder'] ?? null,

                    'help_text' =>
                        $field['help'] ?? null,

                    'options' => $options,

                    'accepted_file_types' => $isFile
                        ? ($field['accepted_file_types'] ?? null)
                        : null,

                    'max_file_size' => $isFile
                        ? ($field['max_file_size'] ?? null)
                        : null,

                    'is_required' =>
                        (bool) $field['required'],

                    'is_active' =>
                        (bool) $field['active'],

                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()
            ->route(
                'superadmin.templates.index'
            )
            ->with(
                'success',
                'บันทึกแบบฟอร์ม Template สำเร็จ'
            );
    }
}