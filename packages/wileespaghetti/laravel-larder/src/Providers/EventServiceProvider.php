<?php

namespace Larder\Providers;

use App\Bookmark;
use App\Folder;
use App\Tag;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Larder\Observers\BookmarkObserver;
use Larder\Observers\FolderObserver;
use Larder\Observers\TagObserver;
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
    }
}
