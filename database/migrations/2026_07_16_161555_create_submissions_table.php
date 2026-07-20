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
        Schema::create('submissions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('competition_id')
                ->constrained('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('submission_code',30)->unique();

            $table->string('project_title',255);

            $table->text('project_description')->nullable();

            $table->string('team_name',255)->nullable();

            $table->string('contact_name',150);

            $table->string('contact_email',150);

            $table->string('contact_phone',20);

            $table->decimal('final_score',8,2)
                ->default(0);

            $table->enum('status',[
                'draft',
                'submitted',
                'under_review',
                'completed',
                'disqualified'
            ])->default('submitted');

            $table->timestamp('submitted_at')
                ->useCurrent();

            $table->timestamps();

            $table->index('competition_id');
            $table->index('status');
            $table->index('submitted_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};