<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('submission_id')
                ->constrained('submissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('title',255);

            $table->text('summary')->nullable();

            $table->longText('content')->nullable();

            $table->string('cover_image',255)->nullable();

            $table->boolean('is_featured')
                ->default(false);

            $table->enum('status',[
                'draft',
                'published',
                'hidden'
            ])->default('draft');

            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique('submission_id');

            $table->index('status');
            $table->index('is_featured');
            $table->index('published_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_items');
    }
};