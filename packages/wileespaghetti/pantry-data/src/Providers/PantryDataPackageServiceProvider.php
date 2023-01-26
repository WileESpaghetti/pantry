<?php

namespace Pantry\Providers;

use Illuminate\Support\ServiceProvider;

class PantryDataPackageServiceProvider extends ServiceProvider {
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'pantry');
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('pantry.php'),
            ], 'config');

        }

    }
}
