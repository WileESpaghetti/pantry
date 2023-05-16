<?php

declare(strict_types=1);

namespace HtmlBookmarks\Jobs;

use Exception;
use HtmlBookmarks\Models\BookmarkFile;
use HtmlBookmarks\Services\ImportJobService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pantry\Models\Bookmark;
use Pantry\Models\Folder;
use Pantry\Repositories\BookmarkRepository;
use Pantry\Repositories\FolderRepository;
use Pantry\Repositories\TagRepository;
use Pantry\User;
use Psr\Log\LoggerInterface;
use Shaarli\NetscapeBookmarkParser\NetscapeBookmarkParser;
use Throwable;
use function hash as createHash;

/*
 * Potential errors
 *
 * FIXME
 * keeping all of the imports in memory could be an issue. Probably need to batch
 * and then find an alternate way to check for duplicates. Also need to be wary
 * of a too many variables error. Need to look for other common import/file handling
 * issues and write tests for them.
 */

/**
 * ## CODE CLEANUP ##
 *
 * FIXME
 * more consistent use of arrays vs collections
 *
 * FIXME
 * I want to separate out most of this logic and have most of the import handled by a service, but I
 * need to decide how to communicate current progress and success/failure (with data) back to the job
 * from the service.
 *
 * TODO
 * replace Helpers with DI equivalents (ex. Storage)
 *
 * FIXME
 * folder should be looked up by ID instead of name since we are going to allow duplicate folder names
 *
 * ## TESTING ##
 *
 * TODO
 * need to stress test to find a good chunk sizes
 *
 *
 * ## QUESTIONS ##
 *
 * TODO
 * is there an advantage to using API Resource objects instead?
 *
 * TODO
 * is there better/built in way to detect equality. what about the `is` and `isNot` functions?
 * see: https://laravel.com/docs/10.x/eloquent#comparing-models
 *
 * FIXME
 * Pipelines/Actions: Should the code be be set up to use pipelines/actions?
 *
 * TODO
 * is there any benefit to using lazy collections?
 *
 * FIXME
 * need to do some testing with tags that contain spaces. Bookmark parser will allow them if a header has a comma,
 * or if one of the tags fields has a comma. Will need to normalize if Larder doesn't allow them.
 *
 * TODO
 * not sure if it makes sense to have some sort of service build the job. Advantages is that we can consistently do
 * stuff like creating with the same config values each time
 *
 * TODO
 * parts of this might be good to be generic in the bookmark handling so that larder imports can be bulk manipulated a
 * similar way
 *
 * TODO
 * Identify what needs to be in a transaction
 *
 * FIXME
 * Am I doing the right thing by manually failing the job on certain errors? Fail usually happens after max retries
 *
 * TODO
 * @see https://laravel.com/docs/5.8/queues#class-structure
 *
 * TODO
 * does insert() actually do a bulk operation or just avoid all of the safety checks of create()
 *
 * TODO
 * is upsert() actually a bulk operation?
 *
 *
 * ## SETTINGS ##
 *
 * TODO
 * use config value for default folder name. This should probably be passed in?
 *
 * TODO
 * user setting to automatically merge exact duplicates on import.
 * This would most effect things where the folder was exported from a place when it was assigned multiple tags
 *
 * FIXME
 * the splitting of folder names into multiple tags is annoying and we need to just hyphenate instead. Is there a
 * config option to do this?
 *
 *
 *
 * ## UNIT TESTS ##
 *
 * TODO
 * unit test when no bookmarks are tagged
 *
 * TODO
 * unit test when no new tags
 *
 * TODO
 * unit test when only new tags
 *
 * FIXME
 * port tests from shaarli parser to make sure that the models match what they expect using their test files
 *
 *
 *
 * ## REMAINING TASKS ##
 *
 * TODO
 * Job cleanup
 * @see https://laravel.com/docs/7.x/queues#cleaning-up-after-failed-jobs
 *
 * TODO
 * remove bookmark file after successful import
 *
 * FIXME
 * bookmark parsing logs shouldn't be public since they could contain PIM, but they should be available to the user for
 * troubleshooting
 *
 * FIXME
 * log messages leak hashed file names or paths. They need to show the user the name of the file as they uploaded it
 *
 * TODO
 * handle unix signals and cleanup
 *
 * TODO
 * needs to handle script timeouts
 * @see https://stackoverflow.com/questions/16409715/php-set-a-script-timeout-and-call-a-function-to-cleanup-when-time-limit-reached
 * @see https://www.php.net/manual/en/function.register-shutdown-function.php
 * @see https://laravel.com/docs/5.5/queues#job-expirations-and-timeouts
 * @see https://divinglaravel.com/explaining-laravel-queue-configuration-keys
 * @see https://github.com/laravel/framework/discussions/33473
 *
 * TODO
 * needs to be able to continue where it left off in case of timeouts, crashes, or errors
 * continuing where it left off might not make sense because we have to re-read the file from the beginning anyway.
 * might want to store the comparison key or make the import key more predictable to know if a bookmark has already been
 * saved. if I use a more predicatable import_sequence then I should be able to detect if a bookmark has already been imported
 * and filter it out if needed. I would need to account for if the merge duplicate setting changes though
 *
 * TODO
 * better business level error handling
 *
 * TODO
 * use gates to protect the file permissions
 *
 * FIXME
 * Bookmark insert :: SQLSTATE[HY000]: General error: 1390 Prepared statement contains too many placeholders
 * not sure if there is a portable way to detect this, but some googling suggests that it is around 65K for MySQL.
 * Could use this value for deciding collection chunking size.
 *
 * TODO
 * notifications
 * @see https://laravel.com/docs/8.x/queues#job-events
 *
 * TODO
 * actually save to a folder
 *
 * FIXME
 * do not split folder names by spaces. will probably need to override decoder::splitTagString
 *
 *
 *
 *
 * ## IDEAS ##
 *
 * TODO
 * might be able to extend NetscapeBookmarkParser to be streamable by getting line by line and parsingString then
 * listening to the logger and parsing log messages
 *
 * TODO
 * turn NetscapeParserDecoder in new Shaarli to be a generator and yeild bookmarks as they are written.
 * Do we need to resort to composer, or laravel DI magic to get this to work?
 *
 * TODO
 * streaming the file might not be possible because of the sanitization functions which would need to be able to check
 * multiple lines at once. (ex. making one line per tag) So if we wanted to implement this we would need to sanitize/save
 * the files first so we could ignore that step on decoding.
 *
 * TODO
 * perhaps bookmark transformers could be autodiscovered/loaded in a similar manner as Laravel providers
 *
 * TODO
 * Performance can be improved by switching to `Bookmark::insert()`. Will need a way to filter out non-fillable
 * attributes and fetch the ID's of inserted items since `insert` only returns boolean. Additionally we
 * probably will want a way to trigger the normal create events. We will also need to keep in mind error
 * handling for bookmarks that fail to save. Bookmark errors will no doubt give us complications in finding out
 * what the existing IDs are. Failed bookmark imports should be reported to the user, but should not hold up
 * other bookmarks from being imported.
 *
 * FIXME
 * need to find a way to fire events for bulk inserts (this should really be handled by the service/model)
 * @see https://github.com/lapaliv/laravel-bulk-upsert
 *
 * FIXME
 * would kind of be nice to get stats on average number of tags/bookmarks/file size/run time/other stuff
 *
 * FIXME
 * would be nice if we could get the original line numbers and use that as an index instead
 *
 */
class ProcessBookmarks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // UTILS
    private LoggerInterface $log;
    private FolderRepository $folderRepository;
    private TagRepository $tagRepository;
    private DatabaseManager $db;
    private Carbon $carbon;

    // USER SETTINGS
    private bool $shouldMergeDuplicates;
    private const defaultFolderName = 'imported';

    // JOB DATA
    private int $importId;
    private User $user;
    private string $folderName;

    // IMPORT DATA
    private ImportJobService $import;
    private BookmarkFile $file;
    private string $fileName;


    /**
     * Create a new job instance.
     */
    public function __construct(
        int $importId,
        User $user,
        string $settingTargetFolder = null, // FIXME should be @notnull using null for now until implementing selecting folder is added
        bool $settingShouldMergeDuplicates = true
    )
    {
        $this->importId = $importId;
        $this->user = $user;
        $this->folderName = empty($settingTargetFolder) ? self::defaultFolderName : $settingTargetFolder;
        $this->shouldMergeDuplicates = $settingShouldMergeDuplicates;
    }


    /**
     * FIXME this should be in a service
     * create a hash of bookmark data to easily compare if data is equivalent
     * @param array $bookmarkData
     * @return string
     */
    private function getDataHash(array $bookmarkData): string {
        $content = $bookmarkData['url'] . $bookmarkData['name'] . $bookmarkData['description'];
        return createHash('sha256', $content);
    }

    private function getImportFolder(): ?Folder {
        $importFolder = $this->folderRepository->findOrCreateForUser($this->user, $this->folderName);
        if ($importFolder === null) { // FIXME can this just be `!$importFolder` or empty()?
            $e = new Exception(__('import.job_failed_create_folder'));

            $this->log->error($e->getMessage());
            $this->fail($e);
            return null;
        }

        return $importFolder;
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
        ImportJobService $importJobService,
        BookmarkRepository $bookmarkRepository,
        FolderRepository $folderRepository,
        TagRepository $tagRepository,
        DatabaseManager $databaseManager,
        Carbon $carbon
    ): void
    {
        $this->log = $logger;
        $this->db = $databaseManager;
        $this->carbon = $carbon;

        $this->folderRepository = $folderRepository;
        $this->tagRepository = $tagRepository;
        $this->import = $importJobService;

        // 0. initialization
        // FIXME needs to handle errors and fail if import can not be found or updated. ...maybe just throw import warning if state can not be updated
        $logger->debug(__('import.job_init', ['folder' => $this->folderName]));

        $import = $this->import->get($this->importId); // FIXME handle exception
        $import = $this->import->initialize($import);
        $import = $this->import->start($import);

        // FIXME not sure if I want some file validation here. (ex. gates/file permissions)

        // 1. get the import folder or create if missing // FIXME might move this step later, because there is no reason to create a folder and keep that in memory if we don't have any bookmarks to save
        $logger->debug(__('import.job_find_folder', ['folder' => $this->folderName]));

        $importFolder = $this->getImportFolder();
        if (!$importFolder) {
            $e = new Exception(__('could not find or create the import folder'));

            $this->log->error($e->getMessage(), ['folder' => /*FIXME*/'']);

            throw $e;
        }

        // 2. get the bookmark file contents
        $this->file = $import->bookmarkFile;
        $this->fileName = $this->file->path;

        $logger->debug(__('import.job.bookmark_file_found', [
            'bookmark_file_meta' => $this->file->id,
            'path' => $this->fileName,
            'folder' => $this->folderName,
        ]));

        // 3. get the bookmark file contents
        $logger->debug(__('import.job.bookmark_file.read', ['folder' => $this->folderName]));

        $bookmarkFileContents = Storage::get($this->fileName);
        if ($bookmarkFileContents === null) {
            // handle file not found or empty content
            $e = new FileNotFoundException(__('import.job_error_file_not_found', ['path' => $this->fileName]));

            $logger->error($e->getMessage(), ['path' => $this->fileName]);

            throw $e;
        }

        // 4. parse the bookmark file
        $logger->debug(__('import.job.bookmark_file.parse', ['folder' => $this->folderName]));

        try {
            $bookmarkData = $bookmarkParser->parseString($bookmarkFileContents);
            if (!count($bookmarkData)) {
                $this->log->info(__('import.job_no_data'));
                $this->cleanup();
                return;
            }
        } catch (Throwable $e) { // FIXME what conditions causes this to happen?
            $this->log->error(__('import.job_parse_failed'), ['error' => $e->getMessage()]);

            throw $e;
        }

        $logger->debug(__('finished parsing bookmarks. Detected :bookmark_count new bookmarks', ['bookmark_count' => count($bookmarkData)]));

        // 5. cleanup bookmark/tag data if needed
        $logger->debug(__('import.job_bookmark_transform'));

        $bookmarkCollection = collect($bookmarkData);
        $this->log->debug(__("discovered :count bookmarks", ['count' => $bookmarkCollection->count()]));

        // Transform bookmarks

        list(
            $importKeyToBookmark,
            $bookmarksByComparisonKey,
            $tagNameToImportKeys,
            $tagNamesByComparisonKey
        ) = $bookmarkCollection->reduceSpread(function (
            Collection $importKeyToBookmark,
            Collection $comparisonKeyToImportKeys,
            Collection $tagNameToImportKeys,
            Collection $comparisonKeyToTagNames,
            array      $bookmarkData
        ) use ($import) {
            // TODO sanitize/validate bookmark data?
            $this->log->debug('processing bookmark', $bookmarkData);

            /*
             * used to tie the original bookmark data back to the model that is saved to the database
             *
             * When doing a bulk insert we are unable to recover which ID corresponds to which bookmark data.
             * We can not rely on index value because these transformations may reorganize or remove data. The
             * database might not be guaranteed to be saved in the same order as given.
             *
             * this is commonly referred to as a "sequence number" should we switch to that parlance?
             */
            $importKey = (string) Str::uuid();


            $comparisonKey = $this->getDataHash($bookmarkData); // rough equality detection between bookmarks that we might want to merge later
            $isDuplicate = $comparisonKeyToImportKeys->has($comparisonKey);
            $shouldSkip = $isDuplicate && $this->shouldMergeDuplicates;


            // normalize tag data
            if (!empty($bookmarkData['tags'])) {
                // FIXME dedupe tags here or later?
                $this->log->debug(__('normalizing tags and removing duplicates'), ['tags' => $bookmarkData['tags']]);

                $tags = collect($bookmarkData['tags'])
                    ->map(fn($tagName) => $this->tagRepository->normalizeTagName($tagName))
                    ->all();

                $this->log->debug(__('tags have been normalized'), ['tags' => $tags]);

                $comparisonKeyToTagNames = $comparisonKeyToTagNames->mergeRecursive([$comparisonKey => $bookmarkData['tags']]); // tag names for merged bookmarks // FIXME might contain duplicate tags

                if ($shouldSkip) {
                    $this->log->debug('duplicate bookmark detected. Tags will be merged');

                    $this->log->info('duplicate bookmark detected, skipping extra processing');
                    return [$importKeyToBookmark, $comparisonKeyToImportKeys, $tagNameToImportKeys, $comparisonKeyToTagNames];
                }

                $tagNameToImportKeys = $tagNameToImportKeys->mergeRecursive(array_fill_keys($bookmarkData['tags'], [$importKey])); // tag names for unmerged bookmarks // FIXME check to see if this dedups keys
            }

            // normalize bookmark data
            $bookmarkData = $this->import->transformKeys($bookmarkData, [
                'user_id' => $this->user->id,
                'import_id' => $import->id, // needed so we can query the saved bookmarks later
                'comparison_key' => $comparisonKey, // not persisted
                'is_duplicate' => $isDuplicate,     // not persisted
                'import_sequence' => $importKey,
            ]);

            $importKeyToBookmark = $importKeyToBookmark->put($importKey, $bookmarkData); // rebuild the collection with the changed data
            $comparisonKeyToImportKeys = $comparisonKeyToImportKeys->mergeRecursive([$comparisonKey => [$importKey]]); // duplicate detection

            return [$importKeyToBookmark, $comparisonKeyToImportKeys, $tagNameToImportKeys, $comparisonKeyToTagNames];
        }, $importKeyToBookmark = collect(), $comparisonKeyToImportKeys = collect(), $tagNameToImportKeys = collect(), $comparisonKeyToTagNames = collect());

        // 6. Create missing tags
        // FIXME this setting might just be better to do upsert and then fetch.
        // FIXME Maybe switch to upsert if a certain number of tags?
        // FIXME or maybe use an insert with a subselect to filter out the existing ones
        // FIXME is upsert bulk a bulk operation?

        $this->log->debug(__('import.job_create_tags'));

        // TODO needs to check if this step actually needs to run (ex. when no bookmarks have tags)
        // FIXME this whole section could probably be an insert with select
        $discoveredTagsNames = $tagNameToImportKeys->keys();
        $existingTagsByName = $this->tagRepository->getTagsByNames($this->user, $discoveredTagsNames)->keyBy('name');
        $newTagsByTagName = $tagNameToImportKeys->diffKeys($existingTagsByName)
            ->keys()
            ->map(fn($tagName) => ['name' => $tagName])
            ->pipe(function($newTagNames) {
                return $this->tagRepository->createManyForUser($this->user, $newTagNames->all()); // FIXME this needs to be a batch insert
            })->keyBy('name')
            ->all();

        $tagNamesToTag = $existingTagsByName->mergeRecursive($newTagsByTagName); // FIXME or do I want to just refetch all the user's tags?

        $this->log->debug(__('import.job_tags_created'), ['tags' => $newTagsByTagName/*->keys()->all()*/]);

        // 7. Save bookmarks
        // FIXME this should probably be in BookmarkRepository
        $bookmarkData = $importKeyToBookmark->values()
            ->filter(function($bookmark) {// FIXME filtering might not be needed anymore
            $shouldInclude = !($this->shouldMergeDuplicates && $bookmark['is_duplicate']);
            if (!$shouldInclude) {
                $this->log->info(__('import.job_skip_bookmark'), [
                    'comparison_key' => $bookmark['comparison_key'],
                    'import_sequence' => $bookmark['import_sequence'],
                ]);
            }

            return $shouldInclude;
        });

        // format into actual model data
        // FIXME this should be in BookmarkRepository
        $modifiedAt = $this->carbon->now()->toDateTimeString(); // FIXME not sure if toDateTimeString() is needed
        $bookmarkSaveData = $bookmarkData->map(function ($bookmarkData) use ($modifiedAt) {
            return [
                'name' => $bookmarkData['name'],
                'url' => $bookmarkData['url'],
                'description' => $bookmarkData['description'],
                'public' => 0,
                'created_at' => !empty($bookmarkData['created_at']) ? $bookmarkData['created_at'] : $modifiedAt,
                'updated_at' => $modifiedAt,
                'import_sequence' => $bookmarkData['import_sequence'],
                'import_id' => $bookmarkData['import_id'],
                'user_id' => $bookmarkData['user_id'],
            ];
        });

        Bookmark::insert($bookmarkSaveData->all()); // FIXME handle errors / put in transaction / should be in repository/service and give option to return the ids

        // Add tags to bookmarks
        $importKeyToId = Bookmark::where('import_id', $import->id)
            ->pluck('id', 'import_sequence');

//        $this->logger->debug($tagNamesToTag);
        $bookmarkData2 = $importKeyToBookmark
            ->filter(function($bookmark) { // FIXME filtering might not be needed anymore
                $shouldInclude = !($this->shouldMergeDuplicates && $bookmark['is_duplicate']);
                if (!$shouldInclude) {
                    $this->log->info(__('import.job_skip_bookmark'), [
                        'comparison_key' => $bookmark['comparison_key'],
                        'import_sequence' => $bookmark['import_sequence'],
                    ]);
                }

                return $shouldInclude;
            }); // FIXME duplicate code should be calculated elsewhere

        $bookmarkTagRecords = array_merge(...$tagNameToImportKeys
            ->mapWithKeys(function ($importKeys, $tagName) use ($tagNamesToTag, $importKeyToId, $bookmarkData2) {
//                $this->logger->debug("TagName: $tagName");
//                $this->logger->debug($tagNamesToTag->get($tagName));
                $tagId = $tagNamesToTag->get($tagName)->id; // since we just saved $tagNamesToTag without errors we shouldn't have to worry about `get()` returning null

                // filter out duplicates // FIXME this might not be needed anymore
                if ($this->shouldMergeDuplicates) {
                    $importKeys = collect($importKeys)
                        ->filter(fn($importKey) => !empty($bookmarkData2[$importKey]))
                        ->all();
                }

                $bookmarkTagRecords = array_map(fn($importKey) => ['tag_id' => $tagId, 'bookmark_id' => $importKeyToId[$importKey]], $importKeys);
                return [$tagName => $bookmarkTagRecords];
            })
            ->values()
            ->all());

        $this->db->table('bookmark_tag')->insert($bookmarkTagRecords); // FIXME handle errors / move to service


        // 7. perform job cleanup

        /*
         * TODO gather import statistics
         * - bookmark import count
         * - bookmarks combines
         * - [X] tags create count
         * - [X] start time
         * - [X] end time
         * - job duration
         * - errors/warnings
         * - bookmark list?
         * - raw parser logs?
         * - [X] file meta
         */

        $importStats = []; // FIXME fill out data
        $this->import->end($import, $importStats);

        // 8. notify the user
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
        // FIXME this might only be called after the max retries
        // Send user notification of failure, etc...
        // set import status as failed
        // update any debug stats to help troubleshoot why the import failed
//        $this->user->notify(new BookmarksImported($counts, $warnings));
    }

    /**
     * Terminate the job successfully and clean up
     * @return void
     */
    private function cleanup(): void {
    }
}
