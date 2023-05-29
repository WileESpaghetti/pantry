<?php

declare(strict_types=1);

namespace Pantry\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Pantry\Http\Requests\TagDeleteManyRequest;
use Pantry\Http\Requests\TagStoreRequest;
use Pantry\Http\Requests\TagUpdateRequest;
use Pantry\Models\Tag;
use Pantry\Repositories\BookmarkRepository;
use Pantry\Repositories\TagRepository;
use Throwable;

class TagController extends Controller
{
    private BookmarkRepository $bookmarkRepo;
    private TagRepository $tagRepo;

    public function __construct(BookmarkRepository $bookmarkRepo, TagRepository $tagRepo)
    {
        $this->bookmarkRepo = $bookmarkRepo;
        $this->tagRepo = $tagRepo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Application|View|ViewFactory
    {
        $tags = $this->tagRepo->getAll();

        return view('tags.index', ['tags' => $tags]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Application|View|ViewFactory
    {
        return view('tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TagStoreRequest $request): RedirectResponse
    {
        $tagData = $request->safe()->all();

        try {
            $tag = $this->tagRepo->createOrFail($tagData);
        } catch (Throwable $e) {
            $this->log->error(__('messages.tag.create.fail'), [
                'error' => $e->getMessage(),
                'tag' => $request->safe()->all()
            ]);

            return to_route('tags.create')
                ->withErrors([
                    'save' => __('messages.tag.create.fail'),
                    'error' => $e->getMessage()
                ])
                ->withInput();
        }

        return redirect('tags')
            ->with('success', __('messages.tag.create.success', ['name' => $tag->name]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag): Application|View|ViewFactory
    {
        $bookmarks = $this->bookmarkRepo->getAllByTag($tag);

        return view('bookmarks.index', ['isContainer' => true, 'bookmarks' => $bookmarks, 'tag' => $tag]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag): Application|View|ViewFactory
    {
        return view('tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TagUpdateRequest $request, Tag $tag): RedirectResponse
    {
        try {
            $tag = $this->tagRepo->update($tag, $request->safe()->all());
        } catch (Throwable $e) {
            return to_route('tags.edit', [$tag])
                ->withErrors([
                    'save' => __('messages.tag.update.failed', ['name' => $request->safe('name')['name']]),
                    'error' => $e->getMessage()
                ])
                ->withInput();
        }

        return redirect('tags')
            ->with('success', __('messages.tag.update.success', ['name' => $tag->name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * TODO
     * should tell how many bookmarks were modified
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        try {
            $wasDeleted = $this->tagRepo->delete($tag);
        } catch (Throwable $e) {
            return redirect('tags')
                ->withErrors([
                    'delete' => __('messages.tag.delete.fail', ['name' => $tag->name]),
                    'error' => $e->getMessage(),
                ]);
        }

        return redirect('tags')
            ->with('success', __('messages.tag.delete.success', ['name' => $tag->name]));
    }

    public function deleteMany(TagDeleteManyRequest $deleteTagsRequest) {
        $tagIds = $deleteTagsRequest->safe()->tags;

        try {
            $this->tagRepo->deleteMany($tagIds);
        } catch (Throwable $e) {
            return back()
                ->withErrors('errors', [__('messages.tag.delete_many.failed'), ['tags' => $deleteTagsRequest->safe()->only(['tags'])]])
                ->withInput();
        }

        return redirect('tags')
            ->with('success', __('messages.tag.deleteMany.success', [
                'tagNames' => implode(', ', ['FIXME tag names here'])
            ]));
    }
}
