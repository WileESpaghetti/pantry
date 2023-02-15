<?php

namespace Larder\Observers;

use Larder\Services\LarderService;
use Pantry\Folder;

/*
 * FIXME should probably split out the non-larder stuff into pantry-data observer
 */
class FolderObserver
{
    /**
     * Set the folder's color to a random value
     *
     * In most cases this value will be overwritten by the Larder API because a color is automatically assigned when
     * creating a folder and the API does not allow you to set a color manually. Assigning a random color ensures that
     * we are still able to fulfill the requirement of having a color when we are not connected to Larder.
     *
     * FIXME
     * should this be pre-populated when creating a folder model instead of on creating?
     *
     * @param Folder $folder
     * @return void
     */
    private function setRandomColor(Folder $folder): void {
        // this color has a high chance of being overwritten by the Larder API, but this is needed to fulfil DB constraints
        $folder->color = sprintf('%06X', mt_rand(0, 0xFFFFFF));
    }

    /**
     * TODO
     * this should probaly be moved to a service or repository
     *
     * TODO
     * handle case if bookmark is a duplicate in Larder
     *
     * TODO
     * how to handle when pantry is doing an update on a bookmark that has been deleted in larder
     *
     * @param Folder $folder
     * @return bool
     */
    private function saveToLarder(Folder $folder): bool {
        // FIXME disabled for the moment. needs to be toggleable via a user setting
        return true;

        $larder= app()->make(LarderService::class);
        $user = $folder->user;
        $larderIdentity = $user->identities()->firstWhere('provider_name', 'larder');
        if (!($larderIdentity && $larderIdentity->access_token)) {
            return false;
        }
        $larder->withOauth($larderIdentity->access_token, $larderIdentity->refresh_token);

        $larderFolder = $larder->createFolder($folder);
        $folder->color = $larderFolder['color'];

        return true;
    }

//retrieved, creating, created, updating, updated, saving, saved, deleting, deleted, restoring, restored, and replicating
    /**
     * Handle the "saving" event.
     *
     * @param  \Pantry\Folder  $folder
     * @return bool
     */
    public function creating(Folder $folder): bool {
        $this->setRandomColor($folder);

//        $isSyncedToLarder = $this->saveToLarder($folder);

        return true; //$isSyncedToLarder;
    }

    /**
     * Handle the Folder "created" event.
     *
     * @param  \Pantry\Folder  $folder
     * @return void
     *
     * FIXME
     * apparently duplicates can be created in Larder, but this is functionality that the user might want to turn off
     *
     * FIXME also need some way to rollback if db operation fails. (would we just want to try and import instead?)
     */
    public function created(Folder $folder)
    {
        //
    }

    /**
     * Handle the Folder "updated" event.
     *
     * @param  \Pantry\Folder  $folder
     * @return void
     */
    public function updated(Folder $folder)
    {
        //
    }

    /**
     * Handle the Folder "deleted" event.
     *
     * @param  \Pantry\Folder  $folder
     * @return void
     */
    public function deleted(Folder $folder)
    {
        //
    }

    /**
     * Handle the Folder "restored" event.
     *
     * @param  \Pantry\Folder  $folder
     * @return void
     */
    public function restored(Folder $folder)
    {
        //
    }

    /**
     * Handle the Folder "force deleted" event.
     *
     * @param  \Pantry\Folder  $folder
     * @return void
     */
    public function forceDeleted(Folder $folder)
    {
        //
    }
}
