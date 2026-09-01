<?php

namespace Tests\Feature;

use App\Models\KnowledgeItem;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class SecureKnowledgeItemFilesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_dry_run_reports_without_changing_files(): void
    {
        $path = 'knowledge-items/covers/legacy.png';
        $this->item(['cover_image' => $path]);
        Storage::disk('public')->put($path, 'legacy');

        $this->artisan('knowledge-items:secure-files')
            ->expectsOutputToContain('DRY RUN: found=1 moved=0')
            ->assertSuccessful();

        Storage::disk('public')->assertExists($path);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_execute_moves_referenced_managed_files_only(): void
    {
        $cover = 'knowledge-items/covers/legacy.png';
        $attachment = 'knowledge-items/attachments/legacy.pdf';
        $submission = 'submissions/source.pdf';
        $this->item(['cover_image' => $cover, 'attachment_path' => $attachment]);
        $this->item(['cover_image' => $submission]);
        Storage::disk('public')->put($cover, 'cover');
        Storage::disk('public')->put($attachment, 'attachment');
        Storage::disk('public')->put($submission, 'submission');

        $this->artisan('knowledge-items:secure-files', ['--execute' => true])
            ->assertSuccessful();

        Storage::disk('local')->assertExists($cover);
        Storage::disk('local')->assertExists($attachment);
        Storage::disk('public')->assertMissing($cover);
        Storage::disk('public')->assertMissing($attachment);
        Storage::disk('public')->assertExists($submission);
    }

    public function test_copy_failure_keeps_public_source(): void
    {
        $path = 'knowledge-items/covers/legacy.png';
        $this->item(['cover_image' => $path]);

        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, 'legacy');
        rewind($stream);

        $public = Mockery::mock(Filesystem::class);
        $public->shouldReceive('exists')->once()->with($path)->andReturnTrue();
        $public->shouldReceive('readStream')->once()->with($path)->andReturn($stream);
        $public->shouldNotReceive('delete');

        $private = Mockery::mock(Filesystem::class);
        $private->shouldReceive('exists')->once()->with($path)->andReturnFalse();
        $private->shouldReceive('writeStream')->once()->with($path, $stream)->andReturnFalse();

        $factory = Mockery::mock(FilesystemFactory::class);
        $factory->shouldReceive('disk')->with('public')->andReturn($public);
        $factory->shouldReceive('disk')->with('local')->andReturn($private);
        $this->app->instance(FilesystemFactory::class, $factory);
        $this->app->instance('filesystem', $factory);
        Storage::clearResolvedInstances();

        $this->artisan('knowledge-items:secure-files', ['--execute' => true])
            ->expectsOutputToContain('failed=1')
            ->assertFailed();
    }

    private function item(array $attributes): KnowledgeItem
    {
        return KnowledgeItem::create(array_merge([
            'title' => 'Knowledge '.uniqid(),
            'status' => 'draft',
        ], $attributes));
    }
}
