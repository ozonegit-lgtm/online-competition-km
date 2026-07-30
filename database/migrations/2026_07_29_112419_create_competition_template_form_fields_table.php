<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'competition_template_form_fields',
                function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('template_id')->constrained('competition_templates')->cascadeOnUpdate()->cascadeOnDelete();
                    $table->string('label', 150);
                    $table->string('field_name', 100);
                    $table->enum('field_type', [
                        'text',
                        'textarea',
                        'number',
                        'email',
                        'phone',
                        'date',
                        'file',
                        'select',
                        'radio',
                        'checkbox',
                    ]);
                    $table->text('placeholder')->nullable();
                    $table->text('help_text')->nullable();
                    $table->text('options')->nullable();
                    $table->boolean('is_required')->default(false);
                    $table->integer('sort_order')->default(1);
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                    $table->index('template_id');
                    $table->index('field_type');
                    $table->index('sort_order');
                    $table->unique([
                        'template_id',
                        'field_name',
                    ]);
                }
        );
    }
    public function down(): void
    {
        Schema::dropIfExists(
            'competition_template_form_fields'
        );
    }
};