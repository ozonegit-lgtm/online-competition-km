<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_template_form_fields', function (Blueprint $table) {
            $table->string('accepted_file_types')->nullable()->after('options');
            $table->unsignedInteger('max_file_size')->nullable()->after('accepted_file_types');
        });

        DB::table('competition_template_form_fields')
            ->where('field_type', 'file')
            ->update([
                'accepted_file_types' => 'jpg,jpeg,png,webp,pdf,doc,docx,ppt,pptx,zip',
                'max_file_size' => 10,
            ]);
    }

    public function down(): void
    {
        Schema::table('competition_template_form_fields', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_file_types',
                'max_file_size',
            ]);
        });
    }
};
