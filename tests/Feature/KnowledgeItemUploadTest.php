<?php

namespace Tests\Feature;

use App\Http\Requests\KnowledgeItemRequest;
use App\Models\KnowledgeItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use ZipArchive;

class KnowledgeItemUploadTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

    private ?int $categoryId = null;

    public static function manualOfficeUploads(): array
    {
        $uploads = [
            'super admin docx' => ['Super Admin', 'superadmin', 'docx'],
            'super admin pptx' => ['Super Admin', 'superadmin', 'pptx'],
            'competition admin docx' => ['Competition Admin', 'competition-admin', 'docx'],
            'competition admin pptx' => ['Competition Admin', 'competition-admin', 'pptx'],
        ];

        foreach (['superadmin' => 'Super Admin', 'competition-admin' => 'Competition Admin'] as $prefix => $role) {
            foreach (['docx', 'pptx'] as $extension) {
                $uploads[$prefix.' generic MIME '.$extension] = [$role, $prefix, $extension, 'generic'];
            }
            foreach (['microsoft-word.docx', 'microsoft-powerpoint.pptx', 'libreoffice-writer.docx', 'libreoffice-impress.pptx'] as $fixture) {
                $uploads[$prefix.' '.$fixture] = [$role, $prefix, pathinfo($fixture, PATHINFO_EXTENSION), $fixture];
            }
        }

        return $uploads;
    }

    #[DataProvider('manualOfficeUploads')]
    public function test_both_roles_can_create_and_replace_manual_ooxml_attachments(
        string $roleName, string $routePrefix, string $extension, ?string $fixture = null
    ): void {
        Storage::fake('local');
        Storage::fake('public');
        $this->actingAs($this->manualAuthor($roleName));
        $this->assertAttachmentInput($this->get(route($routePrefix.'.km.create'))->assertOk()->getContent());

        $file = $this->officeDocument($extension, $fixture);
        $contents = file_get_contents($file->getPathname());
        $this->post(route($routePrefix.'.km.store'), array_merge($this->validData(), [
            'attachment' => $file,
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $item = KnowledgeItem::sole();
        $this->assertSame(auth()->id(), $item->created_by);
        $this->assertNull($item->submission_id);
        $this->assertSame($file->getClientOriginalName(), $item->attachment_original_name);
        $this->assertSame($contents, Storage::disk('local')->get($item->attachment_path));
        $oldPath = $item->attachment_path;
        $this->assertAttachmentInput($this->get(route($routePrefix.'.km.edit', $item))->assertOk()->getContent());

        $replacement = $this->officeDocument($extension, $fixture);
        $this->post(route($routePrefix.'.km.update', $item), array_merge($this->validData(), [
            '_method' => 'PUT', 'attachment' => $replacement,
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $item->refresh();
        $this->assertNotSame($oldPath, $item->attachment_path);
        Storage::disk('local')->assertMissing($oldPath);
        $this->assertSame(file_get_contents($replacement->getPathname()), Storage::disk('local')->get($item->attachment_path));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    #[DataProvider('manualOfficeUploads')]
    public function test_both_roles_reject_invalid_manual_ooxml_uploads(
        string $roleName, string $routePrefix, string $extension, ?string $fixture = null
    ): void {
        Storage::fake('local');
        Storage::fake('public');
        $this->actingAs($this->manualAuthor($roleName));
        $valid = $this->officeDocument($extension, $fixture);
        $bytes = file_get_contents($valid->getPathname());
        foreach ([
            $this->upload('spoofed.'.$extension, 'not an Office document'),
            $this->zip('fake.'.$extension, ['readme.txt' => 'not OOXML']),
            $this->upload('corrupt.'.$extension, substr($bytes, 0, intdiv(strlen($bytes), 2))),
            $this->upload('unsupported.exe', $bytes),
            $this->upload('oversized.'.$extension, $bytes.str_repeat('0', 10 * 1024 * 1024)),
        ] as $file) {
            $this->post(route($routePrefix.'.km.store'), array_merge($this->validData(), [
                'attachment' => $file,
            ]))->assertSessionHasErrors('attachment');
            $this->assertSame(0, KnowledgeItem::count());
            $this->assertSame([], Storage::disk('local')->allFiles());
            $this->assertSame([], Storage::disk('public')->allFiles());
        }
    }

    private function assertAttachmentInput(string $html): void
    {
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $input = (new \DOMXPath($document))->query('//input[@name="attachment"]')->item(0);
        $this->assertNotNull($input);
        $this->assertSame('file', $input->getAttribute('type'));
        foreach (['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip'] as $extension) {
            $this->assertContains('.'.$extension, explode(',', $input->getAttribute('accept')));
        }
    }

    private function manualAuthor(string $roleName): User
    {
        $roleId = DB::table('roles')->where('role_name', $roleName)->value('id')
            ?? DB::table('roles')->insertGetId(['role_name' => $roleName, 'display_name' => $roleName]);

        return User::create([
            'role_id' => $roleId, 'username' => 'office-'.uniqid(),
            'email' => uniqid().'@example.com', 'password' => 'password', 'is_active' => true,
        ]);
    }

    private function officeDocument(string $extension, ?string $fixture = null): UploadedFile
    {
        if ($fixture !== null && $fixture !== 'generic') {
            return $this->upload($fixture, file_get_contents(base_path('tests/Fixtures/manual-km-office/'.$fixture)));
        }

        $word = $extension === 'docx';
        $part = $word ? 'word/document.xml' : 'ppt/presentation.xml';
        $type = $word ? 'wordprocessingml.document' : 'presentationml.presentation';
        $entries = [
            '[Content_Types].xml' => '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/'.$part.'" ContentType="application/vnd.openxmlformats-officedocument.'.$type.'.main+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="'.$part.'"/></Relationships>',
            $part => $word
                ? '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Manual KM</w:t></w:r></w:p></w:body></w:document>'
                : '<?xml version="1.0"?><p:presentation xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"><p:sldMasterIdLst/><p:sldIdLst/><p:sldSz cx="9144000" cy="6858000"/><p:notesSz cx="6858000" cy="9144000"/></p:presentation>',
        ];
        if ($fixture === 'generic') {
            // Match the failing uploads: property parts precede the main Office
            // part. Fileinfo may return octet-stream for this valid ZIP order.
            $main = $entries[$part];
            unset($entries[$part]);
            $entries += [
                'docProps/core.xml' => '<?xml version="1.0"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"/>',
                'docProps/app.xml' => '<?xml version="1.0"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"/>',
                $part => $main,
            ];
        }
        $file = $this->zip('manual.'.$extension, $entries);
        // Exercise the server-detected Office MIME, not a fake client MIME.
        $this->assertSame($fixture === 'generic' ? 'application/octet-stream' : 'application/vnd.openxmlformats-officedocument.'.$type,
            (new \finfo(FILEINFO_MIME_TYPE))->file($file->getPathname()));

        return $file;
    }

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
        $files[] = $this->officeDocument('docx');
        $files[] = $this->officeDocument('pptx');
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

    public function test_ooxml_requires_real_xml_content_types_relationships_and_intact_crc(): void
    {
        foreach (['docx', 'pptx'] as $extension) {
            $valid = $this->officeDocument($extension, 'generic');
            $zip = new ZipArchive();
            $this->assertTrue($zip->open($valid->getPathname()));
            $entries = [];
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entries[$zip->getNameIndex($index)] = $zip->getFromIndex($index);
            }
            $zip->close();
            $part = $extension === 'docx' ? 'word/document.xml' : 'ppt/presentation.xml';
            $withoutRelationships = $entries;
            unset($withoutRelationships['_rels/.rels']);
            $variants = [
                'names-only' => ['[Content_Types].xml' => '<Types/>', $part => '<document/>'],
                'broken-main-xml' => array_replace($entries, [$part => '<broken']),
                'wrong-main-root' => array_replace($entries, [$part => '<document/>']),
                'wrong-content-type' => array_replace($entries, ['[Content_Types].xml' => str_replace('.main+xml', '.invalid+xml', $entries['[Content_Types].xml'])]),
                'missing-relationship' => $withoutRelationships,
                'external-relationship' => array_replace($entries, ['_rels/.rels' => str_replace('Target=', 'TargetMode="External" Target=', $entries['_rels/.rels'])]),
                'doctype' => array_replace($entries, [$part => str_replace('<?xml version="1.0"?>', '<!DOCTYPE document [<!ENTITY payload SYSTEM "file:///nonexistent-km-test">]>', $entries[$part])]),
            ];
            foreach ($variants as $name => $contents) {
                $validator = $this->validator(array_merge($this->validData(), [
                    'attachment' => $this->zip($name.'.'.$extension, $contents),
                ]));
                $this->assertTrue($validator->fails(), $name.' '.$extension);
                $this->assertArrayHasKey('attachment', $validator->errors()->toArray());
            }

            // Corrupt a stored entry while leaving it valid XML and preserving
            // its ZIP size/directory. Only checking names/XML misses this.
            $this->assertTrue($zip->open($valid->getPathname()));
            $zip->setCompressionName('docProps/app.xml', ZipArchive::CM_STORE);
            $zip->close();
            $bytes = file_get_contents($valid->getPathname());
            $position = strpos($bytes, 'extended-properties');
            $this->assertNotFalse($position);
            $bytes[$position] = 'E';
            $validator = $this->validator(array_merge($this->validData(), [
                'attachment' => $this->upload('bad-crc.'.$extension, $bytes),
            ]));
            $this->assertTrue($validator->fails(), 'CRC mismatch '.$extension);

            $spoof = $this->upload('spoofed.'.$extension, str_repeat("\0", 512));
            $claimedMime = 'application/vnd.openxmlformats-officedocument.'.($extension === 'docx' ? 'wordprocessingml.document' : 'presentationml.presentation');
            $spoof = new UploadedFile($spoof->getPathname(), $spoof->getClientOriginalName(), $claimedMime, null, true);
            $this->assertSame($claimedMime, $spoof->getClientMimeType());
            $this->assertSame('application/octet-stream', $spoof->getMimeType());
            $this->assertTrue($this->validator(array_merge($this->validData(), ['attachment' => $spoof]))->fails());
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
