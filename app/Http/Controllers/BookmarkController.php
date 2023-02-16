<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BookmarkStoreRequest;
use App\Http\Requests\BookmarkUpdateRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Pantry\Repositories\BookmarkRepository;
use Pantry\Bookmark;
use Pantry\Repositories\TagRepository;

class BookmarkController extends Controller
{
    const DEFAULT_PAGE_SIZE = 25; // FIXME read from configuration setting
    private BookmarkRepository $bookmarkRepo;
    private TagRepository $tagRepository;

    public function __construct(BookmarkRepository $bookmarkRepo, TagRepository $tagRepository)
    {
        $this->bookmarkRepo = $bookmarkRepo;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|View|ViewFactory
     */
    public function index(): Application|View|ViewFactory
    {
        $user = Auth::user();
        $bookmarks = Bookmark::with('tags')->whereBelongsTo($user)->paginate(self::DEFAULT_PAGE_SIZE);

        return view('bookmarks.index', ['bookmarks' => $bookmarks]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|View|ViewFactory
     */
    public function create(): Application|View|ViewFactory
    {
        return view('bookmarks.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  BookmarkStoreRequest  $request
     * @return RedirectResponse
     */
    public function store(BookmarkStoreRequest $request): RedirectResponse
    {
        $bookmark = $this->bookmarkRepo->createForUser(
            $request->user(),
            $request
                ->safe()
                ->all() // FIXME might not want to use all because we could be including stuff like CSR token or other data that is required by request, but not model
        );

        if (!$bookmark) {
            return back()
                ->withErrors(['save' => __('messages.bookmark.create.fail', ['name' => empty($request->safe(['title'])['title']) ? $request->safe(['url'])['url'] : $request->safe(['title'])['title']])]) // FIXME I think safe returns an array
                ->withInput();
        }

        return redirect('bookmarks')
            ->with('success', __('messages.bookmark.create.success', ['name' => empty($bookmark->title) ? $bookmark->url : $bookmark->title ]));
    }

    /**
     * Display the specified resource.
     *
     * @param Bookmark $bookmark
     * @return \Illuminate\Http\Response
     */
    public function show(Bookmark $bookmark)
    {
        // TODO
    }

    /**
     * Show the form for editing the specified resource.
     *
     * FIXME
     * might want to eagerly fetch tags and folder to save a query
     *
     * @param Bookmark $bookmark
     * @return Application|View|ViewFactory
     */
    public function edit(Bookmark $bookmark)
    {
        return view('bookmarks.edit', compact('bookmark'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BookmarkUpdateRequest  $request
     * @param Bookmark $bookmark
     * @return Application|RedirectResponse|Redirector
     */
    public function update(BookmarkUpdateRequest $request, Bookmark $bookmark): Redirector|RedirectResponse|Application
    {
        $tags = $request->only('tags')['tags'];
        $tags = empty($tags) ? [] : preg_split('/,* +/', $tags, -1, PREG_SPLIT_NO_EMPTY);
        if ($tags === false) {
            throw new \Exception('splitting tags failed unexpectidly');
        }
        $tags = $this->tagRepository->upsertForUser(Auth::user(), $tags);

        $bookmark->tags()->sync($tags->pluck('id')); // FIXME needs transaction
        $bookmark = $this->bookmarkRepo->update($bookmark, $request->safe()->all());
        if (!$bookmark) {
            return back() // FIXME this doesn't properly send errors to the frontend
                ->withErrors(['error' => __('messages.bookmark.update.failed', ['name' => empty($request->safe(['title'])['title']) ? $request->safe(['url'])['url'] : $request->safe(['title'])['title']])])
                ->withInput(); // FIXME $bookmark is null here, might not want to overwrite passed in $bookmark value:w

        }

        return redirect('bookmarks')
            ->with('success', __('messages.bookmark.update.success', ['name' => empty($bookmark->title) ? $bookmark->url : $bookmark->title]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Bookmark $bookmark
     * @return Application|Redirector|RedirectResponse
     */
    public function destroy(Bookmark $bookmark): Redirector|RedirectResponse|Application
    {
        $wasDeleted = $this->bookmarkRepo->delete($bookmark);
        if (!$wasDeleted) {
            return redirect('bookmarks')
                ->with('errors', [__('messages.bookmark.delete.fail', ['name' => empty($bookmark->title) ? $bookmark->url : $bookmark->title])]);
        }

        return redirect('bookmarks')
            ->with('success', __('messages.bookmark.delete.success', ['name' => empty($bookmark->title) ? $bookmark->url : $bookmark->title]));
    }
}
