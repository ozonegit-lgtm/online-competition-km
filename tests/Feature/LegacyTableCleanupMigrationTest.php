<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyTableCleanupMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const TABLES = [
        'submission_awards',
        'awards',
        'knowledge_item_tags',
        'knowledge_tags',
        'knowledge_item_files',
        'competition_judges',
    ];

    public function test_cleanup_migration_drops_and_restores_the_latest_legacy_schema(): void
    {
        foreach (self::TABLES as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }

        $migration = require database_path('migrations/2026_09_25_000001_remove_legacy_tables.php');
        $migration->down();

        $this->assertTrue(Schema::hasColumns('competition_judges', [
            'fullname', 'email', 'phone', 'organization', 'position', 'password',
            'remember_token', 'last_login_at', 'status', 'created_at', 'updated_at',
        ]));
        $this->assertTrue(Schema::hasColumns('knowledge_item_files', [
            'knowledge_item_id', 'original_name', 'stored_name', 'file_path',
            'file_extension', 'mime_type', 'file_size',
        ]));
        $this->assertTrue(Schema::hasColumns('knowledge_tags', [
            'tag_name', 'slug', 'color', 'is_active',
        ]));
        $this->assertTrue(Schema::hasColumns('knowledge_item_tags', [
            'knowledge_item_id', 'knowledge_tag_id',
        ]));
        $this->assertTrue(Schema::hasColumns('awards', [
            'competition_id', 'award_name', 'description', 'rank',
            'certificate_title', 'is_special', 'is_active',
        ]));
        $this->assertTrue(Schema::hasColumns('submission_awards', [
            'submission_id', 'award_id', 'awarded_at', 'remark',
        ]));
        $this->assertTrue(Schema::hasIndex('knowledge_item_files', ['knowledge_item_id', 'file_extension']));
        $this->assertTrue(Schema::hasIndex('knowledge_item_tags', ['knowledge_item_id', 'knowledge_tag_id']));
        $this->assertTrue(Schema::hasIndex('submission_awards', ['submission_id', 'award_id']));

        $migration->up();

        foreach (self::TABLES as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }
    }
}
