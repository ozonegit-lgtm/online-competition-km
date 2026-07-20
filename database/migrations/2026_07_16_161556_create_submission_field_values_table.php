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
        Schema::create('submission_field_values', function (Blueprint $table) {

            $table->id();

            $table->foreignId('submission_id')
                ->constrained('submissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('field_id')
                ->constrained('competition_form_fields')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->longText('field_value')->nullable();

            $table->timestamps();

            $table->unique([
                'submission_id',
                'field_id'
            ]);

            $table->index('submission_id');
            $table->index('field_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_field_values');
    }
};