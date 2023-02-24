<?php

declare(strict_types=1);

namespace HtmlBookmarks\Http\Controllers;

use App\Http\Controllers\Controller;
use HtmlBookmarks\Http\Requests\BookmarkFileImportRequest;
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
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BookmarkFileImportRequest $request
     * @param HtmlBookmarkService $htmlBookmarkService
     * @return RedirectResponse
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

        return redirect()
            ->route('imports.index')
            ->with('success', __('htmlbookmarks::import.started'));
    }

    public function show(BookmarkFile $file) {
        return view('bookmarks.files.show', compact($file));
    }
}
