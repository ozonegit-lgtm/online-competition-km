<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KnowledgeManagementReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_schema_contains_current_knowledge_management_columns_and_constraints(): void
    {
        $this->assertTrue(Schema::hasColumns('knowledge_items', ['knowledge_type', 'knowledge_category_id', 'external_url', 'archived_at', 'attachment_path']));
        $this->assertTrue(Schema::hasColumns('knowledge_categories', ['name', 'slug', 'description', 'is_active']));
        $this->assertFalse(Schema::hasTable('knowledge_item_files'));
        $category = DB::table('knowledge_categories')->insertGetId(['name' => 'Archive', 'slug' => 'archive']);
        $item = DB::table('knowledge_items')->insertGetId(['title' => 'Historical', 'status' => 'archived', 'knowledge_category_id' => $category]);
        $this->assertDatabaseHas('knowledge_items', ['id' => $item, 'knowledge_type' => 'article', 'status' => 'archived']);
        DB::table('knowledge_categories')->where('id', $category)->delete();
        $this->assertDatabaseHas('knowledge_items', ['id' => $item, 'knowledge_category_id' => null]);
        $this->assertTrue(Schema::hasIndex('knowledge_items', ['status']));
    }
}
