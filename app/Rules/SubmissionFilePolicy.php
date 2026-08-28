<?php

namespace App\Rules;

use App\Models\CompetitionFormField;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class SubmissionFilePolicy implements ValidationRule
{
    public function __construct(
        private readonly CompetitionFormField $field
    ) {
    }

    public static function resolve(CompetitionFormField $field): array
    {
        $global = array_values(config('submissions.uploads.allowed_extensions', []));
        $raw = $field->accepted_file_types;

        if ($raw === null || trim((string) $raw) === '') {
            $extensions = $global;
        } else {
            $requested = collect(explode(',', (string) $raw))
                ->map(fn ($extension) => strtolower(ltrim(trim($extension), '.')))
                ->filter()
                ->unique()
                ->all();
            $extensions = array_values(array_filter(
                $global,
                fn ($extension) => in_array($extension, $requested, true)
            ));
        }

        $globalMaximum = max(1, (int) config('submissions.uploads.max_file_megabytes', 10));
        $fieldMaximum = $field->max_file_size === null
            ? $globalMaximum
            : max(0, (int) $field->max_file_size);

        return [
            'extensions' => $extensions,
            'max_megabytes' => min($fieldMaximum, $globalMaximum),
            'valid' => $extensions !== [] && $fieldMaximum > 0,
        ];
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $policy = self::resolve($this->field);

        if (! $policy['valid']) {
            $fail('การตั้งค่าช่องอัปโหลดไม่ถูกต้อง');
            return;
        }

        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('ไฟล์เอกสารไม่สมบูรณ์หรือไม่สามารถเปิดตรวจสอบได้');
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        if (! in_array($extension, $policy['extensions'], true)) {
            $fail('ช่อง “'.$this->field->label.'” รองรับเฉพาะ '.strtoupper(implode(', ', $policy['extensions'])));
            return;
        }

        if ($value->getSize() > $policy['max_megabytes'] * 1024 * 1024) {
            $fail('ไฟล์ต้องมีขนาดไม่เกิน '.$policy['max_megabytes'].' MB');
            return;
        }

        $path = $value->getRealPath();
        $mime = $path ? (new \finfo(FILEINFO_MIME_TYPE))->file($path) : false;
        $allowedMimes = config("submissions.uploads.mime_types.{$extension}", []);

        if (! is_string($mime) || ! in_array($mime, $allowedMimes, true) || ! $this->hasValidStructure($extension, $path)) {
            $fail('ชนิดข้อมูลภายในไฟล์ไม่ตรงกับนามสกุล');
        }
    }

    private function hasValidStructure(string $extension, string|false $path): bool
    {
        if (! $path) {
            return false;
        }

        if ($extension === 'pdf') {
            $handle = @fopen($path, 'rb');
            $signature = $handle ? fread($handle, 5) : false;
            if (is_resource($handle)) {
                fclose($handle);
            }
            return $signature === '%PDF-';
        }

        if (in_array($extension, ['doc', 'ppt'], true)) {
            $handle = @fopen($path, 'rb');
            $signature = $handle ? fread($handle, 8) : false;
            if (is_resource($handle)) {
                fclose($handle);
            }
            return $signature === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
        }

        if (in_array($extension, ['docx', 'pptx', 'zip'], true)) {
            $zip = new ZipArchive();
            if ($zip->open($path, ZipArchive::RDONLY) !== true) {
                return false;
            }
            $valid = $extension === 'zip' || (
                $zip->locateName('[Content_Types].xml') !== false
                && $zip->locateName($extension === 'docx' ? 'word/document.xml' : 'ppt/presentation.xml') !== false
            );
            $zip->close();
            return $valid;
        }

        return true;
    }
}
