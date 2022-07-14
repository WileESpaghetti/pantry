<?php

namespace Larder\Providers;

use Illuminate\Support\ServiceProvider;

class LarderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(EventServiceProvider::class);

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'larder');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishConfig();

    }

    /**
     * @return void
     */
    private function publishConfig() {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('larder.php'),
            ], 'config');
        }
    }
}
