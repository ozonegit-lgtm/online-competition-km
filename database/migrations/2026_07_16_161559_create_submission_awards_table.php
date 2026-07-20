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
        Schema::create('submission_awards', function (Blueprint $table) {

            $table->id();

            $table->foreignId('submission_id')
                ->constrained('submissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('award_id')
                ->constrained('awards')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamp('awarded_at')
                ->useCurrent();

            $table->text('remark')->nullable();

            $table->timestamps();

            $table->unique([
                'submission_id',
                'award_id'
            ]);

            $table->index('submission_id');
            $table->index('award_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_awards');
    }
};