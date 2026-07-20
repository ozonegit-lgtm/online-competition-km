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
        Schema::create('submission_members', function (Blueprint $table) {

            $table->id();

            $table->foreignId('submission_id')
                ->constrained('submissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('fullname',150);

            $table->string('email',150)->nullable();

            $table->string('phone',20)->nullable();

            $table->string('organization',255)->nullable();

            $table->string('position',150)->nullable();

            $table->boolean('is_team_leader')
                ->default(false);

            $table->timestamps();

            $table->index('submission_id');
            $table->index('is_team_leader');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_members');
    }
};