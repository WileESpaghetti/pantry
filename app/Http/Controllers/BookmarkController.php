<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BookmarkStoreRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Pantry\Bookmark;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|ViewFactory|View
     */
    public function index(): Application|ViewFactory|View
    {
        $bookmarks = Bookmark::paginate(100);

        return view('bookmarks.index', ['notifications' => Collection::empty(), 'bookmarks' => $bookmarks]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|ViewFactory|View
     */
    public function create(): Application|ViewFactory|View
    {
        return view('bookmarks.create', ['notifications' => Collection::empty()]);
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
                $fileName = $bookmarksFile->hashName();
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
