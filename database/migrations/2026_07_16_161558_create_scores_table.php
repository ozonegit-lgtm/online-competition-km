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
        Schema::create('scores', function (Blueprint $table) {

            $table->id();

            $table->foreignId('submission_id')
                ->constrained('submissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('rubric_id')
                ->constrained('rubrics')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('judge_assignment_id')
                ->constrained('judge_assignments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->decimal('score',6,2);

            $table->text('comment')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->unique([
                'submission_id',
                'rubric_id',
                'judge_assignment_id'
            ]);

            $table->index('submission_id');
            $table->index('rubric_id');
            $table->index('judge_assignment_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};