<?php

namespace App\Http\Controllers;

use App\Http\Requests\FolderStoreRequest;
use App\Http\Requests\FolderUpdateRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Pantry\Folder;

/*
 * TODO
 * write tests that user can not update/delete other users' folders
 */
class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $folders = Folder::whereBelongsTo($user)->paginate(25);

        return view('folders.index', ['notifications' => Collection::empty() , 'folders' => $folders]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('folders.create', ['notifications' => Collection::empty()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FolderStoreRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(FolderStoreRequest $request)
    {
        $user = Auth::user();
        $request->validated($request); // FIXME might not be needed since we use form request object

        $folder = Folder::make($request->safe()->all()); // FIXME heard that using all() is unsafe
        $folder->user()->associate($user);

        try {
            $folder->save();
        } catch (QueryException $qe) {
            // TODO handle specific SQL errors
            return back()
                ->withErrors(['save' => __('Could not save folder')])
                ->withInput();
        }

        return redirect('folders')
            ->with('success', __('The folder :name has been created.', ['name' => $folder->name])); // FIXME make :name bold
    }

    /**
     * Display the specified resource.
     *
     * @param  \Pantry\Folder  $folder
     * @return \Illuminate\Http\Response
     */
    public function show(Folder $folder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Pantry\Folder  $folder
     * @return \Illuminate\Http\Response
     */
    public function edit(Folder $folder)
    {
        return view('folders.edit', ['notifications' => Collection::empty()])->with(compact('folder'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FolderUpdateRequest $request
     * @param \Pantry\Folder $folder
     * @return \Illuminate\Http\Response
     */
    public function update(FolderUpdateRequest $request, Folder $folder)
    {
        $request->validated($request); // FIXME might not be needed since we use form request object

        try {
            $folder->update($request->safe()->all()); // FIXME using all considered unsafe
        } catch (QueryException $qe) {
            // TODO handle specific SQL errors
            return back()
                ->withErrors(['save' => __('Could not save folder')])
                ->withInput();
        }

        return redirect('folders')
            ->with('success', __('The folder :name has been updated.', ['name' => $folder->name])); // FIXME make :name bold
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Pantry\Folder  $folder
     * @return \Illuminate\Http\Response
     *
     * FIXME
     * incomplete: I think I was working on displaying delete errors on the frontent.
     *
     * TODO
     * needs to handle deleting nested bookmarks on folder delete
     *
     * TODO
     * needs to handle moving bookmarks to a new folder when current folder is being deleted
     */
    public function destroy(Folder $folder)
    {
        try {
            throw new QueryException();
            $folder->delete();
        } catch (QueryException $qe) {
            // TODO handle specific SQL errors
            // FIXME might want to change to `back()` if we ever add a delete button to the folder.view page
            return redirect('folders')
                ->withErrors(['delete' => __('Could not delete folder')]);
        }

        return redirect('folders')
            ->with('success', __('The folder :name has been deleted.', ['name' => $folder->name])); // FIXME make :name bold
    }
}
