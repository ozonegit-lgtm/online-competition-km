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
        Schema::create('judge_assignments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('competition_id')
                ->constrained('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('judge_id')
                ->constrained('competition_judges')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamp('assigned_at')
                ->useCurrent();

            $table->enum('assignment_status',[
                'pending',
                'accepted',
                'declined'
            ])->default('pending');

            $table->timestamp('accepted_at')->nullable();

            $table->timestamp('declined_at')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->unique([
                'competition_id',
                'judge_id'
            ]);

            $table->index('assignment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('judge_assignments');
    }
};