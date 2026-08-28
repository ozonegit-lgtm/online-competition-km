<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\CompetitionFormField;
use App\Models\Submission;
use App\Rules\SubmissionFilePolicy;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
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
        $formGuardToken = Crypt::encryptString(json_encode([
            'competition_id' => $competition->id,
            'issued_at' => now()->timestamp,
            'nonce' => Str::random(40),
        ], JSON_THROW_ON_ERROR));

        return view('submissions.create', compact(
            'competition',
            'fields',
            'formGuardToken'
        ));
    }

    /**
     * ตรวจสอบและบันทึกผลงาน
     */
    public function store(Request $request, Competition $competition)
    {
        $this->ensureCompetitionIsAcceptingSubmissions($competition);

        $fields = $competition->formFields()
            ->where('is_active', true)
            ->get();
        $nonceKey = $this->validateSubmissionProtection(
            $request,
            $competition,
            $fields
        );
        $lock = Cache::lock(
            "public-submission:lock:{$nonceKey}",
            config('submissions.form_guard.lock_seconds')
        );

        if (! $lock->get()) {
            throw ValidationException::withMessages([
                'form' => 'ไม่สามารถส่งผลงานซ้ำได้ กรุณารอสักครู่',
            ]);
        }

        $usedKey = "public-submission:used:{$nonceKey}";

        try {
            if (Cache::has($usedKey)) {
                throw ValidationException::withMessages([
                    'form' => 'แบบฟอร์มนี้ถูกส่งเรียบร้อยแล้ว กรุณาเปิดแบบฟอร์มใหม่',
                ]);
            }

            if (! Cache::put(
                $usedKey,
                true,
                now()->addMinutes(config('submissions.form_guard.ttl_minutes'))
            )) {
                throw ValidationException::withMessages([
                    'form' => 'ไม่สามารถยืนยันแบบฟอร์มได้ กรุณาลองใหม่อีกครั้ง',
                ]);
            }

            try {
                return $this->persistSubmission($request, $competition);
            } catch (Throwable $exception) {
                Cache::forget($usedKey);
                throw $exception;
            }
        } finally {
            $lock->release();
        }
    }

    private function persistSubmission(
        Request $request,
        Competition $competition
    )
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



            $submission = Submission::create([
                'competition_id' => $competition->id,
                'submission_code' => $submissionCode,
                'project_title' => $validated['project_title'],
                'project_description' => null,
                'team_name' => $competition->competition_type === 'team'
                    ? ($validated['team_name'] ?? null)
                    : null,
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'final_score' => null,
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
            if ($field->field_type === 'file') {
                $field->setAttribute('resolved_file_policy', SubmissionFilePolicy::resolve($field));
            }
        });
    }

    private function validateSubmissionProtection(
        Request $request,
        Competition $competition,
        Collection $fields
    ): string {
        $contentLength = max(0, (int) $request->server('CONTENT_LENGTH', 0));
        if ($contentLength > config('submissions.uploads.max_request_kilobytes') * 1024) {
            throw ValidationException::withMessages([
                'files' => 'ข้อมูลที่ส่งทั้งหมดต้องมีขนาดไม่เกิน 25 MB',
            ]);
        }

        if (filled($request->input('website'))) {
            throw ValidationException::withMessages([
                'form' => 'ไม่สามารถส่งแบบฟอร์มได้ กรุณาตรวจสอบข้อมูลแล้วลองใหม่',
            ]);
        }

        try {
            $payload = json_decode(
                Crypt::decryptString((string) $request->input('form_guard_token')),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (! is_array($payload)) {
                throw new \JsonException('Invalid form guard payload.');
            }
        } catch (DecryptException|\JsonException) {
            throw ValidationException::withMessages([
                'form' => 'แบบฟอร์มไม่ถูกต้องหรือหมดอายุ กรุณาเปิดแบบฟอร์มใหม่',
            ]);
        }

        $issuedAt = filter_var(
            $payload['issued_at'] ?? null,
            FILTER_VALIDATE_INT
        );
        $nonce = $payload['nonce'] ?? null;
        $age = $issuedAt === false ? null : now()->timestamp - $issuedAt;

        if (
            (int) ($payload['competition_id'] ?? 0) !== (int) $competition->id
            || ! is_string($nonce)
            || $nonce === ''
            || $age === null
            || $age < config('submissions.form_guard.minimum_seconds')
            || $age > config('submissions.form_guard.ttl_minutes') * 60
        ) {
            throw ValidationException::withMessages([
                'form' => 'แบบฟอร์มไม่ถูกต้องหรือหมดอายุ กรุณาเปิดแบบฟอร์มใหม่',
            ]);
        }

        $files = collect(Arr::dot($request->allFiles()))
            ->filter(fn ($file) => $file instanceof \Illuminate\Http\UploadedFile);
        $allowedFileInputs = $fields
            ->where('field_type', 'file')
            ->mapWithKeys(fn ($field) => ["fields.{$field->id}" => true]);

        if ($files->keys()->contains(
            fn ($key) => ! $allowedFileInputs->has($key)
        )) {
            throw ValidationException::withMessages([
                'files' => 'พบไฟล์ในช่องที่ไม่ได้รับอนุญาต',
            ]);
        }

        if ($files->count() > config('submissions.uploads.max_files')) {
            throw ValidationException::withMessages([
                'files' => 'แนบไฟล์ได้ไม่เกิน '.config('submissions.uploads.max_files').' ไฟล์',
            ]);
        }

        $totalKilobytes = (int) ceil(
            $files->sum(fn ($file) => max(0, (int) $file->getSize())) / 1024
        );

        if ($totalKilobytes > config('submissions.uploads.max_total_kilobytes')) {
            throw ValidationException::withMessages([
                'files' => 'ขนาดไฟล์รวมต้องไม่เกิน 20 MB',
            ]);
        }

        return hash('sha256', $nonce);
    }

    private function buildValidationRules(
        Competition $competition,
        Collection $fields
    ): array {
        $rules = [
            'project_title' => [
                'required',
                'string',
                'max:255',
            ],
            'contact_name' => [
                'required',
                'string',
                'max:150',
            ],
            'contact_email' => [
                'required',
                'email',
                'max:150',
            ],
            'contact_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\\-\\s()]{8,20}$/',
            ],
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
        'project_title' => 'ชื่อผลงาน',
        'contact_name' => 'ชื่อ-นามสกุลผู้ส่ง',
        'contact_email' => 'อีเมล',
        'contact_phone' => 'เบอร์โทรศัพท์',
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
                    new SubmissionFilePolicy($field),
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
