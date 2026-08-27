<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('competitions')->update([
            'publish_scores' => false,
        ]);

        Schema::table('competitions', function (Blueprint $table) {
            $table->boolean('publish_scores')
                ->default(false)
                ->change();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->decimal('final_score', 8, 2)
                ->nullable()
                ->default(null)
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('submissions')
            ->whereNull('final_score')
            ->update(['final_score' => 0]);

        Schema::table('submissions', function (Blueprint $table) {
            $table->decimal('final_score', 8, 2)
                ->nullable(false)
                ->default(0)
                ->change();
        });

        Schema::table('competitions', function (Blueprint $table) {
            $table->boolean('publish_scores')
                ->default(true)
                ->change();
        });
    }
};
