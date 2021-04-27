<?php

namespace App\Jobs;

use App\Bookmark;
use App\Notifications\BookmarksImported;
use App\Tag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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
     * Execute the job.
     *
     * @return void
     */
    public function handle(NetscapeBookmarkParser $bookmarkParser)
    {
        $tags = [];
        $html = Storage::get($this->fileName);
        $bookmarks = $bookmarkParser->parseString($html);

        $bookmarks = array_map(function($bookmarkData) use (&$tags) {
            // remove attributes that we do not track
            unset($bookmarkData['icon']);
//            unset($bookmarkData['tags']);

            $bookmarkData['user_id'] = $this->user->id;

            // Convert from Unix timestamp in microseconds
            $bookmarkData['created_at'] = Carbon::createFromTimestamp($bookmarkData['time']);
            unset($bookmarkData['time']);

            // split tags
            $uri = $bookmarkData['uri'];
            $bookmarkTags = is_array($bookmarkData['tags']) ? $bookmarkData['tags'] : [$bookmarkData['tags']];
            if (!isset($tags[$uri])) {
                $tags[$uri] = [];
            }
            $tags[$uri] = array_merge($tags[$uri], $bookmarkTags);
            unset($bookmarkData['tags']);

            return $bookmarkData;
        } , $bookmarks);

        Bookmark::upsert($bookmarks, ['uri', 'user_id'], ['title', 'note', 'pub']); // FIXME this returns "affectedRows" which on first run is 361 (new items) and 722 (not sure how this is calculated) on second run

        // attempt importing tags FIXME larder imports all bookmarks to a specific tag instead of trying to map them
        // FIXME google bookmarks can have tags that have multiple words, but they get incorrectly parsed as multiple tags
        $usersBookmarks = $this->user->bookmarks;
        $unsavedTags = [];
        foreach($usersBookmarks as $b) {
            if (!isset($tags[$b->uri])) {
                continue;
            }

            $currentTags = $tags[$b->uri];
            $tagData = array_map(function($tag) use ($b) {
                return [
                    'tag' => $tag,
                    'user_id' => $this->user->id,
                    'bookmark_id' => $b->id
                ];

            }, $currentTags);

            foreach($tagData as $data) {
                $unsavedTags[] = $data;

            }
        }
        Tag::insertOrIgnore($unsavedTags);

        $importedCount = count($bookmarks); // FIXME this is how many bookmarks are in the file, but needs to be updated/new/error/duplicated counts
        $this->user->notify(new BookmarksImported($importedCount));
    }
}
