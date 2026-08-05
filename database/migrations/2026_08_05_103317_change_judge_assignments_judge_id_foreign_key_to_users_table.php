<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เปลี่ยน judge_id จาก competition_judges.id
     * ให้เชื่อมกับ users.id
     */
    public function up(): void
    {
        Schema::table('judge_assignments', function (Blueprint $table) {
            $table->dropForeign([
                'judge_id',
            ]);

            $table->foreign('judge_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * คืน Foreign Key กลับไปยัง competition_judges
     */
    public function down(): void
    {
        Schema::table('judge_assignments', function (Blueprint $table) {
            $table->dropForeign([
                'judge_id',
            ]);

            $table->foreign('judge_id')
                ->references('id')
                ->on('competition_judges')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};