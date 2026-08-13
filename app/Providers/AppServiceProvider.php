<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Asset and route URLs are built from the request host so the app works
        // both locally (http://127.0.0.1:8000) and behind the Cloudflare tunnel
        // (https://...trycloudflare.com). trustProxies(at: '*') in bootstrap/app.php
        // makes Laravel honour X-Forwarded-Host/X-Forwarded-Proto for the tunnel,
        // so the forwarded HTTPS host is used there automatically. Do NOT call
        // URL::forceRootUrl() here — it would pin every URL to one host and break
        // local access (e.g. CSS/JS would be requested from the tunnel domain).
    }
}
