<?php

use App\Services\DeduplicateAttendanceLogsService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Enforce one clock-in and one clock-out per employee per day by
     * removing any duplicate attendance records left over from earlier
     * clocking flows.
     */
    public function up(): void
    {
        app(DeduplicateAttendanceLogsService::class)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Deleted logs cannot be restored.
    }
};
