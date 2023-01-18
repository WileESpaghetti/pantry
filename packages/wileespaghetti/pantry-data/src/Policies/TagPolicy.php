<?php
declare(strict_types=1);

namespace Pantry\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Pantry\Tag;
use Pantry\User;

class TagPolicy
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
     * @param Tag $tag
     * @return Response|bool
     */
    public function view(User $user, Tag $tag)
    {
        return $user->id === $tag->user->id;
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
     * @param Tag $tag
     * @return Response|bool
     */
    public function update(User $user, Tag $tag)
    {
        return $user->id === $tag->user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Tag $tag
     * @return Response|bool
     */
    public function delete(User $user, Tag $tag)
    {
        return $user->id === $tag->user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Tag $tag
     * @return Response|bool
     */
    public function restore(User $user, Tag $tag)
    {
        return $user->id === $tag->user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Tag $tag
     * @return Response|bool
     */
    public function forceDelete(User $user, Tag $tag)
    {
        return $user->id === $tag->user->id;
    }
}
