<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KnowledgeItemAttachmentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_attachment_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumn(
            'knowledge_items',
            'attachment_path'
        ));
        $this->assertTrue(Schema::hasColumn(
            'knowledge_items',
            'attachment_original_name'
        ));
    }

    public function test_multiple_manual_items_can_be_stored_without_attachments(): void
    {
        $firstId = $this->insertKnowledgeItem([
            'title' => 'Manual item without attachment one',
        ]);
        $secondId = $this->insertKnowledgeItem([
            'title' => 'Manual item without attachment two',
        ]);

        $this->assertDatabaseHas('knowledge_items', [
            'id' => $firstId,
            'submission_id' => null,
            'attachment_path' => null,
            'attachment_original_name' => null,
        ]);
        $this->assertDatabaseHas('knowledge_items', [
            'id' => $secondId,
            'submission_id' => null,
            'attachment_path' => null,
            'attachment_original_name' => null,
        ]);
    }

    public function test_attachment_and_existing_owner_and_category_fields_are_stored(): void
    {
        [$userId, $categoryId] = $this->ownerAndCategory();
        $path = 'knowledge-items/manual/guide.pdf';
        $originalName = 'คู่มือการจัดการความรู้.pdf';

        $itemId = $this->insertKnowledgeItem([
            'title' => 'Manual item with attachment',
            'created_by' => $userId,
            'category_id' => $categoryId,
            'attachment_path' => $path,
            'attachment_original_name' => $originalName,
        ]);

        $item = DB::table('knowledge_items')->find($itemId);

        $this->assertNotNull($item);
        $this->assertSame($path, $item->attachment_path);
        $this->assertSame(
            $originalName,
            $item->attachment_original_name
        );
        $this->assertSame($userId, $item->created_by);
        $this->assertSame($categoryId, $item->category_id);
    }

    public function test_submission_id_remains_unique(): void
    {
        [$userId, $categoryId] = $this->ownerAndCategory();
        $submissionId = $this->submission($userId, $categoryId);

        $this->insertKnowledgeItem([
            'submission_id' => $submissionId,
            'title' => 'First submission item',
        ]);

        $this->expectException(QueryException::class);

        $this->insertKnowledgeItem([
            'submission_id' => $submissionId,
            'title' => 'Duplicate submission item',
        ]);
    }

    private function insertKnowledgeItem(array $attributes): int
    {
        return DB::table('knowledge_items')->insertGetId(array_merge([
            'submission_id' => null,
            'created_by' => null,
            'category_id' => null,
            'title' => 'Knowledge item',
            'summary' => null,
            'content' => null,
            'cover_image' => null,
            'attachment_path' => null,
            'attachment_original_name' => null,
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
