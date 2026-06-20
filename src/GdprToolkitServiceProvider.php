<?php

namespace Kartinov\GdprToolkit;

use Illuminate\Support\ServiceProvider;

class GdprToolkitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge default config so app can override
        $this->mergeConfigFrom(
            __DIR__.'/../config/gdpr-toolkit.php',
            'gdpr-toolkit'
        );

        // Bind main manager
        $this->app->singleton('gdpr-toolkit', function ($app) {
            return new GdprToolkitManager;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Allow publishing config to host app
        $this->publishes([
            __DIR__.'/../config/gdpr-toolkit.php' => config_path('gdpr-toolkit.php'),
        ], 'gdpr-toolkit-config');

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ScanCommand::class,
            ]);
        }
    }
}
