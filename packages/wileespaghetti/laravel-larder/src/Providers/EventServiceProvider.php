<?php

namespace Larder\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Larder\Observers\FolderObserver;
use Pantry\Folder;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        SocialiteWasCalled::class => [
            'SocialiteProviders\\Larder\\LarderExtendSocialite@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // FIXME have this be a toggleable user/app setting
        Folder::observe(FolderObserver::class);
//        Tag::observe(TagObserver::class);
//        Bookmark::observe(BookmarkObserver::class);
    }
}
