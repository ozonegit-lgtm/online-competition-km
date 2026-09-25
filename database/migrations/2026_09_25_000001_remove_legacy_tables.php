<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('submission_awards');
        Schema::dropIfExists('awards');
        Schema::dropIfExists('knowledge_item_tags');
        Schema::dropIfExists('knowledge_tags');
        Schema::dropIfExists('knowledge_item_files');
        Schema::dropIfExists('competition_judges');
    }

    public function down(): void
    {
        Schema::create('competition_judges', function (Blueprint $table): void {
            $table->id();
            $table->string('fullname', 150);
            $table->string('email', 150)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('organization')->nullable();
            $table->string('position', 150)->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });

        Schema::create('knowledge_item_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('knowledge_item_id')
                ->constrained('knowledge_items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('file_path');
            $table->string('file_extension', 20);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();
            $table->index(['knowledge_item_id', 'file_extension']);
        });

        Schema::create('knowledge_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('tag_name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('knowledge_item_tags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('knowledge_item_id')
                ->constrained('knowledge_items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('knowledge_tag_id')
                ->constrained('knowledge_tags')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['knowledge_item_id', 'knowledge_tag_id']);
            $table->index('knowledge_item_id');
            $table->index('knowledge_tag_id');
        });

        Schema::create('awards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('competition_id')
                ->constrained('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('award_name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->string('certificate_title')->nullable();
            $table->boolean('is_special')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index('competition_id');
            $table->index('rank');
        });

        Schema::create('submission_awards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('submission_id')
                ->constrained('submissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('award_id')
                ->constrained('awards')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamp('awarded_at')->useCurrent();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->unique(['submission_id', 'award_id']);
            $table->index('submission_id');
            $table->index('award_id');
        });
    }
};
