<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use ZipArchive;

class KnowledgeItemFilePolicy implements ValidationRule
{
    private const COVER = 'cover';

    private const ATTACHMENT = 'attachment';

    private const MAX_BYTES = 10 * 1024 * 1024;

    private const IMAGE_MIMES = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
    ];

    private const ATTACHMENT_MIMES = [
        'pdf' => ['application/pdf'],
        'doc' => [
            'application/msword',
            'application/x-ole-storage',
            'application/vnd.ms-office',
            'application/CDFV2',
        ],
        'ppt' => [
            'application/vnd.ms-powerpoint',
            'application/x-ole-storage',
            'application/vnd.ms-office',
            'application/CDFV2',
        ],
        'docx' => [
            'application/zip',
            'application/x-zip-compressed',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'pptx' => [
            'application/zip',
            'application/x-zip-compressed',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ],
        'zip' => [
            'application/zip',
            'application/x-zip-compressed',
        ],
    ];

    private function __construct(private readonly string $mode)
    {
    }

    public static function cover(): self
    {
        return new self(self::COVER);
    }

    public static function attachment(): self
    {
        return new self(self::ATTACHMENT);
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('ไฟล์ไม่สมบูรณ์หรือไม่สามารถอ่านได้');
            return;
        }

        if ($value->getSize() > self::MAX_BYTES) {
            $fail('ไฟล์ต้องมีขนาดไม่เกิน 10 MB');
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $allowed = $this->mode === self::COVER
            ? array_keys(self::IMAGE_MIMES)
            : array_merge(
                array_keys(self::IMAGE_MIMES),
                array_keys(self::ATTACHMENT_MIMES)
            );

        if ($extension === '' || ! in_array($extension, $allowed, true)) {
            $fail($this->unsupportedTypeMessage());
            return;
        }

        $path = $value->getPathname();

        if (! is_file($path) || ! is_readable($path)) {
            $fail('ไม่สามารถอ่านและตรวจสอบเนื้อหาไฟล์ได้');
            return;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if (! is_string($mime) || ! $this->validMime($extension, $mime)) {
            $fail('ชนิดข้อมูลภายในไฟล์ไม่ตรงกับนามสกุลไฟล์');
            return;
        }

        if (! $this->hasValidStructure($extension, $path)) {
            $fail('โครงสร้างหรือเนื้อหาภายในไฟล์ไม่ถูกต้อง');
        }
    }

    private function validMime(string $extension, string $mime): bool
    {
        $allowedMimes = self::IMAGE_MIMES[$extension]
            ?? self::ATTACHMENT_MIMES[$extension]
            ?? [];

        return in_array($mime, $allowedMimes, true);
    }

    private function hasValidStructure(string $extension, string $path): bool
    {
        if (isset(self::IMAGE_MIMES[$extension])) {
            return $this->validImage($extension, $path);
        }

        if ($extension === 'pdf') {
            return $this->startsWith($path, '%PDF-');
        }

        if (in_array($extension, ['doc', 'ppt'], true)) {
            return $this->startsWith(
                $path,
                "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"
            );
        }

        if (in_array($extension, ['docx', 'pptx', 'zip'], true)) {
            return $this->validZip($extension, $path);
        }

        return false;
    }

    private function validImage(string $extension, string $path): bool
    {
        $image = @getimagesize($path);

        if ($image === false) {
            return false;
        }

        $expectedType = match ($extension) {
            'jpg', 'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
            'webp' => IMAGETYPE_WEBP,
            default => null,
        };

        return $expectedType !== null
            && ($image[2] ?? null) === $expectedType;
    }

    private function startsWith(string $path, string $signature): bool
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        try {
            return fread($handle, strlen($signature)) === $signature;
        } finally {
            fclose($handle);
        }
    }

    private function validZip(string $extension, string $path): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            return false;
        }

        try {
            if ($extension === 'zip') {
                return true;
            }

            $documentPath = $extension === 'docx'
                ? 'word/document.xml'
                : 'ppt/presentation.xml';

            return $zip->locateName('[Content_Types].xml') !== false
                && $zip->locateName($documentPath) !== false;
        } finally {
            $zip->close();
        }
    }

    private function unsupportedTypeMessage(): string
    {
        if ($this->mode === self::COVER) {
            return 'ภาพปกรองรับเฉพาะไฟล์ JPG, JPEG, PNG และ WEBP';
        }

        return 'ไฟล์แนบเป็นชนิดที่ระบบไม่รองรับ';
    }
}
