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
        Schema::create('competitions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('category_id')
                ->constrained('competition_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('template_id')
                ->nullable()
                ->constrained('competition_templates')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('title',255);

            $table->text('description')->nullable();

            $table->string('cover_image',255)->nullable();

            $table->enum('competition_type',[
                'individual',
                'team'
            ])->default('individual');

            $table->enum('visibility',[
                'public',
                'private'
            ])->default('public');

            $table->string('access_code',100)->nullable();

            $table->dateTime('registration_start');

            $table->dateTime('registration_end');

            $table->dateTime('judging_start')->nullable();

            $table->dateTime('judging_end')->nullable();

            $table->dateTime('result_announcement')->nullable();

            $table->boolean('publish_scores')
                ->default(true);

            $table->boolean('publish_km')
                ->default(false);

            $table->enum('status',[
                'draft',
                'open',
                'closed',
                'judging',
                'completed',
                'archived'
            ])->default('draft');

            $table->timestamps();

            $table->index('status');
            $table->index('visibility');
            $table->index('competition_type');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};