<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('contact_name', 150)
                ->nullable()
                ->change();

            $table->string('contact_email', 150)
                ->nullable()
                ->change();

            $table->string('contact_phone', 20)
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('contact_name', 150)
                ->nullable(false)
                ->change();

            $table->string('contact_email', 150)
                ->nullable(false)
                ->change();

            $table->string('contact_phone', 20)
                ->nullable(false)
                ->change();
        });
    }
};