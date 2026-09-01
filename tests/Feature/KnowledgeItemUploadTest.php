<?php

namespace Tests\Feature;

use App\Http\Requests\KnowledgeItemRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use ZipArchive;

class KnowledgeItemUploadTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

    private ?int $categoryId = null;

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_required_and_server_controlled_fields_are_validated(): void
    {
        $validator = $this->validator([]);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        $validator = $this->validator([
            'title' => 'Manual KM',
            'category_id' => 999999,
        ]);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        foreach ([
            'created_by',
            'submission_id',
            'attachment_path',
            'attachment_original_name',
            'status',
            'published_at',
            'is_featured',
        ] as $field) {
            $validator = $this->validator(array_merge(
                $this->validData(),
                [$field => $field === 'is_featured' ? true : 1]
            ));

            $this->assertTrue($validator->fails(), $field);
            $this->assertArrayHasKey(
                $field,
                $validator->errors()->toArray()
            );
        }
    }

    public function test_cover_and_attachment_are_optional_and_size_is_limited(): void
    {
        $this->assertFalse($this->validator($this->validData())->fails());

        $oversized = $this->upload(
            'oversized.pdf',
            "%PDF-1.4\n".str_repeat('0', 10 * 1024 * 1024)
        );
        $validator = $this->validator(array_merge(
            $this->validData(),
            ['attachment' => $oversized]
        ));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('attachment', $validator->errors()->toArray());
    }

    public function test_cover_accepts_real_jpeg_png_and_webp_images(): void
    {
        foreach ($this->realImages() as $name => $contents) {
            $validator = $this->validator(array_merge(
                $this->validData(),
                ['cover_image' => $this->upload($name, $contents)]
            ));

            $this->assertFalse(
                $validator->fails(),
                $name.': '.implode(' ', $validator->errors()->all())
            );
        }
    }

    public function test_cover_rejects_non_images_spoofs_and_unsupported_extensions(): void
    {
        foreach ([
            $this->pdf('document.pdf'),
            $this->upload('spoofed.jpg', 'not an image'),
            $this->upload('image.gif', 'GIF89a'),
        ] as $file) {
            $validator = $this->validator(array_merge(
                $this->validData(),
                ['cover_image' => $file]
            ));

            $this->assertTrue($validator->fails());
            $this->assertArrayHasKey(
                'cover_image',
                $validator->errors()->toArray()
            );
        }
    }

    public function test_attachment_accepts_real_images_pdf_ole_ooxml_and_zip(): void
    {
        $files = [];

        foreach ($this->realImages() as $name => $contents) {
            $files[] = $this->upload($name, $contents);
        }

        $ole = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1".str_repeat("\0", 512);
        $files[] = $this->pdf('คู่มือองค์ความรู้.pdf');
        $files[] = $this->upload('document.doc', $ole);
        $files[] = $this->upload('slides.ppt', $ole);
        $files[] = $this->zip('document.docx', [
            '[Content_Types].xml' => '<Types/>',
            'word/document.xml' => '<document/>',
        ]);
        $files[] = $this->zip('slides.pptx', [
            '[Content_Types].xml' => '<Types/>',
            'ppt/presentation.xml' => '<presentation/>',
        ]);
        $files[] = $this->zip('archive.zip', [
            'readme.txt' => 'knowledge',
        ]);

        foreach ($files as $file) {
            $validator = $this->validator(array_merge(
                $this->validData(),
                ['attachment' => $file]
            ));

            $this->assertFalse(
                $validator->fails(),
                $file->getClientOriginalName().': '
                    .implode(' ', $validator->errors()->all())
            );
        }
    }

    public function test_attachment_rejects_spoofed_broken_and_unsupported_files(): void
    {
        $invalidFiles = [
            $this->upload('fake.pdf', 'not a pdf'),
            $this->zip('fake.docx', ['readme.txt' => 'missing Word parts']),
            $this->zip('fake.pptx', ['readme.txt' => 'missing PowerPoint parts']),
            $this->upload('broken.zip', 'PK broken archive'),
            $this->upload('program.exe', 'MZ executable'),
            $this->upload('notes.txt', 'plain text'),
            $this->zip('sheet.xlsx', [
                '[Content_Types].xml' => '<Types/>',
                'xl/workbook.xml' => '<workbook/>',
            ]),
            $this->upload('double.exe.pdf', 'MZ executable'),
        ];

        foreach ($invalidFiles as $file) {
            $validator = $this->validator(array_merge(
                $this->validData(),
                ['attachment' => $file]
            ));

            $this->assertTrue(
                $validator->fails(),
                $file->getClientOriginalName()
            );
            $this->assertArrayHasKey(
                'attachment',
                $validator->errors()->toArray()
            );
        }
    }

    public function test_validation_never_writes_to_public_storage(): void
    {
        Storage::fake('public');

        $validator = $this->validator(array_merge(
            $this->validData(),
            [
                'cover_image' => $this->upload(
                    'cover.png',
                    $this->realImages()['cover.png']
                ),
                'attachment' => $this->pdf('document.pdf'),
            ]
        ));

        $this->assertFalse($validator->fails());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    private function validator(array $data)
    {
        $request = new KnowledgeItemRequest();

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    private function validData(): array
    {
        return [
            'title' => 'Manual Knowledge Item',
            'category_id' => $this->categoryId(),
            'summary' => 'Summary',
            'content' => 'Content',
        ];
    }

    private function categoryId(): int
    {
        if ($this->categoryId !== null) {
            return $this->categoryId;
        }

        return $this->categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function realImages(): array
    {
        return [
            'cover.jpg' => base64_decode(
                '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAf/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAF//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABBQJ//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAwEBPwF//8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAgEBPwF//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQAGPwJ//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPyF//9oADAMBAAIAAwAAABD/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAEDAQE/EB//xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oACAECAQE/EB//xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAE/EB//2Q=='
            ),
            'cover.png' => base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
            ),
            'cover.webp' => base64_decode(
                'UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEALmk0mk0iIiIiIgBoSygABc6zbAAA'
            ),
        ];
    }

    private function pdf(string $name): UploadedFile
    {
        return $this->upload(
            $name,
            "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF"
        );
    }

    private function zip(string $name, array $entries): UploadedFile
    {
        $path = $this->temporaryPath();
        $zip = new ZipArchive();
        $this->assertTrue(
            $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE)
        );

        try {
            foreach ($entries as $entry => $contents) {
                $zip->addFromString($entry, $contents);
            }
        } finally {
            $zip->close();
        }

        return new UploadedFile($path, $name, null, null, true);
    }

    private function upload(string $name, string $contents): UploadedFile
    {
        $path = $this->temporaryPath();
        file_put_contents($path, $contents);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function temporaryPath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'km-upload-');
        $this->assertNotFalse($path);
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
