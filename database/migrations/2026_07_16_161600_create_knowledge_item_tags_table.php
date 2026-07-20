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
        Schema::create('knowledge_item_tags', function (Blueprint $table) {

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

            $table->unique([
                'knowledge_item_id',
                'knowledge_tag_id'
            ]);

            $table->index('knowledge_item_id');
            $table->index('knowledge_tag_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_item_tags');
    }
};