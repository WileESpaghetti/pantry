<?php

declare(strict_types=1);

namespace Pantry\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Pantry\Http\Requests\FolderStoreRequest;
use Pantry\Http\Requests\FolderUpdateRequest;
use Pantry\Models\Folder;
use Pantry\Repositories\BookmarkRepository;
use Pantry\Repositories\FolderRepository;
use Psr\Log\LoggerInterface;
use Throwable;

class FolderController extends Controller
{
    private AuthManager $auth;
    private LoggerInterface $log;
    private BookmarkRepository $bookmarksRepo;
    private FolderRepository $folderRepo;

    public function __construct(BookmarkRepository $bookmarksRepo, FolderRepository $folderRepo, AuthManager $auth, LoggerInterface $logger)
    {
        $this->auth = $auth;
        $this->log = $logger;
        $this->bookmarksRepo = $bookmarksRepo;
        $this->folderRepo = $folderRepo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Application|View|ViewFactory
    {
        $folders = $this->folderRepo->getAll();

        return view('folders.index', ['folders' => $folders]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Application|View|ViewFactory
    {
        return view('folders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FolderStoreRequest $request): RedirectResponse
    {
        $folderData = $request ->safe() ->all();

        try {
            $folder = $this->folderRepo->createOrFail($folderData);
        } catch (Throwable $e) {
            $this->log->error(__('messages.folder.create.fail'), [
                'error' => $e->getMessage(),
                'folder' => $request->safe()->all()
            ]);

            return to_route('folders.create')
                ->withErrors([
                    'save' => __('messages.folder.create.fail'),
                    'error' => $e->getMessage()
                ])
                ->withInput();
        }

        return redirect('folders')
            ->with('success', __('messages.folder.create.success', ['name' => $folder->name]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Folder $folder): Application|View|ViewFactory
    {
        $bookmarks = $this->bookmarksRepo->getAllByFolder($folder);

        return view('bookmarks.index', ['isContainer' => true, 'bookmarks' => $bookmarks, 'folder' => $folder]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Folder $folder): Application|View|ViewFactory
    {
        return view('folders.edit', compact('folder'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FolderUpdateRequest $request, Folder $folder): Redirector|RedirectResponse|Application
    {
        try {
            $folder = $this->folderRepo->update($folder, $request->safe()->all());
        } catch (Throwable $e) {
            return to_route('folders.edit', [$folder])
                ->withErrors([
                    'save' => __('messages.folder.update.failed', ['name' => $request->safe('name')['name']]),
                    'error' => $e->getMessage()
                ])
                ->withInput();
        }

        return redirect('folders')
            ->with('success', __('messages.folder.update.success', ['name' => $folder->name]));
    }

    /**
     * Remove the specified resource from storage.
     * Should delete bookmarks in the folder or move the bookmarks to the specified folder
     *
     * TODO
     * should tell how many bookmarks were modified
     *
     * TODO
     * Allow user to pass in optional folder to move bookmarks into
     */
    public function destroy(Folder $folder): Redirector|RedirectResponse|Application
    {
        try {
            $wasDeleted = $this->folderRepo->delete($folder);
        } catch (Throwable $e) {
            return redirect('folders')
                ->withErrors([
                    'delete' => __('messages.folder.delete.fail', ['name' => $folder->name]),
                    'error' => $e->getMessage()
                ]);
        }

        return redirect('folders')
            ->with('success', __('messages.folder.delete.success', ['name' => $folder->name]));
    }
}
