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
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->string('shift_start_time')->default('09:00')->after('auto_clock_out_time');
            $table->string('shift_end_time')->default('17:00')->after('shift_start_time');
            $table->unsignedInteger('reason_text_max_length')->default(255)->after('shift_end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['shift_start_time', 'shift_end_time', 'reason_text_max_length']);
        });
    }
};
