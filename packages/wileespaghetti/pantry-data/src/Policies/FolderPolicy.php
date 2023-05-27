<?php
declare(strict_types=1);

namespace Pantry\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Pantry\Models\Folder;
use Pantry\User;

class FolderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
//    public function viewAny(User $user)
//    {
//         TODO user must be logged in
//    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function view(User $user, Folder $folder)
    {
        return $user->id === $folder->user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
//    public function create(User $user)
//    {
//         TODO user has an active account
//    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function update(User $user, Folder $folder)
    {
        return $user->id === $folder->user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function delete(User $user, Folder $folder)
    {
        return $user->id === $folder->user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function restore(User $user, Folder $folder)
    {
        return $user->id === $folder->user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Folder $folder
     * @return Response|bool
     */
    public function forceDelete(User $user, Folder $folder)
    {
        return $user->id === $folder->user->id;
    }
}
