<?php

namespace App\Jobs;

use App\Bookmark;
use App\Notifications\BookmarksImported;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Shaarli\NetscapeBookmarkParser\NetscapeBookmarkParser;

class ProcessBookmarks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $fileName;

    private $user;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'note', 'uri', 'title', 'pub'
    ];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fileName, $user)
    {
        $this->fileName = $fileName;
        $this->user = $user;
    }

    /**
     * Detect the number of inserted vs updated rows
     *
     * According to MySQL docs, the calculation for "rows affected", which is returned from upsert(), is that each row
     * has a value of 1 if insert, 2 if update, 0 if unmodified. The value of 0 never occurs because the updated_at
     * field is always changed by upsert().
     *
     * @param int $rowsGiven the total number of rows fed to upsert()
     * @param int $rowsAffected the number of rows affected that was returned by upsert()
     * @param int $skipped only use if your data contains rows with duplicate data for the unique key [user_id, uri].
     *
     * $skipped = 2 for
     *     [[ 'user_id' => 1, 'uri' => 'http://www.example.com', 'note' => 'i am original'],
     *      [ 'user_id' => 1, 'uri' => 'http://www.example.com', 'note' => 'i am not'],
     *      [ 'user_id' => 1, 'uri' => 'http://www.example.com', 'note' => 'neither am i']]
     *
     *
     * @see https://dev.mysql.com/doc/refman/5.7/en/insert-on-duplicate.html
     */
    private function calcInsertsAndUpdates($rowsGiven, $rowsAffected, $skipped = 0) {
        $maxAffected = 2 * ($rowsGiven - $skipped);
        $inserts = $maxAffected - $rowsAffected;
        $updates = $rowsGiven - $skipped - $inserts;
        return [
            'inserted' => $inserts,
            'updated' => $updates,
            'skipped' => $skipped,
            'total' => $rowsGiven,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(NetscapeBookmarkParser $bookmarkParser)
    {
        // TODO disable logging from NetscapeBookmarkParser, or integrate with laravel logger, or extend class to have more useful logging
        /*
         * TODO
         * larder imports all bookmarks to a specific tag instead of trying to map them.
         * If a bookmark already exists it should not be moved to the import folder
         */
        $html = Storage::get($this->fileName);
        $bookmarks = $bookmarkParser->parseString($html);

        $warnings = [];
        $seenUriIndexes = [];
        foreach($bookmarks as $i => $bookmarkData) {
            $uri = $bookmarkData['uri'];

            // remove attributes that we do not track
            unset($bookmarkData['icon']);
            unset($bookmarkData['tags']);

            if (isset($seenUriIndexes[$uri])) {
                $firstIndex = $seenUriIndexes[$uri];
                $firstBookmark = $bookmarks[$firstIndex];

                $diff = array_diff($firstBookmark, $bookmarkData);
                $json = json_encode($diff);

                /*
                 * FIXME
                 * to avoid issues with warnings exceeding column length we should check the length of warnings and
                 * fail to process the file if it generates too many warnings. Maybe for a v2 we could split into
                 * multiple jobs.
                 */
                $warnings[] = sprintf(__("skipping duplicate bookmark: %s\n\t%s"), $uri, $json);
                // TODO if we want to ignore the duplicates entirely the we can either unset or create a new array and then continue;
            }

            $seenUriIndexes[$uri] = $i;

            $bookmarkData['user_id'] = $this->user->id;

            $bookmarkData['created_at'] = Carbon::createFromTimestamp($bookmarkData['time']);
            unset($bookmarkData['time']);

            $bookmarks[$i] = $bookmarkData;
        }

        $affectedRows = Bookmark::upsert($bookmarks, ['uri', 'user_id'], ['title', 'note', 'pub']); // FIXME not sure if we want to overwrite data for already existing bookmarks. If we do not update data, would `insertOrIgnore()` be more efficient

        $counts = $this->calcInsertsAndUpdates(count($bookmarks), $affectedRows, count($warnings));
        $warnings = array_splice($warnings, 0, 5);// FIXME truncating warnings due to test file generating more warning data than column length allows

        // TODO https://laravel.com/docs/8.x/queues#job-events
        $this->user->notify(new BookmarksImported($counts, $warnings));

        // TODO remove import file
    }
}
