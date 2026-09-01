<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\KnowledgeItem;
use App\Models\Submission;
use App\Models\User;
use App\Policies\KnowledgeItemPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class KnowledgeItemPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_laravel_discovers_knowledge_item_policy(): void
    {
        $this->assertInstanceOf(
            KnowledgeItemPolicy::class,
            Gate::getPolicyFor(KnowledgeItem::class)
        );
    }

    public function test_super_admin_has_every_declared_ability(): void
    {
        $superAdmin = $this->user('super-admin', 'Super Admin');
        $owner = $this->user('owner', 'Competition Admin');
        $ownedItem = $this->knowledgeItem($owner->id);
        $orphanItem = $this->knowledgeItem(null);

        $this->assertTrue($superAdmin->can('viewAny', KnowledgeItem::class));
        $this->assertTrue($superAdmin->can('create', KnowledgeItem::class));

        foreach (['view', 'update', 'publish', 'unpublish', 'delete'] as $ability) {
            $this->assertTrue($superAdmin->can($ability, $ownedItem));
            $this->assertTrue($superAdmin->can($ability, $orphanItem));
        }

        $this->assertTrue($superAdmin->can('feature', $ownedItem));
    }

    public function test_competition_admin_can_manage_only_owned_items(): void
    {
        $owner = $this->user('owner', 'Competition Admin');
        $otherAdmin = $this->user('other-admin', 'Competition Admin');
        $ownedItem = $this->knowledgeItem($owner->id);
        $otherItem = $this->knowledgeItem($otherAdmin->id);
        $orphanItem = $this->knowledgeItem(null);

        $this->assertTrue($owner->can('viewAny', KnowledgeItem::class));
        $this->assertTrue($owner->can('create', KnowledgeItem::class));

        foreach (['view', 'update', 'publish', 'unpublish', 'delete'] as $ability) {
            $this->assertTrue($owner->can($ability, $ownedItem));
            $this->assertFalse($owner->can($ability, $otherItem));
            $this->assertFalse($owner->can($ability, $orphanItem));
        }

        $this->assertFalse($owner->can('feature', $ownedItem));
    }

    public function test_submission_id_does_not_bypass_created_by_ownership(): void
    {
        $owner = $this->user('owner', 'Competition Admin');
        $otherAdmin = $this->user('other-admin', 'Competition Admin');
        $category = $this->category();
        $submission = $this->submission($owner, $category);
        $item = $this->knowledgeItem(
            $otherAdmin->id,
            $category->id,
            $submission->id
        );

        foreach (['view', 'update', 'publish', 'unpublish', 'delete'] as $ability) {
            $this->assertFalse($owner->can($ability, $item));
            $this->assertTrue($otherAdmin->can($ability, $item));
        }
    }

    public function test_judge_has_no_knowledge_item_management_abilities(): void
    {
        $judge = $this->user('judge', 'Judge');
        $item = $this->knowledgeItem($judge->id);

        $this->assertFalse($judge->can('viewAny', KnowledgeItem::class));
        $this->assertFalse($judge->can('create', KnowledgeItem::class));

        foreach (
            ['view', 'update', 'publish', 'unpublish', 'delete', 'feature']
            as $ability
        ) {
            $this->assertFalse($judge->can($ability, $item));
        }
    }

    public function test_model_fields_and_relationships_support_manual_items(): void
    {
        $owner = $this->user('owner', 'Competition Admin');
        $category = $this->category();
        $submission = $this->submission($owner, $category);
        $item = KnowledgeItem::create([
            'submission_id' => $submission->id,
            'created_by' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Knowledge item relationships',
            'attachment_path' => 'knowledge-items/guide.pdf',
            'attachment_original_name' => 'คู่มือความรู้.pdf',
        ]);

        $this->assertTrue($item->creator->is($owner));
        $this->assertTrue($item->category->is($category));
        $this->assertTrue($item->submission->is($submission));
        $this->assertTrue($owner->knowledgeItems->contains($item));
        $this->assertSame(
            'knowledge-items/guide.pdf',
            $item->attachment_path
        );
        $this->assertSame(
            'คู่มือความรู้.pdf',
            $item->attachment_original_name
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

    private function category(): CompetitionCategory
    {
        return CompetitionCategory::create([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function knowledgeItem(
        ?int $createdBy,
        ?int $categoryId = null,
        ?int $submissionId = null
    ): KnowledgeItem {
        return KnowledgeItem::create([
            'submission_id' => $submissionId,
            'created_by' => $createdBy,
            'category_id' => $categoryId,
            'title' => 'Knowledge item '.uniqid(),
            'status' => 'draft',
        ]);
    }

    private function submission(
        User $owner,
        CompetitionCategory $category
    ): Submission {
        $competition = Competition::create([
            'category_id' => $category->id,
            'created_by' => $owner->id,
            'title' => 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subDay(),
            'registration_end' => now(),
            'status' => 'closed',
        ]);

        return Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'Submission project',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }
}
