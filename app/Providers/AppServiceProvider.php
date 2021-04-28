<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Larder\Provider as LarderProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        $this->bootLarderSocialite();
        Paginator::useBootstrap();
    }

//    private function bootLarderSocialite()
//    {
//        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
//        $socialite->extend(
//            'larder',
//            function ($app) use ($socialite) {
//                $config = $app['config']['services.larder'];
//                return $socialite->buildProvider(LarderProvider::class, $config);
//            }
//        );
//    }
}
