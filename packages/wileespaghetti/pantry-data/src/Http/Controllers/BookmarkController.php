<?php
declare(strict_types=1);

namespace Pantry\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Pantry\Http\Requests\BookmarkStoreRequest;
use Pantry\Http\Requests\BookmarkUpdateRequest;
use Pantry\Models\Bookmark;
use Pantry\Repositories\BookmarkRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

class BookmarkController extends Controller
{
    private BookmarkRepository $bookmarkRepo;
    private LoggerInterface $log;

    public function __construct(BookmarkRepository $bookmarkRepo, LoggerInterface $logger)
    {
        $this->bookmarkRepo = $bookmarkRepo;
        $this->log = $logger;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Application|View|ViewFactory
    {
        $bookmarks = $this->bookmarkRepo->getAll();

        return view('bookmarks.index', ['bookmarks' => $bookmarks]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Application|View|ViewFactory
    {
        return view('bookmarks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookmarkStoreRequest $request): RedirectResponse
    {
        $bookmarkData = $request->safe()->all();

        try {
            $bookmark = $this->bookmarkRepo->createOrFail($bookmarkData);
        } catch (Throwable $e) {
            $this->log->error(__('messages.bookmark.create.fail'), [
                'error' => $e->getMessage(),
                'bookmark' => $request->safe()->all()
            ]);

            return to_route('bookmarks.create')
                ->withErrors([
                    'save' => __('messages.bookmark.create.fail'),
                    'error' => $e->getMessage()
                ])
                ->withInput();
        }

        $displayName = !empty($bookmark->title) ? $bookmark->title : $bookmark->url;
        return redirect('bookmarks')
            ->with('success', __('messages.bookmark.create.success', ['name' => $displayName]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bookmark $bookmark): Response
    {
        throw new ServiceUnavailableHttpException();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bookmark $bookmark): Application|View|ViewFactory
    {
        return view('bookmarks.edit', compact('bookmark'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookmarkUpdateRequest $request, Bookmark $bookmark): RedirectResponse
    {
        try{
            $bookmark = $this->bookmarkRepo->update($bookmark, $request->safe()->all());
        } catch (Throwable $e){
            $this->log->error(__('messages.bookmark.update.fail'), [
                'error' => $e->getMessage(),
                'bookmark' => $request->safe()->all()
            ]);

            return to_route('bookmarks.edit', [$bookmark])
                ->withErrors([
                    'save' => __('messages.bookmark.update.failed'),
                    'error' => $e->getMessage()
                ])
                ->withInput();
        }

        $displayName = !empty($bookmark->title) ? $bookmark->title : $bookmark->url;
        return redirect('bookmarks')
            ->with('success', __('messages.bookmark.update.success', [
                'name' => $displayName
                // TODO stats about new tags
            ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bookmark $bookmark): RedirectResponse
    {
        $displayName = !empty($bookmark->title) ? $bookmark->title : $bookmark->url;

        try {
            $wasDeleted = $this->bookmarkRepo->delete($bookmark);
        } catch (Throwable $e) {
            return redirect('bookmarks')
                ->withErrors([
                    'delete' => __('messages.bookmark.delete.fail', ['name' => $displayName]),
                    'error' => $e->getMessage()
                ]);
        }

        return redirect('bookmarks')
            ->with('success', __('messages.bookmark.delete.success', ['name' => $displayName]));
    }
}
