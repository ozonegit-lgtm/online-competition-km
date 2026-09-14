<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_page_nav_items', function (Blueprint $table): void {
            $table->id();
            $table->string('placement', 20);
            $table->string('label', 150);
            $table->string('url', 2048);
            $table->string('target', 10)->default('_self');
            $table->string('icon_key', 30)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->index(['placement', 'is_visible', 'sort_order'], 'knowledge_nav_display_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_page_nav_items');
    }
};
