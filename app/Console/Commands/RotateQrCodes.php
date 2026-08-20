<?php

namespace App\Console\Commands;

use App\Services\RotateQrCodesService;
use Illuminate\Console\Command;

class RotateQrCodes extends Command
{
    protected $signature = 'attendance:rotate-qr-codes';

    protected $description = 'Regenerate every active location QR code when automatic renewal is enabled.';

    public function handle(RotateQrCodesService $service): int
    {
        $rotated = $service->run();

        if ($rotated === 0) {
            $this->info('QR code renewal is disabled or no locations were found. Nothing rotated.');
        } else {
            $this->info("{$rotated} location QR code(s) were renewed.");
        }

        return self::SUCCESS;
    }
}
