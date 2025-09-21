<?php

namespace Ersalak\Sms;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/ersalak.php', 'ersalak');

        // Bind SmsClient
        $this->app->singleton(SmsClient::class, function ($app) {
            $config = $app['config']['ersalak'];

            return new SmsClient(
                $config['username'],
                $config['password'],
                $config['base_url'],
                $config['logging']
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ersalak.php' => config_path('ersalak.php'),
        ], 'ersalak-config');
    }
}
