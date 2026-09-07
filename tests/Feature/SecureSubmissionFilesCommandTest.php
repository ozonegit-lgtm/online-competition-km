<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class SecureSubmissionFilesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_dry_run_does_not_change_files(): void
    {
        $path = 'submissions/1/SUB-1/legacy.pdf';
        $this->file($path);
        Storage::disk('public')->put($path, 'legacy');

        $this->artisan('submissions:secure-files')
            ->expectsOutputToContain('DRY RUN: found=1 moved=0')
            ->assertSuccessful();

        Storage::disk('public')->assertExists($path);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_execute_moves_only_referenced_submission_paths(): void
    {
        $submission = 'submissions/1/SUB-1/legacy.pdf';
        $knowledge = 'knowledge-items/attachments/guide.pdf';
        $template = 'templates/cover.png';
        $this->file($submission);
        $this->file($knowledge);
        foreach ([$submission, $knowledge, $template] as $path) {
            Storage::disk('public')->put($path, $path);
        }

        $this->artisan('submissions:secure-files', ['--execute' => true])
            ->assertSuccessful();

        Storage::disk('local')->assertExists($submission);
        Storage::disk('public')->assertMissing($submission);
        Storage::disk('public')->assertExists($knowledge);
        Storage::disk('public')->assertExists($template);
    }

    public function test_copy_failure_keeps_public_source(): void
    {
        $path = 'submissions/1/SUB-1/legacy.pdf';
        $this->file($path);
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

        $this->artisan('submissions:secure-files', ['--execute' => true])
            ->expectsOutputToContain('failed=1')
            ->assertFailed();
    }

    private function file(string $path): SubmissionFile
    {
        $roleId = DB::table('roles')->insertGetId([
            'role_name' => 'Admin '.uniqid(), 'display_name' => 'Admin',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $user = User::create([
            'role_id' => $roleId, 'username' => 'admin-'.uniqid(),
            'email' => uniqid().'@example.com', 'password' => 'password', 'is_active' => true,
        ]);
        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(), 'category_slug' => 'category-'.uniqid(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $competition = Competition::create([
            'category_id' => $categoryId, 'created_by' => $user->id,
            'title' => 'Competition '.uniqid(), 'competition_type' => 'individual',
            'visibility' => 'public', 'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(), 'status' => 'open',
        ]);
        $submission = Submission::create([
            'competition_id' => $competition->id, 'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'Project', 'contact_name' => 'Person',
            'contact_email' => 'person@example.com', 'contact_phone' => '0800000000',
            'status' => 'submitted', 'submitted_at' => now(),
        ]);

        return SubmissionFile::create([
            'submission_id' => $submission->id, 'original_name' => 'legacy.pdf',
            'stored_name' => 'legacy.pdf', 'file_path' => $path, 'file_extension' => 'pdf',
            'mime_type' => 'application/pdf', 'file_size' => 6, 'is_primary' => true,
        ]);
    }
}
