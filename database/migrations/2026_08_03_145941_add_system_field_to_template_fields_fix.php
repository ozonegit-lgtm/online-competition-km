<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_template_form_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('competition_template_form_fields', 'system_field')) {
                $table->string('system_field')->nullable()->after('field_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('competition_template_form_fields', function (Blueprint $table) {
            if (Schema::hasColumn('competition_template_form_fields', 'system_field')) {
                $table->dropColumn('system_field');
            }
        });
    }
};