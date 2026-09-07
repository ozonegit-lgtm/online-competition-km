<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KnowledgeManagementReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_schema_contains_deployed_expansion_and_constraints(): void
    {
        $this->assertTrue(Schema::hasColumns('knowledge_items', ['knowledge_type', 'knowledge_category_id', 'external_url', 'archived_at', 'attachment_path']));
        $this->assertTrue(Schema::hasColumns('knowledge_categories', ['name', 'slug', 'description', 'is_active']));
        $this->assertTrue(Schema::hasColumns('knowledge_item_files', ['knowledge_item_id', 'original_name', 'stored_name', 'file_path', 'file_extension', 'mime_type', 'file_size']));
        $category = DB::table('knowledge_categories')->insertGetId(['name' => 'Archive', 'slug' => 'archive']);
        $item = DB::table('knowledge_items')->insertGetId(['title' => 'Historical', 'status' => 'archived', 'knowledge_category_id' => $category]);
        $this->assertDatabaseHas('knowledge_items', ['id' => $item, 'knowledge_type' => 'article', 'status' => 'archived']);
        DB::table('knowledge_categories')->where('id', $category)->delete();
        $this->assertDatabaseHas('knowledge_items', ['id' => $item, 'knowledge_category_id' => null]);
        $this->assertTrue(Schema::hasIndex('knowledge_item_files', ['knowledge_item_id', 'file_extension']));
        $this->assertTrue(Schema::hasIndex('knowledge_items', ['status']));
    }

    public function test_reconciliation_is_repeatable_and_does_not_drop_existing_data_on_down(): void
    {
        $item = DB::table('knowledge_items')->insertGetId(['title' => 'Keep me', 'knowledge_type' => 'image', 'external_url' => 'https://example.com', 'status' => 'archived']);
        $legacy = DB::table('knowledge_item_files')->insertGetId([
            'knowledge_item_id' => $item, 'original_name' => 'old.doc', 'stored_name' => 'old.doc',
            'file_path' => "knowledge-items/{$item}/files/old.doc", 'file_extension' => 'doc',
            'mime_type' => 'application/msword', 'file_size' => 12,
        ]);
        $migration = require database_path('migrations/2026_09_07_000001_reconcile_knowledge_management_schema.php');
        $before = (array) DB::table('knowledge_items')->find($item);
        $migration->up();
        $migration->up();
        $migration->down();
        $this->assertSame($before, (array) DB::table('knowledge_items')->find($item));
        $this->assertDatabaseHas('knowledge_item_files', ['id' => $legacy, 'knowledge_item_id' => $item]);
        DB::table('knowledge_items')->where('id', $item)->delete();
        $this->assertDatabaseMissing('knowledge_item_files', ['id' => $legacy]);
    }
}
