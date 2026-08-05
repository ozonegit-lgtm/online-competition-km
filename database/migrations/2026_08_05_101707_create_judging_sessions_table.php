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
        Schema::create('judging_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')
                ->constrained('competitions')
                ->cascadeOnDelete();

            $table->foreignId('controller_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('current_submission_id')
                ->nullable()
                ->constrained('submissions')
                ->nullOnDelete();

            $table->foreignId('current_file_id')
                ->nullable()
                ->constrained('submission_files')
                ->nullOnDelete();

            /*
             * waiting = เปิดห้องแล้ว กำลังรอ
             * live    = กำลังตัดสิน
             * paused  = พักการตัดสิน
             * ended   = จบการตัดสิน
             * closed  = ปิดห้อง
             */
            $table->string('status', 20)
                ->default('waiting')
                ->index();

            $table->unsignedInteger('current_page')
                ->default(1);

            /*
             * ตำแหน่งเลื่อนตั้งแต่ 0.00000 ถึง 1.00000
             */
            $table->decimal('scroll_progress', 6, 5)
                ->default(0);

            $table->decimal('zoom', 5, 2)
                ->default(1);

            /*
             * ใช้ป้องกัน Event เก่ามาทับสถานะใหม่
             */
            $table->unsignedBigInteger('state_version')
                ->default(0);

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('ended_at')
                ->nullable();

            $table->timestamps();

            /*
             * การแข่งขันหนึ่งรายการมีห้องตัดสินหนึ่งห้อง
             */
            $table->unique('competition_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('judging_sessions');
    }
};