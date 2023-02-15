<?php

declare(strict_types=1);

namespace HtmlBookmarks\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Pantry\Repositories\BookmarkRepository;
use Pantry\Repositories\FolderRepository;
use Pantry\Repositories\TagRepository;
use Pantry\User;
use Psr\Log\LoggerInterface;
use Shaarli\NetscapeBookmarkParser\NetscapeBookmarkParser;
use Throwable;

/*
 * These might not be needed if we have the import process just add new bookmarks and then allow the user to manually
 * clean up later with provided cleanup tools.
 *
 * TODO
 * user will have to approve the import before things get synced. Then bookmarks will be in read-only until sync finishes.
 * This might not be needed if we have imports simply add and then offer audit tools to clean up bookmarks.
 *
 */

/*
 * Import structure
 *
 * FIXME
 * I want to separate out most of this logic and have most of the import handled by a service, but I
 * need to decide how to communicate current progress and success/failure (with data) back to the job
 * from the service.
 *
 * TODO
 * parts of this might be good to be generic in the bookmark handling so that larder imports can be bulk manipulated a similar way
 *
 * TODO
 * saving bookmarks/tags should be in transactions
 *
 * FIXME
 * there was a good article about writing efficient jobs and it mentioned not putting a whole model in the constructor
 * need to see if we benefit from this. Specifically the $this->user
 * - https://mateusguimaraes.com/posts/scaling-laravel
 *
 * TODO
 * not sure if it makes sense to have some sort of service build the job. Advantages is that we can consistently do
 * stuff like creating with the same config values each time
 */

/*
 * Potential errors
 *
 * FIXME
 * folder should be looked up by ID instead of name since we are going to allow duplicate folder names
 *
 * TODO
 * needs to handle script timeouts
 * @see https://stackoverflow.com/questions/16409715/php-set-a-script-timeout-and-call-a-function-to-cleanup-when-time-limit-reached
 * @see https://www.php.net/manual/en/function.register-shutdown-function.php
 *
 * TODO
 * needs to be able to continue where it left off in case of timeouts or crashes
 *
 * TODO
 * handle unix signals and cleanup
 *
 * TODO
 * better business level error handling
 *
 * FIXME
 * keeping all of the imports in memory could be an issue. Probably need to batch
 * and then find an alternate way to check for duplicates. Also need to be wary
 * of a too many variables error. Need to look for other common import/file handling
 * issues and write tests for them.
 *
 * FIXME
 * need to guard against large notification errors. ex. sending a really long email or logs taking a long time to render.
 * I don't think the errors should exceed the file size, but we don't want to repeatedly send MB worth of errors back
 * and forth if we don't have too. Need ways to consolidate and get more details on specific errors to make the job
 * logs/notifications more useful.
 */

/**
 * TODO
 * job progress monitoring
 *
 * TODO
 * add a dry run feature that will run through the bookmark import process, but won't actually persist anything to the database
 *
 * TODO
 * remove bookmark file after successful import
 *
 * TODO
 * use config value for default folder name. This should probably be passed in?
 *
 * TODO
 * user setting to automatically merge exact duplicates on import.
 * This would most effect things where the folder was exported from a place when it was assigned multiple tags
 *
 * FIXME
 * port tests form shaarli parser to make sure that the models match what they expect using their test files
 *
 * FIXME
 * bookmark parsing logs shouldn't be public since they could contain PIM, but they should be available to the user for
 * troubleshooting
 *
 * FIXME
 * what type of data safety needs to occur? Should bookmark text be sanitized?
 *
 * FIXME
 * log messages leak hashed file names or paths. They need to show the user the name of the file as they uploaded it
 *
 * FIXME
 * might make sense to convert certain steps to actions
 *
 * @see https://laravel.com/docs/5.8/queues#class-structure
 */
class ProcessBookmarks implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const defaultFolderName = 'imported';

    private string $fileName;

    private string $folderName;

    private User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(string $fileName, User $user, string $targetFolder = null/*FIXME should be @notnull using null for now until implementing selecting folder is added */)
    {
        $this->fileName = $fileName;
        $this->user = $user;
        $this->folderName = empty($targetFolder) ? self::defaultFolderName : $targetFolder;
    }

    /**
     * Execute the job.
     *
     * Nested folders are flattened into individual tags
     *
     * @param LoggerInterface $logger
     * @param NetscapeBookmarkParser $bookmarkParser
     * @param BookmarkRepository $bookmarkRepository
     * @param FolderRepository $folderRepository
     * @param TagRepository $tagRepository
     */
    public function handle(
        LoggerInterface $logger,
        NetscapeBookmarkParser $bookmarkParser,
        BookmarkRepository $bookmarkRepository,
        FolderRepository $folderRepository,
        TagRepository $tagRepository
    ): void
    {
        // 0. initialization
        $logger->info(__('import.job_start'), ['file' => $this->fileName, 'user' => $this->user->name]);

        //  TODO set job status as 'in progress'

        // 1. get the import folder or create if missing
        $logger->debug(__('import.job_find_folder', ['folder' => $this->folderName]));

        $importFolder = $folderRepository->findOrCreateForUser($this->user, $this->folderName);
        if ($importFolder === null) {
            $e = new Exception(__('import.job_failed_create_folder'));

            $logger->error($e->getMessage());
            $this->fail($e);
            return;
        }

        // 2. get the bookmark file contents
        $logger->debug(__('import.job.bookmark_file.read', ['folder' => $this->folderName]));

        $bookmarkFileContents = Storage::get($this->fileName); // FIXME does this ever throw FileNotFoundException? It did not during a unit test
        if ($bookmarkFileContents === null) {
            $e = new FileNotFoundException(__('import.job_error_file_not_found', ['path' => $this->fileName]));

            $logger->error($e->getMessage(), ['path' => $this->fileName]);
            $this->fail($e);
            return;
        }

        // 3. parse the bookmark file
        $logger->debug(__('import.job.bookmark_file.parse', ['folder' => $this->folderName]));

        $bookmarkData = $bookmarkParser->parseString($bookmarkFileContents);
        $bookmarkCollection = collect($bookmarkData);

        // 4. cleanup bookmark/tag data if needed

        $bookmarkCollection->map(function ($bookmarkData) {
            // update keys
            $bookmarkData = [ // FIXME which version of shaarli parser switched the attribute names?
                'url' => $bookmarkData['uri'],
                'name' => $bookmarkData['title'],
                'description' => $bookmarkData['note'],
                'created_at' => $bookmarkData['time'],
                'public' => $bookmarkData['pub'],
                'tags' => $bookmarkData['tags']
            ];


            $tagNames = empty($bookmarkData['tags']) ? [] : $bookmarkData['tags'];
            return [$bookmarkData, $tagNames];
        })->eachSpread(function($bookmarkData, $tagNames) use ($importFolder, $logger, $bookmarkRepository, $tagRepository) {

            // convert to Bookmark model
            $bookmark = $bookmarkRepository->createForUser($this->user, $bookmarkData);
            if ($bookmark == null) {
                $logger->warning(__('import.job.bookmark.create.fail'), $bookmarkData);
                // TODO add to report statistics

                return null; // skip over bad bookmarks
            }
            $importFolder->bookmarks()->save($bookmark);

            // create tags
            $tags = $tagRepository->upsertForUser($this->user, $tagNames);

            // associate tags
            $bookmark->tags()->saveMany($tags);

            // TODO update stats
        });


        // 5. perform job cleanup

        /*
         * TODO gather import statistics
         * - bookmark import count
         * - tags create count
         * - start time
         * - end time
         * - job duration
         * - errors/warnings
         * - bookmark list?
         * - raw parser logs?
         */

        // 6. notify the user
        // TODO https://laravel.com/docs/8.x/queues#job-events
//        $this->user->notify(new BookmarksImported($counts, $warnings));
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     */
    public function failed(Throwable $exception): void
    {
        // Send user notification of failure, etc...
    }
}
