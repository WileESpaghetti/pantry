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
//        dd('imloaded');
    }
}
