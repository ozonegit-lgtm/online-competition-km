<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionFormField;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\KnowledgeItem;
use App\Models\Rubric;
use App\Models\Score;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KnowledgeManagementPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_eligible_submission_appears_in_km_candidate_list(): void
    {
        $context = $this->context();

        $this->actingAs($context['owner'])->get(route('competition-admin.km.index', ['source' => 'competition']))
            ->assertOk()
            ->assertViewHas('submissions', fn ($items) => $items->contains('id', $context['submission']->id));
    }

    public function test_disqualified_submission_is_excluded_from_km_candidates(): void
    {
        $context = $this->context(['submission_status' => 'disqualified']);

        $this->actingAs($context['owner'])->get(route('competition-admin.km.index', ['source' => 'competition']))
            ->assertOk()
            ->assertViewHas('submissions', fn ($items) => ! $items->contains('id', $context['submission']->id));
    }

    public function test_owner_can_publish_eligible_submission_to_km(): void
    {
        $context = $this->context();

        $this->publish($context)->assertRedirect()->assertSessionHas('success');

        $item = KnowledgeItem::sole();
        $this->assertSame($context['submission']->id, $item->submission_id);
        $this->assertSame($context['submission']->project_title, $item->title);
        $this->assertSame('published', $item->status);
        $this->assertNotNull($item->published_at);
        $this->get(route('home'))->assertOk()
            ->assertViewHas('knowledgeItems', fn ($items) => $items->contains('id', $item->id));
    }

    public function test_publishing_same_submission_is_idempotent(): void
    {
        $context = $this->context();

        foreach ([1, 2] as $attempt) {
            $response = $this->publish($context);
            $response->assertSessionHas('success');
            $this->assertNotSame(500, $response->getStatusCode(), "attempt {$attempt}");
        }

        $this->assertDatabaseCount('knowledge_items', 1);
        $this->assertSame('published', KnowledgeItem::sole()->status);
    }

    public function test_unpublish_hides_item_and_preserves_judging_data(): void
    {
        $context = $this->context();
        $this->publish($context);
        $item = KnowledgeItem::sole();

        $this->actingAs($context['owner'])->delete(
            route('competition-admin.submissions.km.unpublish', $context['submission'])
        )->assertRedirect()->assertSessionHas('success');

        $item->refresh();
        $this->assertSame('draft', $item->status);
        $this->assertNull($item->published_at);
        $this->get(route('home'))->assertViewHas(
            'knowledgeItems',
            fn ($items) => ! $items->contains('id', $item->id)
        );
        $this->assertDatabaseHas('submissions', ['id' => $context['submission']->id]);
        $this->assertDatabaseHas('scores', ['id' => $context['score']->id]);
        $this->assertDatabaseHas('judge_assignments', ['id' => $context['assignment']->id]);
        $this->assertDatabaseHas('judging_sessions', ['id' => $context['session']->id]);
    }

    public function test_km_publication_does_not_change_result_publication_flag(): void
    {
        $context = $this->context();
        $context['competition']->update(['publish_scores' => false]);

        $this->publish($context);
        $this->assertFalse($context['competition']->fresh()->publish_scores);
        $this->actingAs($context['owner'])->delete(
            route('competition-admin.submissions.km.unpublish', $context['submission'])
        );
        $this->assertFalse($context['competition']->fresh()->publish_scores);
    }

    public function test_result_unpublish_does_not_destroy_published_km_item(): void
    {
        $context = $this->context();
        $context['competition']->update(['publish_scores' => true]);
        $this->publish($context);
        $item = KnowledgeItem::sole();

        $this->actingAs($context['owner'])->delete(
            route('competition-admin.competitions.results.unpublish', $context['competition'])
        )->assertRedirect();

        $this->assertFalse($context['competition']->fresh()->publish_scores);
        $this->assertSame('published', $item->fresh()->status);
    }

    public function test_other_admin_cannot_publish_submission(): void
    {
        $context = $this->context();
        $otherAdmin = $this->user('other-admin', 'Competition Admin');

        $response = $this->actingAs($otherAdmin)->post(
            route('competition-admin.submissions.km.publish', $context['submission'])
        );

        $response->assertForbidden();
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('knowledge_items', 0);
    }

    public function test_other_admin_cannot_unpublish_item(): void
    {
        $context = $this->context();
        $this->publish($context);
        $item = KnowledgeItem::sole();
        $otherAdmin = $this->user('other-admin', 'Competition Admin');

        $this->flushSession();
        auth()->forgetGuards();
        $response = $this->actingAs($otherAdmin)->delete(
            route('competition-admin.submissions.km.unpublish', $context['submission'])
        );

        $response->assertForbidden();
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertSame('published', $item->fresh()->status);
    }

    public function test_public_km_only_shows_published_non_disqualified_items(): void
    {
        $published = $this->context();
        $this->publish($published);
        $draft = $this->context();
        $draftItem = KnowledgeItem::create([
            'submission_id' => $draft['submission']->id,
            'title' => 'Hidden Draft',
            'status' => 'draft',
        ]);
        $disqualified = $this->context();
        $disqualifiedItem = KnowledgeItem::create([
            'submission_id' => $disqualified['submission']->id,
            'title' => 'Hidden Disqualified',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $disqualified['submission']->update(['status' => 'disqualified']);

        $response = $this->get(route('home'));
        $response->assertOk()->assertViewHas('knowledgeItems', function ($items) use (
            $published,
            $draftItem,
            $disqualifiedItem
        ) {
            return $items->contains('submission_id', $published['submission']->id)
                && ! $items->contains('id', $draftItem->id)
                && ! $items->contains('id', $disqualifiedItem->id);
        });
    }

    public function test_search_and_category_filters_do_not_reveal_hidden_items(): void
    {
        $context = $this->context();
        $hidden = KnowledgeItem::create([
            'submission_id' => $context['submission']->id,
            'title' => 'Secret Search Target',
            'status' => 'draft',
        ]);

        $this->get(route('home', [
            'search' => 'Secret Search Target',
            'category' => $context['competition']->category_id,
        ]))->assertOk()->assertViewHas(
            'knowledgeItems',
            fn ($items) => ! $items->contains('id', $hidden->id)
        );
    }

    public function test_ineligible_submissions_cannot_be_published_safely(): void
    {
        $unfinished = $this->context(['session_status' => 'live']);
        $response = $this->publish($unfinished);
        $response->assertStatus(422);
        $this->assertNotSame(500, $response->getStatusCode());

        $incomplete = $this->context(['with_score' => false]);
        $response = $this->publish($incomplete);
        $response->assertStatus(422);
        $this->assertNotSame(500, $response->getStatusCode());

        $disqualified = $this->context(['submission_status' => 'disqualified']);
        $response = $this->publish($disqualified);
        $response->assertStatus(422);
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('knowledge_items', 0);
    }

    public function test_published_manual_km_appears_in_list_featured_search_and_category(): void
    {
        $context = $this->context();
        $item = KnowledgeItem::create([
            'category_id' => $context['competition']->category_id,
            'created_by' => $context['owner']->id,
            'title' => 'Manual public knowledge',
            'cover_image' => 'knowledge-items/covers/manual.png',
            'status' => 'published',
            'is_featured' => true,
            'published_at' => now(),
        ]);
        foreach ([[], ['search' => 'Manual public'], ['category' => $item->category_id]] as $filters) {
            $this->get(route('home', $filters))->assertOk()
                ->assertViewHas('knowledgeItems', fn ($items) => $items->contains('id', $item->id))
                ->assertViewHas('featuredItems', fn ($items) => $items->contains('id', $item->id))
                ->assertSee('src="'.$item->cover_image_url.'"', false);
        }
        $item->update(['status' => 'draft']);
        $this->get(route('home'))->assertOk()
            ->assertViewHas('knowledgeItems', fn ($items) => ! $items->contains('id', $item->id))
            ->assertViewHas('featuredItems', fn ($items) => ! $items->contains('id', $item->id));
    }

    public function test_result_image_access_tracks_publication_and_judging_views_use_routes(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $context = $this->context();
        $submission = $context['submission'];
        $path = "submissions/{$context['competition']->id}/{$submission->submission_code}/cover.png";
        $file = $submission->files()->create([
            'original_name' => 'cover.png', 'stored_name' => 'cover.png', 'file_path' => $path,
            'file_extension' => 'png', 'mime_type' => 'image/png', 'file_size' => 5, 'is_primary' => true,
        ]);
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, 'image');
        $this->get($file->file_url)->assertNotFound();
        $context['competition']->update(['publish_scores' => true, 'result_announcement' => now()]);
        $this->get($file->file_url)->assertOk();
        $this->get(route('home'))->assertOk()->assertSee('src="'.$file->file_url.'"', false);
        $context['session']->update(['current_submission_id' => $submission->id, 'current_file_id' => $file->id]);
        $this->actingAs($context['owner'])->get(route('competition-admin.competitions.judging-room.show', $context['competition']))
            ->assertOk()->assertSee($file->file_url, false)->assertDontSee('/storage/submissions/', false);
        $this->get(route('competition-admin.competitions.results.index', $context['competition']))
            ->assertOk()->assertSee($file->file_url, false);
        $this->flushSession();
        auth()->forgetGuards();
        $this->actingAs($context['judge'])->get(route('judge.judging-rooms.show', $context['session']))
            ->assertOk()->assertSee($file->file_url, false)->assertDontSee('/storage/submissions/', false);
        auth()->logout();
        $context['competition']->update(['publish_scores' => false]);
        $this->get($file->file_url)->assertNotFound();
    }

    public function test_public_competition_detail_renders_sorted_dynamic_values_with_escaped_html(): void
    {
        $context = $this->context();
        $label = '<b>Project objectives</b>';
        $value = '<script>alert("detail")</script>';
        $this->fieldValue($context, ['label' => 'Technologies', 'field_type' => 'checkbox', 'sort_order' => 20], '["Laravel","Docker","0"]');
        $this->fieldValue($context, ['label' => $label, 'field_type' => 'textarea', 'sort_order' => 10], $value);
        $this->fieldValue($context, ['label' => 'Approach', 'sort_order' => 30], 'Community learning');
        $this->fieldValue($context, ['label' => 'Selected area', 'field_type' => 'select', 'sort_order' => 40], 'Education');
        $this->fieldValue($context, ['label' => 'Selected outcome', 'field_type' => 'radio', 'sort_order' => 50], 'Completed');
        $this->publish($context)->assertSessionHas('success');
        $item = KnowledgeItem::sole();
        auth()->logout();

        $this->get(route('knowledge.show', $item))->assertOk()
            ->assertSeeInOrder(['Project summary', $label, $value, 'Technologies', 'Laravel, Docker, 0', 'Approach', 'Community learning', 'Selected area', 'Education', 'Selected outcome', 'Completed'])
            ->assertDontSee($label, false)
            ->assertDontSee($value, false);

        $context['submission']->update(['project_description' => null]);
        $item->update(['content' => 'Fallback KM content']);
        $this->get(route('knowledge.show', $item))->assertOk()
            ->assertSeeInOrder(['Fallback KM content', $label, $value, 'Technologies', 'Laravel, Docker, 0']);
    }

    public function test_public_competition_detail_omits_empty_file_and_contact_fields(): void
    {
        $context = $this->context();
        $hiddenFields = [
            [['label' => 'Null answer'], null],
            [['label' => 'Blank answer'], " \n\t "],
            [['label' => 'Empty choices', 'field_type' => 'checkbox'], '[]'],
            [['label' => 'Private upload', 'field_type' => 'file'], 'submissions/1/secret/report.pdf'],
            [['label' => 'Private email', 'field_type' => 'email'], 'private@example.com'],
            [['label' => 'Private phone', 'field_type' => 'phone'], '0812345678'],
            [['label' => 'Contact email text', 'field_name' => 'contact_email'], 'contact@example.com'],
            [['label' => 'Contact phone text', 'field_name' => 'contact_phone'], '0823456789'],
            [['label' => 'Mapped contact email', 'system_field' => 'contact_email'], 'mapped@example.com'],
            [['label' => 'Mapped contact phone', 'system_field' => 'contact_phone'], '0834567890'],
        ];
        foreach ($hiddenFields as [$attributes, $value]) {
            $this->fieldValue($context, $attributes, $value);
        }
        $this->publish($context)->assertSessionHas('success');
        auth()->logout();

        $response = $this->get(route('knowledge.show', KnowledgeItem::sole()))->assertOk()
            ->assertSee('Project summary')
            ->assertDontSee($context['submission']->contact_email)
            ->assertDontSee($context['submission']->contact_phone);
        foreach ($hiddenFields as [$attributes, $value]) {
            $response->assertDontSee($attributes['label']);
            if ($value !== null && trim($value) !== '' && $value !== '[]') {
                $response->assertDontSee($value);
            }
        }
    }

    public function test_public_competition_detail_without_dynamic_values_preserves_existing_description(): void
    {
        $context = $this->context();
        $this->publish($context)->assertSessionHas('success');
        $item = KnowledgeItem::sole();
        auth()->logout();

        $this->get(route('knowledge.show', $item))->assertOk()
            ->assertSee($context['submission']->project_title)
            ->assertSee('Project summary');
        $context['submission']->update(['project_description' => null]);
        $this->get(route('knowledge.show', $item))->assertOk()->assertSee('ไม่มีรายละเอียดเพิ่มเติม');
    }

    public function test_public_manual_detail_preserves_content_and_summary_fallback(): void
    {
        $item = KnowledgeItem::create([
            'title' => 'Manual detail',
            'content' => 'Manual body content',
            'summary' => 'Manual summary fallback',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('knowledge.show', $item))->assertOk()
            ->assertSee('Manual detail')->assertSee('Manual body content');
        $item->update(['content' => null]);
        $this->get(route('knowledge.show', $item))->assertOk()->assertSee('Manual summary fallback');
    }

    private function fieldValue(array $context, array $attributes, ?string $value): void
    {
        $field = CompetitionFormField::forceCreate(array_merge([
            'competition_id' => $context['competition']->id,
            'field_name' => 'field_'.uniqid(),
            'field_type' => 'text',
            'sort_order' => 1,
        ], $attributes));
        $context['submission']->fieldValues()->create(['field_id' => $field->id, 'field_value' => $value]);
    }

    private function publish(array $context)
    {
        if (auth()->id() !== $context['owner']->id) {
            $this->flushSession();
            auth()->forgetGuards();
        }

        return $this->actingAs($context['owner'])->post(
            route('competition-admin.submissions.km.publish', $context['submission'])
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
            'registration_start' => now()->subDays(3),
            'registration_end' => now()->subDays(2),
            'status' => 'closed',
        ]);
        $submission = Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'KM Project '.uniqid(),
            'project_description' => 'Project summary',
            'contact_name' => 'Submitter',
            'contact_email' => 'submitter@example.com',
            'contact_phone' => '0800000000',
            'final_score' => 90,
            'status' => $overrides['submission_status'] ?? 'submitted',
            'submitted_at' => now(),
        ]);
        $session = JudgingSession::create([
            'competition_id' => $competition->id,
            'controller_user_id' => $owner->id,
            'status' => $overrides['session_status'] ?? 'ended',
            'started_at' => now()->subHour(),
            'ended_at' => ($overrides['session_status'] ?? 'ended') === 'ended' ? now() : null,
        ]);
        $rubric = Rubric::create([
            'competition_id' => $competition->id,
            'criteria_name' => 'Quality',
            'max_score' => 100,
            'weight' => 100,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $assignment = JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $judge->id,
            'assignment_status' => 'accepted',
            'accepted_at' => now(),
        ]);
        $score = null;
        if ($overrides['with_score'] ?? true) {
            $score = Score::create([
                'submission_id' => $submission->id,
                'rubric_id' => $rubric->id,
                'judge_assignment_id' => $assignment->id,
                'score' => 90,
                'submitted_at' => now(),
            ]);
        }

        return compact(
            'owner',
            'judge',
            'competition',
            'submission',
            'session',
            'rubric',
            'assignment',
            'score'
        );
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
}
