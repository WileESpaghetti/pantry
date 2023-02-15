<?php

declare(strict_types=1);

namespace HtmlBookmarks\Http\Controllers;

use App\Http\Controllers\Controller;
use HtmlBookmarks\Http\Requests\BookmarkFileImportRequest;
use HtmlBookmarks\Jobs\ProcessBookmarks;
use HtmlBookmarks\Models\BookmarkFile;
use HtmlBookmarks\Services\HtmlBookmarkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Psr\Log\LoggerInterface;

class HtmlBookmarkController extends Controller
{
    private LoggerInterface $log;

    public function __construct(LoggerInterface $logger) {
        $this->log = $logger;
            $this->middleware('auth');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  BookmarkFileImportRequest  $request
     * @return RedirectResponse
     *
     * FIXME
     * when serving static files add the content-dispostion attachment and nosniff headers
     *
     * FIXME
     * add some sort of middleware that blocks access to the storage folder by user, or better yet prevents serving
     * that directory entirely
     *
     * FIXME
     * should validate/sanitize original file name for user downloads
     *
     * FIXME
     * should return a view for the file import details
     */
    public function store(BookmarkFileImportRequest $request, HtmlBookmarkService $htmlBookmarkService): RedirectResponse
    {
        $user = Auth::user();

        $bookmarksFile = $request->getFile();

        $metadata = $htmlBookmarkService->store($bookmarksFile, $user); // FIXME Authenticatable != Pantry\User
        if (!$metadata) {
            return redirect()
                ->back()
                ->withErrors('upload', __('htmlbookmarks::upload.failure'));
        }

        ProcessBookmarks::dispatch($metadata->path, $user, null);

        return redirect('bookmarks.files.show')
            ->with('success', __('htmlbookmarks::import.started'));
    }

    public function show(BookmarkFile $file) {
        return view('bookmarks.files.show', compact($file));
    }
}
