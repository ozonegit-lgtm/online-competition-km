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
        Schema::create('competition_templates', function (Blueprint $table) {

            $table->id();

            $table->string('template_name',100)->unique();
            $table->string('template_slug',120)->unique();

            $table->text('default_description')->nullable();

            $table->string('cover_image',255)->nullable();

            $table->boolean('is_active')
                ->default(true)
                ->comment('เปิด/ปิดการใช้งาน Template');

            $table->timestamps();

            $table->index('is_active');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_templates');
    }
};