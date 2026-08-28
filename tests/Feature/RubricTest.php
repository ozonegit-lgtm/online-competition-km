<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\JudgingSession;
use App\Models\JudgeAssignment;
use App\Models\Rubric;
use App\Models\Score;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RubricTest extends TestCase
{
    use RefreshDatabase;

    public function test_competition_admin_can_create_valid_rubric(): void
    {
        [$admin, $competition] = $this->context('admin');

        $this->actingAs($admin)->post(
            route('competition-admin.competitions.rubrics.store', $competition),
            $this->payload(['max_score' => 25, 'is_active' => true])
        )->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('rubrics', [
            'competition_id' => $competition->id,
            'criteria_name' => 'Presentation Quality',
            'max_score' => 25,
            'weight' => 25,
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_required_rubric_fields_are_rejected(): void
    {
        [$admin, $competition] = $this->context('admin');

        $response = $this->actingAs($admin)->post(
            route('competition-admin.competitions.rubrics.store', $competition),
            ['criteria_name' => '', 'max_score' => '']
        );

        $response->assertSessionHasErrors(['criteria_name', 'max_score']);
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('rubrics', 0);
    }

    public function test_max_score_outside_one_to_one_hundred_is_rejected(): void
    {
        [$admin, $competition] = $this->context('admin');

        foreach ([-1, 0, 101] as $maxScore) {
            $response = $this->actingAs($admin)->post(
                route('competition-admin.competitions.rubrics.store', $competition),
                $this->payload(['max_score' => $maxScore])
            );

            $response->assertSessionHasErrors('max_score');
            $this->assertNotSame(500, $response->getStatusCode());
        }

        $this->assertDatabaseCount('rubrics', 0);
    }

    public function test_active_score_total_accepts_one_hundred_and_rejects_more(): void
    {
        [$admin, $competition] = $this->context('admin');
        $this->rubric($competition, ['criteria_name' => 'Existing', 'max_score' => 60, 'weight' => 60]);

        $this->actingAs($admin)->post(
            route('competition-admin.competitions.rubrics.store', $competition),
            $this->payload(['criteria_name' => 'Boundary', 'max_score' => 40, 'is_active' => true])
        )->assertSessionHasNoErrors();

        $response = $this->actingAs($admin)->post(
            route('competition-admin.competitions.rubrics.store', $competition),
            $this->payload(['criteria_name' => 'Over Limit', 'max_score' => 1, 'is_active' => true])
        );
        $response->assertSessionHasErrors('max_score');
        $this->assertDatabaseMissing('rubrics', ['criteria_name' => 'Over Limit']);
    }

    public function test_competition_admin_can_update_own_rubric(): void
    {
        [$admin, $competition] = $this->context('admin');
        $rubric = $this->rubric($competition);

        $this->actingAs($admin)->put(
            route('competition-admin.competitions.rubrics.update', [$competition, $rubric]),
            $this->payload([
                'criteria_name' => 'Updated Criteria',
                'description' => 'Updated description',
                'max_score' => 35,
                'is_active' => false,
            ])
        )->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('rubrics', [
            'id' => $rubric->id,
            'competition_id' => $competition->id,
            'criteria_name' => 'Updated Criteria',
            'description' => 'Updated description',
            'max_score' => 35,
            'weight' => 35,
            'sort_order' => 1,
            'is_active' => false,
        ]);
    }

    public function test_other_admin_cannot_update_or_delete_rubric(): void
    {
        [$owner, $competition] = $this->context('owner');
        $otherAdmin = $this->user('other-admin');
        $rubric = $this->rubric($competition);

        $update = $this->actingAs($otherAdmin)->put(
            route('competition-admin.competitions.rubrics.update', [$competition, $rubric]),
            $this->payload(['criteria_name' => 'Unauthorized Change'])
        );
        $update->assertForbidden();
        $this->assertNotSame(500, $update->getStatusCode());

        $delete = $this->actingAs($otherAdmin)->delete(
            route('competition-admin.competitions.rubrics.destroy', [$competition, $rubric])
        );
        $delete->assertForbidden();
        $this->assertNotSame(500, $delete->getStatusCode());

        $this->assertDatabaseHas('rubrics', [
            'id' => $rubric->id,
            'criteria_name' => 'Existing Criteria',
        ]);
    }

    public function test_inactive_rubric_is_persisted_and_excluded_from_active_total(): void
    {
        [$admin, $competition] = $this->context('admin');

        $this->actingAs($admin)->post(
            route('competition-admin.competitions.rubrics.store', $competition),
            $this->payload(['criteria_name' => 'Inactive', 'max_score' => 100, 'is_active' => false])
        )->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(
            route('competition-admin.competitions.rubrics.store', $competition),
            $this->payload(['criteria_name' => 'Active', 'max_score' => 100, 'is_active' => true])
        )->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rubrics', ['criteria_name' => 'Inactive', 'is_active' => false]);
        $this->assertDatabaseHas('rubrics', ['criteria_name' => 'Active', 'is_active' => true]);
    }

    public function test_sort_order_is_assigned_and_indexed_in_ascending_order(): void
    {
        [$admin, $competition] = $this->context('admin');
        $second = $this->rubric($competition, ['criteria_name' => 'Second', 'sort_order' => 2]);
        $first = $this->rubric($competition, ['criteria_name' => 'First', 'sort_order' => 1]);

        $this->actingAs($admin)
            ->get(route('competition-admin.competitions.rubrics.index', $competition))
            ->assertOk()
            ->assertViewHas('rubrics', function ($rubrics) use ($first, $second) {
                return $rubrics->modelKeys() === [$first->id, $second->id];
            });

        $this->actingAs($admin)->post(
            route('competition-admin.competitions.rubrics.store', $competition),
            $this->payload(['criteria_name' => 'Third', 'max_score' => 10])
        )->assertSessionHasNoErrors();
        $this->assertDatabaseHas('rubrics', ['criteria_name' => 'Third', 'sort_order' => 3]);
    }

    public function test_started_judging_session_locks_create_update_and_delete(): void
    {
        [$admin, $competition] = $this->context('admin');
        $rubric = $this->rubric($competition);
        JudgingSession::create([
            'competition_id' => $competition->id,
            'controller_user_id' => $admin->id,
            'status' => 'live',
            'started_at' => now(),
        ]);

        $this->actingAs($admin)->post(
            route('competition-admin.competitions.rubrics.store', $competition),
            $this->payload(['criteria_name' => 'Locked Create'])
        )->assertSessionHasErrors('rubric');
        $this->actingAs($admin)->put(
            route('competition-admin.competitions.rubrics.update', [$competition, $rubric]),
            $this->payload(['criteria_name' => 'Locked Update'])
        )->assertSessionHasErrors('rubric');
        $this->actingAs($admin)->delete(
            route('competition-admin.competitions.rubrics.destroy', [$competition, $rubric])
        )->assertSessionHasErrors('rubric');

        $this->assertDatabaseCount('rubrics', 1);
        $this->assertDatabaseHas('rubrics', [
            'id' => $rubric->id,
            'criteria_name' => 'Existing Criteria',
        ]);
    }

    public function test_unscored_unlocked_rubric_can_be_deleted(): void
    {
        [$admin, $competition] = $this->context('admin');
        $rubric = $this->rubric($competition);

        $this->actingAs($admin)->delete(
            route('competition-admin.competitions.rubrics.destroy', [$competition, $rubric])
        )->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('rubrics', ['id' => $rubric->id]);
    }

    public function test_rubric_with_score_cannot_be_deleted(): void
    {
        [$admin, $competition] = $this->context('admin');
        $rubric = $this->rubric($competition);
        $judge = $this->user('judge', 'Judge');
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
        $assignment = JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $judge->id,
            'assignment_status' => 'accepted',
            'accepted_at' => now(),
        ]);
        Score::create([
            'submission_id' => $submission->id,
            'rubric_id' => $rubric->id,
            'judge_assignment_id' => $assignment->id,
            'score' => 5,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(
            route('competition-admin.competitions.rubrics.destroy', [$competition, $rubric])
        );

        $response->assertSessionHasErrors('rubric');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseHas('rubrics', ['id' => $rubric->id]);
        $this->assertDatabaseHas('scores', ['rubric_id' => $rubric->id]);
    }

    private function context(string $username): array
    {
        $admin = $this->user($username);
        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $competition = Competition::create([
            'category_id' => $categoryId,
            'created_by' => $admin->id,
            'title' => 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subHour(),
            'registration_end' => now()->addHour(),
            'status' => 'open',
        ]);

        return [$admin, $competition];
    }

    private function user(string $username, string $roleName = 'Competition Admin'): User
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
            'username' => $username.'-'.uniqid(),
            'email' => uniqid().'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function rubric(Competition $competition, array $overrides = []): Rubric
    {
        return Rubric::create(array_replace([
            'competition_id' => $competition->id,
            'criteria_name' => 'Existing Criteria',
            'description' => 'Existing description',
            'max_score' => 10,
            'weight' => 10,
            'sort_order' => 1,
            'is_active' => true,
        ], $overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'criteria_name' => 'Presentation Quality',
            'description' => 'Evaluate presentation quality',
            'max_score' => 20,
            'is_active' => true,
        ], $overrides);
    }
}
