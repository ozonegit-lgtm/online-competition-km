<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StandaloneKnowledgeItemSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_manual_items_can_exist_without_submissions(): void
    {
        $firstId = $this->insertKnowledgeItem([
            'title' => 'Manual item one',
        ]);
        $secondId = $this->insertKnowledgeItem([
            'title' => 'Manual item two',
        ]);

        $this->assertDatabaseHas('knowledge_items', [
            'id' => $firstId,
            'submission_id' => null,
        ]);
        $this->assertDatabaseHas('knowledge_items', [
            'id' => $secondId,
            'submission_id' => null,
        ]);
    }

    public function test_manual_item_can_reference_an_existing_user_and_category(): void
    {
        [$userId, $categoryId] = $this->ownerAndCategory();

        $itemId = $this->insertKnowledgeItem([
            'title' => 'Owned manual item',
            'created_by' => $userId,
            'category_id' => $categoryId,
        ]);

        $this->assertDatabaseHas('knowledge_items', [
            'id' => $itemId,
            'submission_id' => null,
            'created_by' => $userId,
            'category_id' => $categoryId,
        ]);
    }

    public function test_submission_item_can_store_its_owner_and_category(): void
    {
        [$userId, $categoryId] = $this->ownerAndCategory();
        $submissionId = $this->submission($userId, $categoryId);

        $itemId = $this->insertKnowledgeItem([
            'submission_id' => $submissionId,
            'title' => 'Submission knowledge item',
            'created_by' => $userId,
            'category_id' => $categoryId,
        ]);

        $this->assertDatabaseHas('knowledge_items', [
            'id' => $itemId,
            'submission_id' => $submissionId,
            'created_by' => $userId,
            'category_id' => $categoryId,
        ]);
    }

    public function test_submission_id_remains_unique(): void
    {
        [$userId, $categoryId] = $this->ownerAndCategory();
        $submissionId = $this->submission($userId, $categoryId);

        $this->insertKnowledgeItem([
            'submission_id' => $submissionId,
            'title' => 'First item',
        ]);

        $this->expectException(QueryException::class);

        $this->insertKnowledgeItem([
            'submission_id' => $submissionId,
            'title' => 'Duplicate item',
        ]);
    }

    public function test_invalid_created_by_foreign_key_is_rejected(): void
    {
        $this->expectException(QueryException::class);

        $this->insertKnowledgeItem([
            'title' => 'Invalid owner',
            'created_by' => 999999,
        ]);
    }

    public function test_invalid_category_foreign_key_is_rejected(): void
    {
        $this->expectException(QueryException::class);

        $this->insertKnowledgeItem([
            'title' => 'Invalid category',
            'category_id' => 999999,
        ]);
    }

    public function test_deleting_category_sets_knowledge_item_category_to_null(): void
    {
        [$userId, $categoryId] = $this->ownerAndCategory();
        $itemId = $this->insertKnowledgeItem([
            'title' => 'Category deletion item',
            'created_by' => $userId,
            'category_id' => $categoryId,
        ]);

        DB::table('competition_categories')
            ->where('id', $categoryId)
            ->delete();

        $this->assertDatabaseHas('knowledge_items', [
            'id' => $itemId,
            'created_by' => $userId,
            'category_id' => null,
        ]);
    }

    public function test_referenced_owner_cannot_be_deleted(): void
    {
        [$userId, $categoryId] = $this->ownerAndCategory();
        $this->insertKnowledgeItem([
            'title' => 'Owner deletion item',
            'created_by' => $userId,
            'category_id' => $categoryId,
        ]);

        $this->expectException(QueryException::class);

        DB::table('users')->where('id', $userId)->delete();
    }

    public function test_deleting_submission_preserves_item_and_sets_submission_to_null(): void
    {
        [$userId, $categoryId] = $this->ownerAndCategory();
        $submissionId = $this->submission($userId, $categoryId);
        $itemId = $this->insertKnowledgeItem([
            'submission_id' => $submissionId,
            'title' => 'Preserved submission item',
            'created_by' => $userId,
            'category_id' => $categoryId,
        ]);

        DB::table('submissions')->where('id', $submissionId)->delete();

        $this->assertDatabaseHas('knowledge_items', [
            'id' => $itemId,
            'submission_id' => null,
            'created_by' => $userId,
            'category_id' => $categoryId,
        ]);
    }

    private function insertKnowledgeItem(array $attributes): int
    {
        return DB::table('knowledge_items')->insertGetId(array_merge([
            'submission_id' => null,
            'created_by' => null,
            'category_id' => null,
            'summary' => null,
            'content' => null,
            'cover_image' => null,
            'is_featured' => false,
            'status' => 'draft',
            'published_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    private function ownerAndCategory(): array
    {
        $roleId = DB::table('roles')->insertGetId([
            'role_name' => 'Competition Admin',
            'display_name' => 'Competition Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'role_id' => $roleId,
            'username' => 'owner-'.uniqid(),
            'email' => uniqid().'@example.com',
            'password' => 'password',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$userId, $categoryId];
    }

    private function submission(int $userId, int $categoryId): int
    {
        $competitionId = DB::table('competitions')->insertGetId([
            'category_id' => $categoryId,
            'created_by' => $userId,
            'title' => 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subDay(),
            'registration_end' => now(),
            'status' => 'closed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('submissions')->insertGetId([
            'competition_id' => $competitionId,
            'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'Submission project',
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
