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
        Schema::create('submission_files', function (Blueprint $table) {

            $table->id();

            $table->foreignId('submission_id')
                ->constrained('submissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('original_name',255);

            $table->string('stored_name',255);

            $table->string('file_path',500);

            $table->string('file_extension',20);

            $table->string('mime_type',100);

            $table->unsignedBigInteger('file_size');

            $table->boolean('is_primary')
                ->default(false);

            $table->timestamps();

            $table->index('submission_id');
            $table->index('is_primary');
            $table->index('file_extension');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_files');
    }
};