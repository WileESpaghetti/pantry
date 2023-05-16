<?php

declare(strict_types=1);

namespace HtmlBookmarks\Http\Controllers;

use HtmlBookmarks\Http\Requests\BookmarkFileImportRequest;
use HtmlBookmarks\Models\BookmarkFile;
use HtmlBookmarks\Services\HtmlBookmarkService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class HtmlBookmarkController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param BookmarkFileImportRequest $request
     * @param HtmlBookmarkService $htmlBookmarkService
     * @return RedirectResponse
     *
     * FIXME
     * for some reason after submitting a bookmark if you try to submit another one without reloading the page
     * (ie: while the success messages are still showing) then the queue gives you a failure max retries error when
     * running. This might be because it is trying to requeue the job and it has the unique trait.
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

    public function show(BookmarkFile $file): View|Factory|Application
    {
        return view('bookmarks.files.show', compact($file));
    }
}
