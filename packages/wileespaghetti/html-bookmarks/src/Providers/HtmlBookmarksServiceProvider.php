<?php

declare(strict_types=1);

namespace HtmlBookmarks\Providers;

use HtmlBookmarks\Services\HtmlBookmarkParserLoggerListener;
use Illuminate\Support\ServiceProvider;
use Shaarli\NetscapeBookmarkParser\NetscapeBookmarkParser;

class HtmlBookmarksServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();

        $this->configureParserBindings();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'htmlbookmarks');
        $this->loadViewsFrom(__DIR__. '/../../resources/views', 'htmlbookmarks');
    }

    public function configureParserBindings(): void
    {
        // FIXME should I use an interface for parser instead of hard-coded implementation?
        $this->app->bind('Shaarli\NetscapeBookmarkParser\NetscapeBookmarkParser', function ($app) {
            $bookmarkLogger = $app->make(HtmlBookmarkParserLoggerListener::class);

            return new NetscapeBookmarkParser([], $bookmarkLogger);
        });
    }
}
