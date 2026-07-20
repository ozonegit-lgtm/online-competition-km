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
        Schema::create('rubrics', function (Blueprint $table) {

            $table->id();

            $table->foreignId('competition_id')
                ->constrained('competitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('criteria_name', 150);

            $table->text('description')->nullable();

            $table->decimal('max_score', 5, 2)
                ->default(10);

            $table->unsignedInteger('weight')
                ->default(100);

            $table->integer('sort_order')
                ->default(1);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index('competition_id');
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubrics');
    }
};