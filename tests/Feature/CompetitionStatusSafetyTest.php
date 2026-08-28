<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionFormField;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CompetitionStatusSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Carbon::setTestNow(Carbon::parse('2026-08-28 12:00:00', 'Asia/Bangkok'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_workflow_statuses_are_never_overridden_by_registration_window(): void
    {
        foreach (['draft', 'published', 'closed', 'judging', 'completed', 'archived'] as $status) {
            $competition = $this->statusModel($status, now()->subHour(), now()->addHour());

            $this->assertSame($status, $competition->display_status, $status);
            $this->assertFalse($competition->isRegistrationOpen(), $status);
        }
    }

    public function test_open_status_before_start_is_upcoming_and_closed_status_stays_closed(): void
    {
        $open = $this->statusModel('open', now()->addSecond(), now()->addHour());
        $closed = $this->statusModel('closed', now()->addSecond(), now()->addHour());

        $this->assertSame('upcoming', $open->display_status);
        $this->assertFalse($open->isRegistrationOpen());
        $this->assertSame('closed', $closed->display_status);
    }

    public function test_open_status_is_open_at_start_and_during_window(): void
    {
        $atStart = $this->statusModel('open', now(), now()->addHour());
        $during = $this->statusModel('open', now()->subHour(), now()->addHour());

        $this->assertSame('open', $atStart->display_status);
        $this->assertTrue($atStart->isRegistrationOpen());
        $this->assertSame('open', $during->display_status);
        $this->assertTrue($during->isRegistrationOpen());
    }

    public function test_open_status_is_closed_at_and_after_end(): void
    {
        foreach ([now(), now()->subSecond()] as $end) {
            $competition = $this->statusModel('open', now()->subHour(), $end);
            $this->assertSame('closed', $competition->display_status);
            $this->assertFalse($competition->isRegistrationOpen());
        }
    }

    public function test_null_time_bounds_preserve_explicit_open_status(): void
    {
        foreach ([[null, null], [null, now()->addHour()], [now()->subHour(), null]] as [$start, $end]) {
            $competition = $this->statusModel('open', $start, $end);
            $this->assertSame('open', $competition->display_status);
            $this->assertTrue($competition->isRegistrationOpen());
        }

        $closed = $this->statusModel('closed', null, null);
        $this->assertSame('closed', $closed->display_status);
        $this->assertFalse($closed->isRegistrationOpen());
    }

    public function test_public_get_and_post_are_rejected_for_non_open_workflow_statuses(): void
    {
        foreach (['draft', 'closed', 'judging', 'completed', 'archived'] as $status) {
            [$competition] = $this->persistedCompetition($status);

            $this->get(route('competitions.submissions.create', $competition))->assertForbidden();
            $this->post(route('competitions.submissions.store', $competition), $this->validPayload($competition))->assertForbidden();
        }

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_public_get_and_post_follow_effective_open_status(): void
    {
        [$competition] = $this->persistedCompetition('open');

        $this->get(route('competitions.submissions.create', $competition))->assertOk();
        $this->post(route('competitions.submissions.store', $competition), $this->validPayload($competition))->assertRedirect();
        $this->assertDatabaseCount('submissions', 1);
    }

    public function test_public_get_and_post_are_rejected_at_registration_end(): void
    {
        [$competition] = $this->persistedCompetition('open', now()->subHour(), now());

        $this->get(route('competitions.submissions.create', $competition))->assertForbidden();
        $this->post(route('competitions.submissions.store', $competition), $this->validPayload($competition))->assertForbidden();
        $this->assertDatabaseCount('submissions', 0);
    }

    private function statusModel(string $status, mixed $start, mixed $end): Competition
    {
        return new Competition([
            'status' => $status,
            'registration_start' => $start,
            'registration_end' => $end,
        ]);
    }

    private function persistedCompetition(string $status, mixed $start = null, mixed $end = null): array
    {
        $roleId = DB::table('roles')->insertGetId([
            'role_name' => 'Admin '.uniqid(), 'display_name' => 'Admin',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin = User::create([
            'role_id' => $roleId, 'username' => 'admin-'.uniqid(),
            'email' => uniqid().'@example.com', 'password' => 'password', 'is_active' => true,
        ]);
        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(), 'category_slug' => 'category-'.uniqid(),
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $competition = Competition::create([
            'category_id' => $categoryId, 'created_by' => $admin->id,
            'title' => 'Competition '.uniqid(), 'competition_type' => 'individual',
            'visibility' => 'public', 'registration_start' => $start ?? now()->subHour(),
            'registration_end' => $end ?? now()->addHour(), 'status' => $status,
        ]);
        CompetitionFormField::create([
            'competition_id' => $competition->id, 'label' => 'Details',
            'field_name' => 'details', 'field_type' => 'text', 'is_required' => false,
            'sort_order' => 1, 'is_active' => true,
        ]);

        return [$competition];
    }

    private function validPayload(Competition $competition): array
    {
        return [
            'project_title' => 'Project', 'contact_name' => 'Person',
            'contact_email' => 'person@example.com', 'contact_phone' => '0800000000',
            'terms' => '1', 'website' => '',
            'form_guard_token' => Crypt::encryptString(json_encode([
                'competition_id' => $competition->id,
                'issued_at' => now()->subSeconds(2)->timestamp,
                'nonce' => bin2hex(random_bytes(20)),
            ], JSON_THROW_ON_ERROR)),
        ];
    }
}
