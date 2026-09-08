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

    private const MAX_EXPANDED_BYTES = 100 * 1024 * 1024;

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
            // Fileinfo can miss OOXML when property parts precede the main part.
            // This candidate still has to pass the full OOXML container checks.
            'application/octet-stream',
            'application/zip',
            'application/x-zip-compressed',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'pptx' => [
            'application/octet-stream',
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
        $office = $extension !== 'zip';
        if ($office && ! $this->startsWith($path, "PK\x03\x04")) {
            return false;
        }

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::RDONLY | ($office ? ZipArchive::CHECKCONS : 0)) !== true) {
            return false;
        }

        try {
            if ($extension === 'zip') {
                return true;
            }

            return $this->validOfficeEntries($zip)
                && $this->validOfficeDocument($zip, $extension);
        } finally {
            $zip->close();
        }
    }

    private function validOfficeEntries(ZipArchive $zip): bool
    {
        $names = [];
        $expanded = 0;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entry = $zip->statIndex($index);
            if ($entry === false || isset($names[$entry['name']])
                || ($entry['encryption_method'] ?? 0) !== 0) {
                return false;
            }
            $names[$entry['name']] = true;
            $expanded += $entry['size'];
            if ($expanded > self::MAX_EXPANDED_BYTES) {
                return false;
            }
            if (str_ends_with($entry['name'], '/')) {
                continue;
            }

            $stream = @$zip->getStream($entry['name']);
            if ($stream === false) {
                return false;
            }
            $crc = hash_init('crc32b');
            $read = 0;
            try {
                while (! feof($stream)) {
                    $chunk = @fread($stream, 8192);
                    if ($chunk === false || ($chunk === '' && ! feof($stream))) {
                        return false;
                    }
                    $read += strlen($chunk);
                    if ($read > $entry['size']) {
                        return false;
                    }
                    hash_update($crc, $chunk);
                }
            } finally {
                fclose($stream);
            }
            if ($read !== $entry['size'] || hash_final($crc) !== sprintf('%08x', $entry['crc'])) {
                return false;
            }
        }

        return true;
    }

    private function validOfficeDocument(ZipArchive $zip, string $extension): bool
    {
        $word = $extension === 'docx';
        $part = $word ? 'word/document.xml' : 'ppt/presentation.xml';
        $type = $word ? 'wordprocessingml.document' : 'presentationml.presentation';
        $types = $this->officeXml($zip, '[Content_Types].xml');
        $relationships = $this->officeXml($zip, '_rels/.rels');
        $document = $this->officeXml($zip, $part);

        if ($types?->documentElement?->localName !== 'Types'
            || $types->documentElement->namespaceURI !== 'http://schemas.openxmlformats.org/package/2006/content-types'
            || $relationships?->documentElement?->localName !== 'Relationships'
            || $relationships->documentElement->namespaceURI !== 'http://schemas.openxmlformats.org/package/2006/relationships'
            || $document?->documentElement?->localName !== ($word ? 'document' : 'presentation')) {
            return false;
        }

        $namespace = $word ? 'wordprocessingml' : 'presentationml';
        if (! in_array($document->documentElement->namespaceURI, [
            'http://schemas.openxmlformats.org/'.$namespace.'/2006/main',
            'http://purl.oclc.org/ooxml/'.$namespace.'/main',
        ], true)) {
            return false;
        }

        $matchingType = false;
        foreach ($types->documentElement->childNodes as $node) {
            if ($node instanceof \DOMElement && $node->localName === 'Override'
                && $node->namespaceURI === $types->documentElement->namespaceURI
                && $node->getAttribute('PartName') === '/'.$part) {
                if ($matchingType || $node->getAttribute('ContentType') !== 'application/vnd.openxmlformats-officedocument.'.$type.'.main+xml') {
                    return false;
                }
                $matchingType = true;
            }
        }

        $matchingRelationship = false;
        foreach ($relationships->documentElement->childNodes as $node) {
            if ($node instanceof \DOMElement && $node->localName === 'Relationship'
                && $node->namespaceURI === $relationships->documentElement->namespaceURI
                && in_array($node->getAttribute('Type'), [
                    'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument',
                    'http://purl.oclc.org/ooxml/officeDocument/relationships/officeDocument',
                ], true)) {
                if ($matchingRelationship || ! in_array($node->getAttribute('Target'), [$part, '/'.$part], true)
                    || ! in_array($node->getAttribute('TargetMode'), ['', 'Internal'], true)) {
                    return false;
                }
                $matchingRelationship = true;
            }
        }

        return $matchingType && $matchingRelationship;
    }

    private function officeXml(ZipArchive $zip, string $name): ?\DOMDocument
    {
        $contents = @$zip->getFromName($name, self::MAX_BYTES + 1);
        if ($contents === false || $contents === '' || strlen($contents) > self::MAX_BYTES) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $document = new \DOMDocument();
            // Never load external DTDs or substitute entities from an upload.
            if (! $document->loadXML($contents, LIBXML_NONET) || $document->doctype !== null) {
                return null;
            }

            return $document;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
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
