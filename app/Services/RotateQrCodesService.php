<?php

namespace App\Services;

use App\Models\AttendanceSetting;
use App\Models\AuditLog;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RotateQrCodesService
{
    /**
     * Regenerate the QR token of every active location once a day so old
     * scan links and printed posters stop working.
     *
     * Returns the number of locations whose QR code was rotated. Does
     * nothing when the auto-renewal setting is disabled.
     */
    public function run(): int
    {
        $setting = AttendanceSetting::current();

        if (! $setting->auto_renew_qr_enabled) {
            return 0;
        }

        $locations = Location::query()->where('active', true)->get();

        if ($locations->isEmpty()) {
            return 0;
        }

        $rotated = [];

        DB::transaction(function () use ($locations, &$rotated) {
            foreach ($locations as $location) {
                $location->update(['qr_token' => Str::random(32)]);
                $rotated[] = $location->id;
            }

            AuditLog::create([
                'action' => 'qr_codes_rotated',
                'description' => count($rotated).' location QR code(s) were renewed automatically.',
                'metadata' => [
                    'location_ids' => $rotated,
                ],
            ]);
        });

        return count($rotated);
    }
}
