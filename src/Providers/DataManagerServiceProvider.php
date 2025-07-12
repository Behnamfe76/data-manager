<?php

namespace DataManager\Providers;

use Illuminate\Support\ServiceProvider;

class DataManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Merge package config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/data-manager.php', 'data-manager'
        );

        // Bind main DataManager service (placeholder)
        // $this->app->singleton('data-manager', function ($app) {
        //     return new \DataManager\Core\DataManager();
        // });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Publish config (manual path for compatibility)
        $this->publishes([
            __DIR__ . '/../../config/data-manager.php' => $this->app->basePath() . '/config/data-manager.php',
        ], 'config');
    }
} 