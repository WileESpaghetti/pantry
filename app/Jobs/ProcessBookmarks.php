<?php

namespace App\Jobs;

use App\Bookmark;
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
        $html = Storage::get($this->fileName);
        $bookmarks = $bookmarkParser->parseString($html);

        $bookmarks = array_map(function($bookmarkData) {
            // remove attributes that we do not track
            unset($bookmarkData['icon']);
            unset($bookmarkData['tags']); // TODO Consolidate tags and store in separate table

            $bookmarkData['user_id'] = $this->user->id;

            // Convert from Unix timestamp in microseconds
            $bookmarkData['created_at'] = Carbon::createFromTimestamp(trim($bookmarkData['time']/* / 1000*/ )); // FIXME Google Bookmarks is in microseconds and others might be different
            unset($bookmarkData['time']);

            return $bookmarkData;
        }, $bookmarks);

        Bookmark::upsert($bookmarks, ['uri', 'user_id'], ['title', 'note', 'pub']);
    }
}
