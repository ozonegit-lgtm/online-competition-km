<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_items', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);

            $table->unsignedBigInteger('submission_id')
                ->nullable()
                ->change();

            $table->foreign('submission_id')
                ->references('id')
                ->on('submissions')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->after('submission_id')
                ->constrained('users')
                ->cascadeOnUpdate();

            $table->foreignId('category_id')
                ->nullable()
                ->after('created_by')
                ->constrained('competition_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        DB::table('knowledge_items')
            ->join(
                'submissions',
                'submissions.id',
                '=',
                'knowledge_items.submission_id'
            )
            ->join(
                'competitions',
                'competitions.id',
                '=',
                'submissions.competition_id'
            )
            ->select([
                'knowledge_items.id',
                'competitions.created_by',
                'competitions.category_id',
            ])
            ->orderBy('knowledge_items.id')
            ->each(function (object $item): void {
                DB::table('knowledge_items')
                    ->where('id', $item->id)
                    ->update([
                        'created_by' => $item->created_by,
                        'category_id' => $item->category_id,
                    ]);
            });
    }

    public function down(): void
    {
        if (
            DB::table('knowledge_items')
                ->whereNull('submission_id')
                ->exists()
        ) {
            throw new \RuntimeException(
                'Cannot disable standalone knowledge items while manual knowledge items exist.'
            );
        }

        Schema::table('knowledge_items', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('category_id');
            $table->unsignedBigInteger('submission_id')
                ->nullable(false)
                ->change();

            $table->foreign('submission_id')
                ->references('id')
                ->on('submissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
