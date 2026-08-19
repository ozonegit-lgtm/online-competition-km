<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\CompetitionFormField;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SubmissionController extends Controller
{
    /**
     * แสดงแบบฟอร์มส่งผลงาน
     */
    public function create(Competition $competition)
    {
        $this->ensureCompetitionIsAcceptingSubmissions($competition);

        $competition->load([
            'category',
            'template',
            'formFields' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        $fields = $competition->formFields->values();

        if ($fields->isEmpty()) {
            throw ValidationException::withMessages([
                'form' => 'การแข่งขันนี้ยังไม่มีคำถามในแบบฟอร์ม',
            ]);
        }

        $this->attachResolvedOptions($fields);
        return view('submissions.create',compact('competition', 'fields'));
    }

    /**
     * ตรวจสอบและบันทึกผลงาน
     */
    public function store(Request $request, Competition $competition)
    {

        $this->ensureCompetitionIsAcceptingSubmissions($competition);

        $fields = $competition->formFields()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($fields->isEmpty()) {
            throw ValidationException::withMessages([
                'form' => 'การแข่งขันนี้ยังไม่มีคำถามในแบบฟอร์ม',
            ]);
        }

        [$rules, $attributes] = $this->buildValidationRules(
            $competition,
            $fields
        );

        $validated = $request->validate(
            $rules,
            [
                'required' => 'กรุณากรอก :attribute',
                'required_if' => 'กรุณากรอก :attribute',
                'accepted' => 'กรุณายืนยันความถูกต้องของข้อมูล',
                'email' => ':attribute ต้องเป็นอีเมลที่ถูกต้อง',
                'max' => ':attribute มีขนาดหรือความยาวเกินที่กำหนด',
                'mimes' => 'ประเภทไฟล์ของ :attribute ไม่รองรับ',
                'in' => 'ค่าที่เลือกใน :attribute ไม่ถูกต้อง',
                'regex' => 'รูปแบบ :attribute ไม่ถูกต้อง',
                'array' => ':attribute ต้องเป็นรายการตัวเลือก',
                'min' => 'กรุณาเลือก :attribute อย่างน้อยหนึ่งรายการ',
            ],
            $attributes
        );

        if (
            $competition->visibility === 'private' &&
            ! hash_equals(
                (string) $competition->access_code,
                (string) ($validated['access_code'] ?? '')
            )
        ) {
            throw ValidationException::withMessages([
                'access_code' => 'รหัสเข้าร่วมการแข่งขันไม่ถูกต้อง',
            ]);
        }


        $storedPaths = [];

        DB::beginTransaction();

        try {
            $submissionCode = $this->generateSubmissionCode();

            /*
             * Template Form เป็นแหล่งข้อมูลหลัก แล้วคัดลอกคำตอบของฟิลด์ระบบ
             * ลงคอลัมน์หลักเพื่อให้หน้ารายการและห้องตัดสินใช้งานได้ทันที
             */
            $systemValues = $fields
                ->filter(fn (CompetitionFormField $field) => filled($field->system_field))
                ->mapWithKeys(function (CompetitionFormField $field) use ($validated) {
                    $value = data_get($validated, "fields.{$field->id}");

                    return [$field->system_field => is_scalar($value) ? (string) $value : null];
                });

            $submission = Submission::create([
                'competition_id' => $competition->id,
                'submission_code' => $submissionCode,
                /*
                * ระบบ Dynamic Form ไม่มีช่องชื่อผลงานแบบตายตัว
                * จึงใช้รหัสการส่งเป็นชื่อรายการเริ่มต้น
                */
                'project_title' => $systemValues->get('project_title')
                    ?: "ผลงาน {$submissionCode}",
                'project_description' => null,
                'team_name' => $competition->competition_type === 'team'
                    ? ($validated['team_name'] ?? null)
                    : null,
                'contact_name' => $systemValues->get('contact_name'),
                'contact_email' => $systemValues->get('contact_email'),
                'contact_phone' => $systemValues->get('contact_phone'),
                'final_score' => 0,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $isPrimaryFile = true;

            foreach ($fields as $field) {
                $inputKey = "fields.{$field->id}";

                if ($field->field_type === 'file') {
                    $uploadedFile = $request->file($inputKey);

                    if (! $uploadedFile) {
                        continue;
                    }

                    $storedPath = $uploadedFile->store(
                        "submissions/{$competition->id}/{$submission->submission_code}",
                        'public'
                    );

                    $storedPaths[] = $storedPath;

                    $submission->files()->create([
                        'original_name' => $uploadedFile->getClientOriginalName(),
                        'stored_name' => basename($storedPath),
                        'file_path' => $storedPath,
                        'file_extension' => strtolower(
                            $uploadedFile->getClientOriginalExtension()
                        ),
                        'mime_type' => $uploadedFile->getMimeType()
                            ?: $uploadedFile->getClientMimeType(),
                        'file_size' => $uploadedFile->getSize(),
                        'is_primary' => $isPrimaryFile,
                    ]);

                    // เก็บ path ไว้กับ field_id เพื่อทราบว่าไฟล์มาจากช่องใด
                    $submission->fieldValues()->create([
                        'field_id' => $field->id,
                        'field_value' => $storedPath,
                    ]);

                    $isPrimaryFile = false;
                    continue;
                }

                $value = data_get($validated, $inputKey);

                if ($value === null || $value === '') {
                    continue;
                }

                $submission->fieldValues()->create([
                    'field_id' => $field->id,
                    'field_value' => is_array($value)
                        ? json_encode($value, JSON_UNESCAPED_UNICODE)
                        : (string) $value,
                ]);
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            foreach ($storedPaths as $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        return redirect()->route(
            'submissions.success',
            ['submission' => $submission->submission_code]
        );
    }

    /**
     * แสดงผลหลังส่งผลงานสำเร็จ
     */
    public function success(Submission $submission)
    {
        $submission->load('competition');

        return view(
            'submissions.success',
            compact('submission')
        );
    }

    private function ensureCompetitionIsAcceptingSubmissions(
        Competition $competition
    ): void {
        abort_unless(
            $competition->isRegistrationOpen(),
            403,
            'หมดเวลารับผลงานแล้ว'
        );
    }


    private function attachResolvedOptions(Collection $fields): void
    {
        $fields->each(function (CompetitionFormField $field) {
            $field->setAttribute(
                'resolved_options',
                $this->resolveFieldOptions($field)
            );
        });
    }

    private function buildValidationRules(
        Competition $competition,
        Collection $fields
    ): array {
        $rules = [
            'access_code' => $competition->visibility === 'private'
                ? ['required', 'string', 'max:100']
                : ['nullable'],
            'team_name' => [
                Rule::requiredIf($competition->competition_type === 'team'),
                'nullable',
                'string',
                'max:255',
            ],
            'terms' => ['accepted'],
        ];

        $attributes = [
            'access_code' => 'รหัสเข้าร่วม',
            'team_name' => 'ชื่อทีม',
            'terms' => 'การยืนยันข้อมูล',
        ];

        foreach ($fields as $field) {
            $key = "fields.{$field->id}";
            $requiredRule = $field->is_required ? 'required' : 'nullable';
            $options = $this->resolveFieldOptions($field);

            $rules[$key] = match ($field->field_type) {
                'textarea' => [$requiredRule, 'string', 'max:10000'],
                'number' => [$requiredRule, 'numeric'],
                'email' => [$requiredRule, 'email', 'max:150'],
                'phone' => [
                    $requiredRule,
                    'string',
                    'max:20',
                    'regex:/^[0-9+\-\s()]{8,20}$/',
                ],
                'date' => [$requiredRule, 'date'],
                'file' => [
                    $requiredRule,
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf,doc,docx,ppt,pptx,zip',
                    'max:' . (($field->max_file_size ?: 10) * 1024),
                ],
                'select', 'radio' => [
                    $requiredRule,
                    'string',
                    Rule::in($options),
                ],
                'checkbox' => [
                    $requiredRule,
                    'array',
                    ...($field->is_required ? ['min:1'] : []),
                ],
                default => [
                    $requiredRule,
                    'string',
                    'max:1000',
                ],
            };

            if ($field->field_type === 'checkbox') {
                $rules["{$key}.*"] = [
                    'string',
                    Rule::in($options),
                ];
            }

            $attributes[$key] = $field->label;
            $attributes["{$key}.*"] = $field->label;
        }

        return [$rules, $attributes];
    }



    private function resolveFieldOptions(
        CompetitionFormField $field
    ): array {
        $rawOptions = $field->getRawOriginal('options');

        if (blank($rawOptions)) {
            return [];
        }

        $decodedOptions = json_decode($rawOptions, true);

        $options = is_array($decodedOptions)
            ? $decodedOptions
            : preg_split('/\r\n|\r|\n|,/', $rawOptions);

        return collect($options)
            ->map(function ($option) {
                if (is_array($option)) {
                    return $option['label'] ?? $option['value'] ?? null;
                }

                return trim((string) $option);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function generateSubmissionCode(): string
    {
        do {
            $code = 'SUB-' . now()->format('Ymd') . '-'
                . Str::upper(Str::random(6));
        } while (
            Submission::where('submission_code', $code)->exists()
        );

        return $code;
    }
}