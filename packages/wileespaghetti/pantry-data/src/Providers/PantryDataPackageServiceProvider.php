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
        // TODO test routes exist as part of testing controllers
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'pantry');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('pantry.php'),
            ], 'config');

        }

    }
}
