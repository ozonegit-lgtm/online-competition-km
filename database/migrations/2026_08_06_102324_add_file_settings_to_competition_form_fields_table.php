<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'competition_form_fields',
            function (Blueprint $table) {
                $table->string('accepted_file_types')
                    ->nullable()
                    ->after('options');

                $table->unsignedInteger('max_file_size')
                    ->nullable()
                    ->after('accepted_file_types');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'competition_form_fields',
            function (Blueprint $table) {
                $table->dropColumn([
                    'accepted_file_types',
                    'max_file_size',
                ]);
            }
        );
    }
};