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

        Validator::make (
            
            ['fields' => $fields],

                [
                    'fields' => ['required', 'array', 'min:1'],
                    'fields.*.label' => ['required','string','max:255',],
                    'fields.*.type' => ['required','in:text,textarea,number,email,phone,date,file,select,radio,checkbox',],
                    'fields.*.placeholder' => ['nullable','string','max:255',],
                    'fields.*.help' => ['nullable','string',],
                    'fields.*.options' => ['nullable','array',],
                    'fields.*.options.*' => ['nullable','string','max:255',],
                    'fields.*.required' => ['required','boolean',],
                    'fields.*.active' => ['required','boolean',],
                ]
        )->validate();

        DB::transaction(function () use ($template, $fields) {
            foreach ($fields as $index => $field) {
                $fieldName = Str::snake($field['label']);

                // ป้องกัน field_name ว่างหรือชื่อซ้ำ
                if ($fieldName === '') {
                    $fieldName = 'field_' . ($index + 1);
                }

                $fieldName .= '_' . ($index + 1);

                CompetitionTemplateFormField::create([
                    'template_id' => $template->id,
                    'label' => $field['label'],
                    'field_name' => $fieldName,
                    'field_type' => $field['type'],
                    'placeholder' => $field['placeholder'] ?: null,
                    'help_text' => $field['help'] ?: null,

                    // ใช้กรณี options ในฐานข้อมูลเป็น TEXT หรือ JSON
                    'options' => !empty($field['options'])
                        ? json_encode(
                            $field['options'],
                            JSON_UNESCAPED_UNICODE
                        )
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
