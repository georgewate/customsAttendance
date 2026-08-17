<?php

namespace App\Console\Commands;

use App\Services\AutoClockOutService;
use Illuminate\Console\Command;

class AutoClockOut extends Command
{
    protected $signature = 'attendance:auto-clockout';

    protected $description = 'Automatically clock out employees who are still clocked in at the configured auto clock-out time.';

    public function handle(AutoClockOutService $service): int
    {
        $processed = $service->run();

        $this->info("Auto clock-out run completed. {$processed} open shift(s) were clocked out automatically.");

        return self::SUCCESS;
    }
}
