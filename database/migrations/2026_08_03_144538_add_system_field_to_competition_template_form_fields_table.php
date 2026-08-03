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
        Schema::table('competition_template_form_fields', function (Blueprint $table) {
            $table->dropColumn('system_field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_template_form_fields', function (Blueprint $table) {
            $table->dropColumn('system_field');
        });
    }
};
