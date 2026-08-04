<?php
    namespace App\Http\Controllers\SuperAdmin;

    use App\Http\Controllers\Controller;
    use Illuminate\Support\Str;
    use App\Models\CompetitionTemplate;
    use Illuminate\Http\Request;
    use Illuminate\Validation\Rule;
    use Illuminate\Validation\ValidationException;
    use Illuminate\Support\Facades\Storage;
    use App\Models\CompetitionTemplateFormField;
    use Illuminate\Support\Facades\DB;

    class CompetitionTemplateController extends Controller
    {
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
            $competitionTemplates = CompetitionTemplate::withCount('formFields')->latest()->paginate(9);
            return view('superadmin.templates.index', compact('competitionTemplates'));
        }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $competitionTemplates = CompetitionTemplate::paginate(10);
        return view('superadmin.templates.create',compact('competitionTemplates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'template_name' => ['required', 'string', 'max:255'],
            'template_slug' => ['nullable', 'string', 'max:255', 'unique:competition_templates,template_slug'],
            'default_description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validate['template_slug'] = Str::slug(
            $validate['template_slug'] ?: $validate['template_name']
        );

        if ($request->hasFile('cover_image')) {
            $validate['cover_image'] = $request->file('cover_image')->store('competition-templates', 'public');
        }
        
        $template = DB::transaction(function () use ($validate) {
            $template = CompetitionTemplate::create($validate);

            $fixedFields = [
                [
                    'label' => 'ชื่อ–นามสกุลผู้ติดต่อ',
                    'field_name' => 'contact_name',
                    'system_field' => 'contact_name',
                    'field_type' => 'text',
                    'placeholder' => 'กรอกชื่อและนามสกุล',
                    'help_text' => null,
                    'options' => null,
                    'is_required' => true,
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'label' => 'อีเมลผู้ติดต่อ',
                    'field_name' => 'contact_email',
                    'system_field' => 'contact_email',
                    'field_type' => 'email',
                    'placeholder' => 'example@email.com',
                    'help_text' => null,
                    'options' => null,
                    'is_required' => true,
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                [
                    'label' => 'เบอร์โทรศัพท์ผู้ติดต่อ',
                    'field_name' => 'contact_phone',
                    'system_field' => 'contact_phone',
                    'field_type' => 'phone',
                    'placeholder' => 'เช่น 0812345678',
                    'help_text' => null,
                    'options' => null,
                    'is_required' => true,
                    'is_active' => true,
                    'sort_order' => 3,
                ],
            ];

            foreach ($fixedFields as $field) {
                CompetitionTemplateFormField::create([
                    'template_id' => $template->id,
                    ...$field,
                ]);
            }

            return $template;
        });
        return redirect()->route('superadmin.templates.form-fields.create', ['template' => $template->id])->with('success','สร้าง Template สำเร็จ กรุณาสร้างช่องกรอกข้อมูลต่อ');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionTemplate $template)
    {
        $template->load(['formFields' => function ($query) {$query->orderBy('sort_order');}]);
        return view('superadmin.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionTemplate $template)
        {
            $template->load('formFields');
            return view('superadmin.templates.edit', compact('template'));
        }

    /**
     * Update the specified resource in storage.
     */
        public function update(Request $request, CompetitionTemplate $template)
        {
            $validated = $request->validate([
                'template_name' => ['required', 'string', 'max:255'],
                'template_slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('competition_templates', 'template_slug')
                        ->ignore($template->id),
                ],
                'default_description' => ['nullable', 'string'],
                'cover_image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:10240',
                ],
                'is_active' => ['nullable', 'boolean'],

                'form_fields' => ['nullable', 'array'],
                'form_fields.*.id' => ['required', 'integer'],
                'form_fields.*.label' => ['required', 'string', 'max:255'],
                'form_fields.*.field_type' => [
                    'required',
                    'in:text,textarea,number,email,phone,date,file,select,radio,checkbox'
                ],
                'form_fields.*.system_field' => [
                    'nullable',
                    'string',
                    'in:contact_name,contact_email,contact_phone,project_title,project_description,project_file',
                ],
                'form_fields.*.placeholder' => ['nullable', 'string'],
                'form_fields.*.help_text' => ['nullable', 'string'],
                'form_fields.*.options' => ['nullable', 'string'],
                'form_fields.*.is_required' => ['required', 'boolean'],
                'form_fields.*.is_active' => ['required', 'boolean'],
            ]);

            // แยกข้อมูล Form Fields ออกจากข้อมูล Template
            $formFields = $validated['form_fields'] ?? [];
            unset($validated['form_fields']);

            // ตรวจว่าฟิลด์ระบบที่จำเป็น (contact_name/email/phone) ยังครบอยู่
            // และไม่มีฟิลด์ระบบตัวไหนถูกกำหนดซ้ำกันมากกว่า 1 ช่อง
            $this->validateRequiredSystemFields($formFields);

            if (!$request->filled('template_slug')) {
                $validated['template_slug'] = $template->template_slug;
            }

            $validated['is_active'] = $request->boolean('is_active');

            if ($request->hasFile('cover_image')) {
                if ($template->cover_image) {
                    Storage::disk('public')->delete($template->cover_image);
                }

                $validated['cover_image'] = $request
                    ->file('cover_image')
                    ->store('competition-templates', 'public');
            }

            // อัปเดตตาราง competition_templates
            $template->update($validated);

            // อัปเดตตาราง competition_template_form_fields
            foreach ($formFields as $fieldId => $fieldData) {
                $field = $template->formFields()->findOrFail($fieldId);

                $options = preg_split(
                    '/\r\n|\r|\n|,/',
                    $fieldData['options'] ?? ''
                );

                $options = array_values(array_filter(
                    array_map('trim', $options)
                ));

                $field->update([
                    'label' => $fieldData['label'],
                    'field_name' => Str::snake($fieldData['label']),
                    'field_type' => $fieldData['field_type'],
                    'system_field' => $fieldData['system_field'] ?? null,
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'options' => $options,
                    'is_required' => (bool) $fieldData['is_required'],
                    'is_active' => (bool) $fieldData['is_active'],
                ]);
            }

            return redirect()
                ->route('superadmin.templates.index')
                ->with('success', 'แก้ไขข้อมูลสำเร็จ');
        }

    /**
     * Remove the specified resource from storage.
     */
        public function destroy(CompetitionTemplate $template)
        {
            if ($template->cover_image) {
                Storage::disk('public')->delete($template->cover_image);
            }
            $template->delete();
            return redirect()->route('superadmin.templates.index')->with('success', 'ลบ Template สำเร็จ');
        }

    /**
     * ตรวจสอบว่าชุด Form Fields ที่กำลังจะบันทึก
     * มีฟิลด์ระบบที่จำเป็นครบ (contact_name, contact_email, contact_phone)
     * และไม่มีฟิลด์ระบบตัวไหนถูกกำหนดซ้ำกันมากกว่า 1 ช่อง
     *
     * ป้องกันปัญหา Competition ที่สร้างจาก Template นี้
     * เปิดฟอร์มส่งผลงานไม่ได้ (system_field เป็น null/ไม่ครบ)
     */
    private function validateRequiredSystemFields(array $formFields): void
    {
        // ถ้ายังไม่มีฟิลด์เลย (ยังไม่เคยไปหน้า Form Builder) ไม่ต้องเช็ค
        // ปล่อยให้แก้ไขข้อมูลพื้นฐานของ Template ได้ก่อน
        if (empty($formFields)) {
            return;
        }

        $requiredSystemFields = [
            'contact_name',
            'contact_email',
            'contact_phone',
        ];

        $systemFieldsUsed = collect($formFields)
            ->pluck('system_field')
            ->filter(fn ($value) => filled($value))
            ->values();

        $missingFields = collect($requiredSystemFields)->diff($systemFieldsUsed);
        $duplicatedFields = $systemFieldsUsed->duplicates()->unique()->values();

        if ($missingFields->isEmpty() && $duplicatedFields->isEmpty()) {
            return;
        }

        $errors = [];

        if ($missingFields->isNotEmpty()) {
            $errors[] = 'กรุณากำหนดฟิลด์ระบบให้ครบ: '
                . $missingFields->implode(', ')
                . ' มิฉะนั้นผู้เข้าร่วมจะไม่สามารถเปิดฟอร์มส่งผลงานได้';
        }

        if ($duplicatedFields->isNotEmpty()) {
            $errors[] = 'พบฟิลด์ระบบที่ถูกกำหนดซ้ำกันมากกว่า 1 ช่อง: '
                . $duplicatedFields->implode(', ');
        }

        throw ValidationException::withMessages([
            'form_fields' => $errors,
        ]);
    }
}
