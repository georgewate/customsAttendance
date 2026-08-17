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
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->boolean('is_late')->default(false)->after('type');
            $table->boolean('is_early_clock_out')->default(false)->after('is_late');
            $table->foreignId('late_reason_id')->nullable()->after('is_early_clock_out')->constrained('attendance_reasons')->nullOnDelete();
            $table->string('late_reason_text')->nullable()->after('late_reason_id');
            $table->foreignId('early_clock_out_reason_id')->nullable()->after('late_reason_text')->constrained('attendance_reasons')->nullOnDelete();
            $table->string('early_clock_out_reason_text')->nullable()->after('early_clock_out_reason_id');
            $table->timestamp('reason_submitted_at')->nullable()->after('early_clock_out_reason_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('late_reason_id');
            $table->dropConstrainedForeignId('early_clock_out_reason_id');
            $table->dropColumn([
                'is_late',
                'is_early_clock_out',
                'late_reason_text',
                'early_clock_out_reason_text',
                'reason_submitted_at',
            ]);
        });
    }
};
