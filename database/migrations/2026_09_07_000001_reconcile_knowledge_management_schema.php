<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Reconcile the schema observed in the database that ran the missing
    // 2026_08_31_000002 migration. Do not rewrite its historical ledger entry.
    public function up(): void
    {
        if (! Schema::hasTable('knowledge_categories')) {
            Schema::create('knowledge_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('knowledge_items', 'knowledge_type')) {
            Schema::table('knowledge_items', fn (Blueprint $table) =>
                $table->string('knowledge_type', 20)->default('article')->index());
        }
        if (! Schema::hasColumn('knowledge_items', 'knowledge_category_id')) {
            Schema::table('knowledge_items', fn (Blueprint $table) =>
                $table->foreignId('knowledge_category_id')->nullable()
                    ->constrained('knowledge_categories')->nullOnDelete()->cascadeOnUpdate());
        }
        if (! Schema::hasColumn('knowledge_items', 'external_url')) {
            Schema::table('knowledge_items', fn (Blueprint $table) => $table->text('external_url')->nullable());
        }
        if (! Schema::hasColumn('knowledge_items', 'archived_at')) {
            Schema::table('knowledge_items', fn (Blueprint $table) => $table->timestamp('archived_at')->nullable()->index());
        }
        // The original repository used an enum; the deployed expansion uses varchar(20).
        // Preserve its existing index and values, including historical statuses.
        $needsStatusChange = Schema::getColumnType('knowledge_items', 'status') === 'enum';
        if (DB::getDriverName() === 'sqlite') {
            $definition = DB::table('sqlite_master')->where('type', 'table')->where('name', 'knowledge_items')->value('sql');
            $needsStatusChange = (bool) preg_match('/check\s*\(\s*["`]?status["`]?\s+in\s*\(/i', $definition);
        }
        if ($needsStatusChange) {
            Schema::withoutForeignKeyConstraints(fn () => Schema::table('knowledge_items',
                fn (Blueprint $table) => $table->string('status', 20)->default('draft')->change()));
        }

        if (! Schema::hasTable('knowledge_item_files')) {
            Schema::create('knowledge_item_files', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('knowledge_item_id')->constrained('knowledge_items')->cascadeOnDelete()->cascadeOnUpdate();
                $table->string('original_name');
                $table->string('stored_name');
                $table->string('file_path');
                $table->string('file_extension', 20);
                $table->string('mime_type', 150);
                $table->unsignedBigInteger('file_size');
                $table->timestamps();
                $table->index(['knowledge_item_id', 'file_extension']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: these tables/columns may predate this
        // migration and contain production data. Re-running up() is safe.
    }
};
