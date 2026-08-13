<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->string('clock_out_type')->nullable()->after('type');
            $table->timestamp('auto_clocked_out_at')->nullable()->after('scanned_at');
        });

        DB::table('attendance_logs')
            ->where('type', 'time_out')
            ->whereNull('clock_out_type')
            ->update(['clock_out_type' => 'manual']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['clock_out_type', 'auto_clocked_out_at']);
        });
    }
};
