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
        Schema::create('awards', function (Blueprint $table) {

            $table->id();

            $table->foreignId('competition_id')
                ->constrained('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('award_name',150);

            $table->text('description')->nullable();

            $table->unsignedInteger('rank')->nullable();

            $table->string('certificate_title',255)->nullable();

            $table->boolean('is_special')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index('competition_id');
            $table->index('rank');
            $table->index('is_special');
            $table->index('is_active');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};