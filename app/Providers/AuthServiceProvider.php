<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        /**
         * TODO
         * use autodiscovery instead - https://laravel.com/docs/9.x/authorization#policy-auto-discovery
         */
        'Pantry\Bookmark' => 'Pantry\Policies\BookmarkPolicy',
        'Pantry\Folder' => 'Pantry\Policies\FolderPolicy',
        'Pantry\Tag' => 'Pantry\Policies\TagPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
