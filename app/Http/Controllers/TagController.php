<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TagDeleteManyRequest;
use App\Http\Requests\TagStoreRequest;
use App\Http\Requests\TagUpdateRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Pantry\Repositories\TagRepository;
use Pantry\Tag;

class TagController extends Controller
{
    const DEFAULT_PAGE_SIZE = 25; // FIXME read from configuration setting
    private TagRepository $tagRepo;
    public function __construct(TagRepository $tagRepo)
    {
        $this->tagRepo = $tagRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|View|ViewFactory
     */
    public function index(): Application|View|ViewFactory
    {
        $user = Auth::user();
        $tags = Tag::whereBelongsTo($user)->orderBy('name')->paginate(self::DEFAULT_PAGE_SIZE);

        return view('tags.index', ['tags' => $tags]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|View|ViewFactory
     */
    public function create(): Application|View|ViewFactory
    {
        return view('tags.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TagStoreRequest $request
     * @return RedirectResponse
     */
    public function store(TagStoreRequest $request): RedirectResponse
    {
        $tag = $this->tagRepo->createForUser(
            $request->user(),
            $request
                ->safe()
                ->merge(['color' => '#ffddee']) // FIXME this needs to be autogenerated
                ->all() // FIXME might not want to use all because we could be including stuff like CSR token or other data that is required by request, but not model
        );

        if (!$tag) {
            return back()
                ->withErrors(['save' => __('messages.tag.create.fail', ['name' => $request->safe(['name'])['name']])])
                ->withInput();
        }

        return redirect('tags')
            ->with('success', __('messages.tag.create.success', ['name' => $tag->name]));
    }

    /**
     * Display the specified resource.
     *
     * @param Tag $tag
     * @return Application|View|ViewFactory
     */
    public function show(Tag $tag): Application|View|ViewFactory
    {
        $bookmarks = $tag->bookmarks()->with('tags')->paginate(self::DEFAULT_PAGE_SIZE);

        return view('bookmarks.index', ['isContainer' => true, 'bookmarks' => $bookmarks]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Tag $tag
     * @return Application|View|ViewFactory
     */
    public function edit(Tag $tag): Application|View|ViewFactory
    {
        return view('tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TagUpdateRequest $request
     * @param Tag $tag
     * @return Application|RedirectResponse|Redirector
     */
    public function update(TagUpdateRequest $request, Tag $tag): Redirector|RedirectResponse|Application
    {
        $tag = $this->tagRepo->update($tag, $request->safe()->all());
        if (!$tag) {
            return back()
                ->withErrors('errors', [__('messages.tag.update.failed', ['name' => $request->safe('name')['name']])])
                ->withInput();
        }

        return redirect('tags')
            ->with('success', __('messages.tag.update.success', ['name' => $tag->name]));
    }

    public function deleteMany(TagDeleteManyRequest $deleteTagsRequest) {
        $tagIds = $tags = $deleteTagsRequest->safe()->tags;
        if (empty($tags)) {
            return back()
                ->withErrors('errors', [__('messages.tag.delete_many.failed'), ['tags' => $deleteTagsRequest->safe()->only(['tags'])]])
                ->withInput();
        }

        $this->tagRepo->deleteMany($tagIds);

        return redirect('tags')
            ->with('success', __('messages.tag.deleteMany.success', ['tagNames' => implode(', ', ['FIXME tag names here'])]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Tag $tag
     * @return Application|Redirector|RedirectResponse
     *
     * TODO
     * should log which bookmarks where modified
     *
     * TODO
     * should tell how many bookmarks were modified
     */
    public function destroy(Tag $tag): Redirector|RedirectResponse|Application
    {
        $wasDeleted = $this->tagRepo->delete($tag);
        if (!$wasDeleted) {
            return redirect('tags')
                ->with('errors', [__('messages.tag.delete.fail', ['name' => $tag->name])]);
        }

        return redirect('tags')
            ->with('success', __('messages.tag.delete.success', ['name' => $tag->name]));
    }
}
