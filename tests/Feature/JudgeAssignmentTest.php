<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\Rubric;
use App\Models\Score;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JudgeAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_assign_valid_judge(): void
    {
        [$superAdmin, , $competition] = $this->context();
        $judge = $this->user('judge', 'Judge');

        $this->actingAs($superAdmin)->put(
            route('superadmin.competitions.judges.sync', $competition),
            ['judge_ids' => [$judge->id]]
        )->assertRedirect(route('superadmin.competitions.judges.index', $competition))
            ->assertSessionHas('success');

        $assignment = JudgeAssignment::sole();
        $this->assertSame($competition->id, $assignment->competition_id);
        $this->assertSame($judge->id, $assignment->judge_id);
        $this->assertSame('accepted', $assignment->assignment_status);
        $this->assertNotNull($assignment->assigned_at);
        $this->assertNotNull($assignment->accepted_at);
        $this->assertSame($judge->id, $assignment->judge->id);
    }

    public function test_invalid_user_and_non_judge_are_rejected(): void
    {
        [$superAdmin, $owner, $competition] = $this->context();

        $missing = $this->actingAs($superAdmin)->put(
            route('superadmin.competitions.judges.sync', $competition),
            ['judge_ids' => [999999]]
        );
        $missing->assertSessionHasErrors('judge_ids.0');
        $this->assertNotSame(500, $missing->getStatusCode());

        $nonJudge = $this->actingAs($superAdmin)->put(
            route('superadmin.competitions.judges.sync', $competition),
            ['judge_ids' => [$owner->id]]
        );
        $nonJudge->assertSessionHasErrors('judge_ids');
        $this->assertNotSame(500, $nonJudge->getStatusCode());
        $this->assertDatabaseCount('judge_assignments', 0);
    }

    public function test_assigning_same_judge_again_is_idempotent(): void
    {
        [$superAdmin, , $competition] = $this->context();
        $judge = $this->user('judge', 'Judge');

        foreach ([1, 2] as $attempt) {
            $response = $this->actingAs($superAdmin)->put(
                route('superadmin.competitions.judges.sync', $competition),
                ['judge_ids' => [$judge->id]]
            );
            $response->assertSessionHasNoErrors();
            $this->assertNotSame(500, $response->getStatusCode(), "attempt {$attempt}");
        }

        $this->assertDatabaseCount('judge_assignments', 1);
    }

    public function test_competition_admin_cannot_sync_or_remove_assignments(): void
    {
        [$superAdmin, $owner, $competition] = $this->context();
        $judge = $this->user('judge', 'Judge');
        $assignment = $this->assignment($competition, $judge, 'accepted');

        $sync = $this->actingAs($owner)->put(
            route('superadmin.competitions.judges.sync', $competition),
            ['judge_ids' => []]
        );
        $sync->assertForbidden();
        $this->assertNotSame(500, $sync->getStatusCode());

        $remove = $this->actingAs($owner)->delete(
            route('superadmin.competitions.judges.destroy', [$competition, $judge])
        );
        $remove->assertForbidden();
        $this->assertNotSame(500, $remove->getStatusCode());
        $this->assertDatabaseHas('judge_assignments', ['id' => $assignment->id]);
    }

    public function test_judge_can_accept_pending_assignment(): void
    {
        [, , $competition] = $this->context();
        $judge = $this->user('judge', 'Judge');
        $assignment = $this->assignment($competition, $judge, 'pending');

        $this->actingAs($judge)->post(route('judge.assignments.accept', $assignment))
            ->assertRedirect()->assertSessionHas('success');

        $assignment->refresh();
        $this->assertSame('accepted', $assignment->assignment_status);
        $this->assertNotNull($assignment->accepted_at);
        $this->assertNull($assignment->declined_at);
    }

    public function test_judge_can_decline_pending_assignment(): void
    {
        [, , $competition] = $this->context();
        $judge = $this->user('judge', 'Judge');
        $assignment = $this->assignment($competition, $judge, 'pending');

        $this->actingAs($judge)->post(route('judge.assignments.decline', $assignment))
            ->assertRedirect()->assertSessionHas('success');

        $assignment->refresh();
        $this->assertSame('declined', $assignment->assignment_status);
        $this->assertNull($assignment->accepted_at);
        $this->assertNotNull($assignment->declined_at);
    }

    public function test_unlocked_unscored_judge_can_be_removed(): void
    {
        [$superAdmin, , $competition] = $this->context();
        $judge = $this->user('judge', 'Judge');
        $assignment = $this->assignment($competition, $judge, 'accepted');

        $this->actingAs($superAdmin)->delete(
            route('superadmin.competitions.judges.destroy', [$competition, $judge])
        )->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('judge_assignments', ['id' => $assignment->id]);
    }

    public function test_judge_with_score_cannot_be_removed(): void
    {
        [$superAdmin, , $competition] = $this->context();
        $judge = $this->user('judge', 'Judge');
        $assignment = $this->assignment($competition, $judge, 'accepted');
        $this->scoreFor($competition, $assignment);

        $response = $this->actingAs($superAdmin)->delete(
            route('superadmin.competitions.judges.destroy', [$competition, $judge])
        );

        $response->assertSessionHasErrors('judge');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseHas('judge_assignments', ['id' => $assignment->id]);
        $this->assertDatabaseHas('scores', ['judge_assignment_id' => $assignment->id]);
    }

    public function test_started_judging_session_locks_assignment_changes(): void
    {
        [$superAdmin, $owner, $competition] = $this->context();
        $judge = $this->user('judge', 'Judge');
        $assignment = $this->assignment($competition, $judge, 'accepted');
        JudgingSession::create([
            'competition_id' => $competition->id,
            'controller_user_id' => $owner->id,
            'status' => 'live',
            'started_at' => now(),
        ]);

        $this->actingAs($superAdmin)->put(
            route('superadmin.competitions.judges.sync', $competition),
            ['judge_ids' => []]
        )->assertSessionHasErrors('judges');
        $this->actingAs($superAdmin)->delete(
            route('superadmin.competitions.judges.destroy', [$competition, $judge])
        )->assertSessionHasErrors('judges');

        $this->assertDatabaseHas('judge_assignments', ['id' => $assignment->id]);
    }

    public function test_only_accepted_assignments_are_counted_as_active_judges(): void
    {
        [, , $competition] = $this->context();
        $accepted = $this->user('accepted', 'Judge');
        $pending = $this->user('pending', 'Judge');
        $declined = $this->user('declined', 'Judge');
        $this->assignment($competition, $accepted, 'accepted');
        $this->assignment($competition, $pending, 'pending');
        $this->assignment($competition, $declined, 'declined');

        $acceptedAssignments = $competition->judgeAssignments()
            ->where('assignment_status', 'accepted')->get();

        $this->assertCount(1, $acceptedAssignments);
        $this->assertSame($accepted->id, $acceptedAssignments->sole()->judge_id);
        $this->assertSame(3, $competition->judgeAssignments()->count());
    }

    public function test_assignments_are_isolated_by_competition(): void
    {
        [, , $competitionA] = $this->context();
        [, , $competitionB] = $this->context();
        $judge = $this->user('judge', 'Judge');
        $assignmentA = $this->assignment($competitionA, $judge, 'accepted');

        $this->assertTrue($competitionA->judgeAssignments()->whereKey($assignmentA->id)->exists());
        $this->assertFalse($competitionB->judgeAssignments()->whereKey($assignmentA->id)->exists());
        $this->assertTrue($competitionA->judges()->whereKey($judge->id)->exists());
        $this->assertFalse($competitionB->judges()->whereKey($judge->id)->exists());
    }

    private function context(): array
    {
        $superAdmin = $this->user('super-admin', 'Super Admin');
        $owner = $this->user('competition-admin', 'Competition Admin');
        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $competition = Competition::create([
            'category_id' => $categoryId,
            'created_by' => $owner->id,
            'title' => 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subHour(),
            'registration_end' => now()->addHour(),
            'status' => 'open',
        ]);

        return [$superAdmin, $owner, $competition];
    }

    private function user(string $prefix, string $roleName): User
    {
        $roleId = DB::table('roles')->where('role_name', $roleName)->value('id');
        if (! $roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'role_name' => $roleName,
                'display_name' => $roleName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return User::create([
            'role_id' => $roleId,
            'username' => $prefix.'-'.uniqid(),
            'email' => uniqid().'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function assignment(
        Competition $competition,
        User $judge,
        string $status
    ): JudgeAssignment {
        return JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $judge->id,
            'assigned_at' => now(),
            'assignment_status' => $status,
            'accepted_at' => $status === 'accepted' ? now() : null,
            'declined_at' => $status === 'declined' ? now() : null,
        ]);
    }

    private function scoreFor(
        Competition $competition,
        JudgeAssignment $assignment
    ): Score {
        $submission = Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'Project',
            'contact_name' => 'Submitter',
            'contact_email' => 'submitter@example.com',
            'contact_phone' => '0800000000',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $rubric = Rubric::create([
            'competition_id' => $competition->id,
            'criteria_name' => 'Quality',
            'max_score' => 10,
            'weight' => 10,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return Score::create([
            'submission_id' => $submission->id,
            'rubric_id' => $rubric->id,
            'judge_assignment_id' => $assignment->id,
            'score' => 5,
            'submitted_at' => now(),
        ]);
    }
}
