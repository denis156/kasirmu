<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Midtrans\Config;

class MidtransServiceProviders extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('midtrans', function () {
            return new class {
                public function getServerKey(): string
                {
                    return Setting::get('midtrans_server_key', '');
                }

                public function getClientKey(): string
                {
                    return Setting::get('midtrans_client_key', '');
                }

                public function getMerchantId(): string
                {
                    return Setting::get('midtrans_merchant_id', '');
                }

                public function isProduction(): bool
                {
                    return (bool) Setting::get('midtrans_is_production', false);
                }

                public function getSnapUrl(): string
                {
                    return $this->isProduction() 
                        ? 'https://app.midtrans.com/snap/snap.js'
                        : 'https://app.sandbox.midtrans.com/snap/snap.js';
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configure Midtrans using closure to ensure it runs when needed
        $this->app->resolving('midtrans', function () {
            $midtrans = app('midtrans');
            
            Config::$serverKey = $midtrans->getServerKey();
            Config::$isProduction = $midtrans->isProduction();
            Config::$isSanitized = true;
            Config::$is3ds = true;
        });
    }
}
