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
        Schema::create('competition_judges', function (Blueprint $table) {

            $table->id();

            $table->string('fullname',150);

            $table->string('email',150)->unique();

            $table->string('phone',20)->nullable();

            $table->string('organization',255)->nullable();

            $table->string('position',150)->nullable();

            $table->string('password');

            $table->rememberToken();

            $table->timestamp('last_login_at')->nullable();

            $table->enum('status',[
                'active',
                'inactive'
            ])->default('active');

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_judges');
    }
};