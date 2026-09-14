<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_items', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->string('volume', 50)->nullable();
            $table->string('issue', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_items', function (Blueprint $table): void {
            $table->dropIndex(['sort_order']);
            $table->dropColumn(['sort_order', 'publication_year', 'volume', 'issue']);
        });
    }
};
