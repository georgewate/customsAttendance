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
            $table->enum('reason_status', ['pending', 'approved', 'rejected'])
                ->nullable()
                ->after('reason_submitted_at');
            $table->foreignId('reason_reviewed_by')
                ->nullable()
                ->after('reason_status')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reason_reviewed_at')
                ->nullable()
                ->after('reason_reviewed_by');
            $table->string('reason_review_note')
                ->nullable()
                ->after('reason_reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reason_reviewed_by');
            $table->dropColumn([
                'reason_status',
                'reason_reviewed_at',
                'reason_review_note',
            ]);
        });
    }
};
