<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\Rubric;
use App\Models\Score;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JudgingSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_session_can_start(): void
    {
        $context = $this->context();

        $response = $this->actingAs($context['owner'])->post(
            route('competition-admin.competitions.judging-room.start', $context['competition'])
        );

        $response->assertRedirect()->assertSessionHas('success');
        $session = JudgingSession::sole();
        $this->assertSame('live', $session->status);
        $this->assertNotNull($session->started_at);
        $this->assertSame($context['submission']->id, $session->current_submission_id);
        $this->assertSame($context['owner']->id, $session->controller_user_id);
        $this->assertSame('under_review', $context['submission']->fresh()->status);
        $this->assertNotSame(500, $response->getStatusCode());
    }

    public function test_session_cannot_start_without_active_rubric(): void
    {
        $context = $this->context(['rubric_active' => false]);

        $response = $this->start($context);

        $response->assertSessionHas('error');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertSame('waiting', JudgingSession::sole()->status);
    }

    public function test_session_cannot_start_without_accepted_judge(): void
    {
        $context = $this->context(['assignment_status' => 'pending']);

        $response = $this->start($context);

        $response->assertSessionHas('error');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertSame('waiting', JudgingSession::sole()->status);
    }

    public function test_session_cannot_start_without_eligible_submission(): void
    {
        $context = $this->context(['submission_status' => 'disqualified']);

        $response = $this->start($context);

        $response->assertSessionHas('error');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertSame('waiting', JudgingSession::sole()->status);
    }

    public function test_only_waiting_session_can_start_and_invalid_restart_preserves_end_time(): void
    {
        foreach (['live', 'paused', 'ended', 'closed'] as $status) {
            $context = $this->context();
            $endedAt = in_array($status, ['ended', 'closed'], true)
                ? now()->subMinute()
                : null;
            $context['session']->update([
                'status' => $status,
                'started_at' => now()->subHour(),
                'ended_at' => $endedAt,
            ]);

            $this->flushSession();
            $this->start($context)->assertSessionHas('error');

            $session = $context['session']->fresh();
            $this->assertSame($status, $session->status);
            if ($endedAt) {
                $this->assertSame(
                    $endedAt->format('Y-m-d H:i:s'),
                    $session->ended_at->format('Y-m-d H:i:s')
                );
            }
        }
    }

    public function test_submission_selection_is_allowed_only_while_live_or_paused(): void
    {
        foreach (['waiting', 'live', 'paused', 'ended', 'closed'] as $status) {
            $context = $this->context();
            $nextSubmission = $this->submission($context['competition'], 'submitted');
            $context['session']->update([
                'status' => $status,
                'started_at' => $status === 'waiting' ? null : now(),
            ]);
            $originalVersion = (int) $context['session']->fresh()->state_version;

            $this->flushSession();
            $response = $this->actingAs($context['owner'])->put(
                route(
                    'competition-admin.competitions.judging-room.submission',
                    $context['competition']
                ),
                ['submission_id' => $nextSubmission->id]
            );

            if (in_array($status, ['live', 'paused'], true)) {
                $response->assertSessionHas('success');
                $this->assertSame(
                    $nextSubmission->id,
                    $context['session']->fresh()->current_submission_id
                );
                $this->assertSame('under_review', $nextSubmission->fresh()->status);
                $this->assertSame($originalVersion + 1, $context['session']->fresh()->state_version);
            } else {
                $response->assertSessionHas('error');
                $this->assertNull($context['session']->fresh()->current_submission_id);
                $this->assertSame('submitted', $nextSubmission->fresh()->status);
                $this->assertSame($originalVersion, $context['session']->fresh()->state_version);
            }
        }
    }

    public function test_owner_can_update_presentation_state_during_live_and_paused(): void
    {
        foreach (['live', 'paused'] as $status) {
            $context = $this->context();
            $file = $this->submissionFile($context['submission']);
            $context['session']->update([
                'status' => $status,
                'started_at' => now(),
                'current_submission_id' => $context['submission']->id,
                'current_file_id' => null,
                'state_version' => 4,
            ]);

            $this->flushSession();
            $this->actingAs($context['owner'])->putJson(
                route('competition-admin.competitions.judging-room.state', $context['competition']),
                [
                    'current_file_id' => $file->id,
                    'current_page' => 3,
                    'scroll_progress' => 0.625,
                    'zoom' => 1.75,
                ]
            )->assertOk()->assertJson([
                'current_file_id' => $file->id,
                'current_page' => 3,
                'scroll_progress' => 0.625,
                'zoom' => 1.75,
                'state_version' => 5,
            ]);

            $session = $context['session']->fresh();
            $this->assertSame($file->id, $session->current_file_id);
            $this->assertSame(3, $session->current_page);
            $this->assertSame(5, $session->state_version);
        }
    }

    public function test_presentation_state_update_rejects_invalid_states_without_changes(): void
    {
        foreach (['waiting', 'ended', 'closed'] as $status) {
            $context = $this->context();
            $file = $this->submissionFile($context['submission']);
            $context['session']->update([
                'status' => $status,
                'started_at' => $status === 'waiting' ? null : now(),
                'current_submission_id' => $context['submission']->id,
                'current_file_id' => $file->id,
                'current_page' => 2,
                'state_version' => 7,
            ]);

            $this->flushSession();
            $this->actingAs($context['owner'])->putJson(
                route('competition-admin.competitions.judging-room.state', $context['competition']),
                [
                    'current_file_id' => null,
                    'current_page' => 9,
                    'scroll_progress' => 0.9,
                    'zoom' => 2,
                ]
            )->assertUnprocessable()->assertJsonValidationErrors('state');

            $session = $context['session']->fresh();
            $this->assertSame($file->id, $session->current_file_id);
            $this->assertSame(2, $session->current_page);
            $this->assertSame(7, $session->state_version);
        }
    }

    public function test_presentation_state_rejects_other_admin_and_unrelated_files(): void
    {
        $context = $this->context();
        $otherContext = $this->context();
        $otherAdmin = $this->user('other-admin', 'Competition Admin');
        $sameCompetitionOtherSubmission = $this->submission($context['competition'], 'submitted');
        $sameCompetitionOtherFile = $this->submissionFile($sameCompetitionOtherSubmission);
        $otherCompetitionFile = $this->submissionFile($otherContext['submission']);
        $context['session']->update([
            'status' => 'live',
            'started_at' => now(),
            'current_submission_id' => $context['submission']->id,
            'state_version' => 2,
        ]);
        $payload = [
            'current_page' => 1,
            'scroll_progress' => 0,
            'zoom' => 1,
        ];

        $this->actingAs($otherAdmin)->putJson(
            route('competition-admin.competitions.judging-room.state', $context['competition']),
            $payload + ['current_file_id' => null]
        )->assertForbidden();

        foreach ([$sameCompetitionOtherFile, $otherCompetitionFile] as $file) {
            $this->flushSession();
            $this->actingAs($context['owner'])->putJson(
                route('competition-admin.competitions.judging-room.state', $context['competition']),
                $payload + ['current_file_id' => $file->id]
            )->assertUnprocessable();
        }

        $this->assertNull($context['session']->fresh()->current_file_id);
        $this->assertSame(2, $context['session']->fresh()->state_version);
    }

    public function test_judge_state_endpoint_returns_updated_presentation_state(): void
    {
        $context = $this->context();
        $file = $this->submissionFile($context['submission']);
        $context['session']->update([
            'status' => 'live',
            'started_at' => now(),
            'current_submission_id' => $context['submission']->id,
            'current_file_id' => $file->id,
            'current_page' => 4,
            'scroll_progress' => 0.5,
            'zoom' => 2,
            'state_version' => 8,
        ]);

        $this->actingAs($context['judge'])->getJson(
            route('judge.judging-rooms.state', $context['session'])
        )->assertOk()->assertJson([
            'current_submission_id' => $context['submission']->id,
            'current_file_id' => $file->id,
            'current_page' => 4,
            'scroll_progress' => 0.5,
            'zoom' => 2,
            'state_version' => 8,
        ]);
    }

    public function test_live_session_can_pause_without_deleting_judging_data(): void
    {
        $context = $this->context();
        $this->start($context)->assertSessionHas('success');

        $response = $this->actingAs($context['owner'])->post(
            route('competition-admin.competitions.judging-room.pause', $context['competition'])
        );

        $response->assertSessionHas('success');
        $session = JudgingSession::sole();
        $this->assertSame('paused', $session->status);
        $this->assertSame($context['submission']->id, $session->current_submission_id);
        $this->assertDatabaseHas('submissions', ['id' => $context['submission']->id]);
        $this->assertDatabaseHas('rubrics', ['id' => $context['rubric']->id]);
        $this->assertDatabaseHas('judge_assignments', ['id' => $context['assignment']->id]);
    }

    public function test_paused_session_can_resume_without_duplicate_or_state_loss(): void
    {
        $context = $this->context();
        $this->start($context);
        $session = JudgingSession::sole();
        $this->actingAs($context['owner'])->post(
            route('competition-admin.competitions.judging-room.pause', $context['competition'])
        );
        $session->refresh()->update(['current_page' => 3, 'scroll_progress' => 0.4, 'zoom' => 1.5]);

        $this->actingAs($context['owner'])->post(
            route('competition-admin.competitions.judging-room.resume', $context['competition'])
        )->assertSessionHas('success');

        $session->refresh();
        $this->assertSame('live', $session->status);
        $this->assertSame(3, $session->current_page);
        $this->assertSame('0.40000', $session->scroll_progress);
        $this->assertSame('1.50', $session->zoom);
        $this->assertSame($context['submission']->id, $session->current_submission_id);
        $this->assertDatabaseCount('judging_sessions', 1);
    }

    public function test_waiting_session_cannot_be_paused(): void
    {
        $context = $this->context();

        $response = $this->actingAs($context['owner'])->post(
            route('competition-admin.competitions.judging-room.pause', $context['competition'])
        );

        $response->assertSessionHas('error');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertSame('waiting', JudgingSession::sole()->status);
    }

    public function test_session_cannot_end_with_incomplete_scores(): void
    {
        $context = $this->context();
        $this->start($context);

        $response = $this->end($context);

        $response->assertSessionHas('error');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertSame('live', JudgingSession::sole()->status);
        $this->assertDatabaseCount('scores', 0);
    }

    public function test_session_can_end_with_complete_scores_and_preserves_all_data(): void
    {
        $context = $this->context();
        $this->start($context);
        $score = $this->score($context, submitted: true);

        $response = $this->end($context);

        $response->assertSessionHas('success');
        $session = JudgingSession::sole();
        $this->assertSame('ended', $session->status);
        $this->assertNotNull($session->ended_at);
        $this->assertDatabaseHas('scores', ['id' => $score->id]);
        $this->assertDatabaseHas('submissions', ['id' => $context['submission']->id]);
        $this->assertDatabaseHas('judge_assignments', ['id' => $context['assignment']->id]);
        $this->assertDatabaseHas('rubrics', ['id' => $context['rubric']->id]);
    }

    public function test_draft_score_does_not_count_as_complete(): void
    {
        $context = $this->context();
        $this->start($context);
        $score = $this->score($context, submitted: false);

        $this->end($context)->assertSessionHas('error');

        $this->assertSame('live', JudgingSession::sole()->status);
        $this->assertDatabaseHas('scores', ['id' => $score->id, 'submitted_at' => null]);
    }

    public function test_inactive_rubric_does_not_block_end(): void
    {
        $context = $this->context();
        $inactive = Rubric::create([
            'competition_id' => $context['competition']->id,
            'criteria_name' => 'Inactive',
            'max_score' => 50,
            'weight' => 50,
            'sort_order' => 2,
            'is_active' => false,
        ]);
        $this->start($context);
        $this->score($context, submitted: true);

        $this->end($context)->assertSessionHas('success');

        $this->assertSame('ended', JudgingSession::sole()->status);
        $this->assertDatabaseMissing('scores', ['rubric_id' => $inactive->id]);
    }

    public function test_pending_and_declined_judges_do_not_block_end(): void
    {
        $context = $this->context();
        $pending = $this->assignment($context['competition'], $this->user('pending', 'Judge'), 'pending');
        $declined = $this->assignment($context['competition'], $this->user('declined', 'Judge'), 'declined');
        $this->start($context);
        $this->score($context, submitted: true);

        $this->end($context)->assertSessionHas('success');

        $this->assertSame('ended', JudgingSession::sole()->status);
        $this->assertDatabaseMissing('scores', ['judge_assignment_id' => $pending->id]);
        $this->assertDatabaseMissing('scores', ['judge_assignment_id' => $declined->id]);
    }

    public function test_disqualified_submission_does_not_block_end(): void
    {
        $context = $this->context();
        $disqualified = $this->submission($context['competition'], 'disqualified');
        $this->start($context);
        $this->score($context, submitted: true);

        $this->end($context)->assertSessionHas('success');

        $this->assertSame('ended', JudgingSession::sole()->status);
        $this->assertDatabaseMissing('scores', ['submission_id' => $disqualified->id]);
    }

    public function test_non_owner_cannot_start_pause_or_end_session(): void
    {
        $context = $this->context();
        $otherAdmin = $this->user('other-admin', 'Competition Admin');

        foreach (['start', 'pause', 'end'] as $action) {
            $response = $this->actingAs($otherAdmin)->post(route(
                "competition-admin.competitions.judging-room.{$action}",
                $context['competition']
            ));
            $response->assertForbidden();
            $this->assertNotSame(500, $response->getStatusCode(), $action);
        }

        $this->assertSame('waiting', JudgingSession::sole()->status);
    }

    private function start(array $context)
    {
        return $this->actingAs($context['owner'])->post(
            route('competition-admin.competitions.judging-room.start', $context['competition'])
        );
    }

    private function end(array $context)
    {
        return $this->actingAs($context['owner'])->post(
            route('competition-admin.competitions.judging-room.end', $context['competition'])
        );
    }

    private function context(array $overrides = []): array
    {
        $owner = $this->user('owner', 'Competition Admin');
        $judge = $this->user('judge', 'Judge');
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
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'status' => 'open',
        ]);
        $rubric = Rubric::create([
            'competition_id' => $competition->id,
            'criteria_name' => 'Overall Quality',
            'max_score' => 100,
            'weight' => 100,
            'sort_order' => 1,
            'is_active' => $overrides['rubric_active'] ?? true,
        ]);
        $assignment = $this->assignment(
            $competition,
            $judge,
            $overrides['assignment_status'] ?? 'accepted'
        );
        $submission = $this->submission(
            $competition,
            $overrides['submission_status'] ?? 'submitted'
        );
        $session = JudgingSession::create([
            'competition_id' => $competition->id,
            'controller_user_id' => $owner->id,
            'status' => 'waiting',
        ]);

        return compact('owner', 'judge', 'competition', 'rubric', 'assignment', 'submission', 'session');
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

    private function assignment(Competition $competition, User $judge, string $status): JudgeAssignment
    {
        return JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $judge->id,
            'assignment_status' => $status,
            'accepted_at' => $status === 'accepted' ? now() : null,
            'declined_at' => $status === 'declined' ? now() : null,
        ]);
    }

    private function submission(Competition $competition, string $status): Submission
    {
        return Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'Project '.uniqid(),
            'contact_name' => 'Submitter',
            'contact_email' => 'submitter@example.com',
            'contact_phone' => '0800000000',
            'status' => $status,
            'submitted_at' => now(),
        ]);
    }

    private function submissionFile(Submission $submission): SubmissionFile
    {
        return SubmissionFile::create([
            'submission_id' => $submission->id,
            'original_name' => 'presentation.pdf',
            'stored_name' => uniqid().'.pdf',
            'file_path' => "submissions/{$submission->competition_id}/{$submission->submission_code}/presentation.pdf",
            'file_extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'is_primary' => true,
        ]);
    }

    private function score(array $context, bool $submitted): Score
    {
        return Score::create([
            'submission_id' => $context['submission']->id,
            'rubric_id' => $context['rubric']->id,
            'judge_assignment_id' => $context['assignment']->id,
            'score' => 80,
            'submitted_at' => $submitted ? now() : null,
        ]);
    }
}
