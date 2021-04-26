<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBookmarks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $fieldName = 'bookmark';
        if ($request->hasFile($fieldName)) {
            if ($request->file($fieldName)->isValid()) {
                $bookmarksFile = $request->file($fieldName);
                $fileName = $bookmarksFile->hashName(); // TODO use tempnam()
                $f = $bookmarksFile->storeAs('/public', $fileName);

                ProcessBookmarks::dispatch($f, $user);


                return redirect()->back()->with('success', __('Import has started. Please allow some time for it to finish.'));
            } else {
                return redirect()->back()->with('error', __('Failed to upload bookmarks file'));
            }
        }

        return redirect()->back()->with('error', __('Failed to upload bookmarks file'));
    }
}
