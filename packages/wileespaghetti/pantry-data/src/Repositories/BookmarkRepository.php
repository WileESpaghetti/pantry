<?php

declare(strict_types=1);

namespace Pantry\Repositories;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Pantry\Models\Bookmark;
use Pantry\Models\Folder;
use Pantry\Models\Tag;
use Pantry\User;
use Psr\Log\LoggerInterface;

/**
 * TODO
 * sanitize bookmark data
 *
 * TODO
 * add folder support
 */
class BookmarkRepository {
    const DEFAULT_PAGE_SIZE = 25; // FIXME read from configuration setting

    private TagRepository $tagRepository;
    private AuthManager $auth;
    private DatabaseManager $db;
    private LoggerInterface $log;

    public function __construct(TagRepository $tagRepository,  AuthManager $auth, DatabaseManager $db, LoggerInterface $log) {
        $this->tagRepository = $tagRepository;
        $this->auth = $auth;
        $this->db = $db;
        $this->log = $log;
    }

    public function getAll(): LengthAwarePaginator
    {
        return Bookmark::with('tags')
            ->where('user_id', $this->auth->id())
            ->paginate(self::DEFAULT_PAGE_SIZE);
    }

    public function getAllByTag(Tag $tag): LengthAwarePaginator {
        return $tag->bookmarks()
            ->with('tags')
            ->paginate(self::DEFAULT_PAGE_SIZE);
    }

    public function getAllByFolder(Folder $folder): LengthAwarePaginator {
        return $folder->bookmarks()
            ->with('tags')
            ->paginate(self::DEFAULT_PAGE_SIZE);
    }

    /**
     * FIXME
     * it might make sense to create a bookmark batch table and bookmarks.batch_id instead of import_id.
     * then we can just use the batch number of Import000$import_id and Larder0000$import_id
     * which will let us fetch all bulk inserted stuff.
     * Would need to decide what to do with that field on CRUD. Ex. should clear if import removed?
     */
    public function createManyForUser(User $user, array $bookmarkData, array $tagData): Collection {
//            $bookmark = Bookmark::make($bookmarkData);
//            $bookmark->user()->associate($this->user);
//            $bookmark->tags()->attach();
//            return $bookmark;
    }

    public function createMany(array|Collection $bookmarksData) {
        if (!is_array($bookmarksData)) {
            $bookmarksData = collect($bookmarksData);
        }

        // FIXME should we create sequence numbers or some other way to fetch after?
        // FIXME should we include updating the create/modified date here? one reason we wouldn't want to is that it will overwrite create dates on imported bookmarks
        Bookmark::insert($bookmarksData->all()); // FIXME handle errors / put in transaction
    }


    /**
     * @throws Exception
     */
    public function createOrFail(array $bookmarkData): Bookmark {
        $user = $this->auth->user();
        return $this->createForUser($user, $bookmarkData);
    }

    /*
     * TODO
     * returned bookmark should have it's ID
     * add a bookmark with no tags
         * on bookmark failure user should be notified of the failure
     * add a bookmark with all existing tags
         * on bookmark failure user should be notified of the failure
     * add a bookmark with all new tags
         * on bookmark failure new tags should be created
             * user should be notified of the failure
         * on tag failure bookmark should be created without the failed tags
             * user should be notified of which tags were unable to be created along with why
     * add a bookmark with mix of existing/new tags
         * on bookmark failure new tags should be created
             * user should be notified of the failure
         * on tag failure bookmark should be created without the failed tags
             * user should be notified of which tags were unable to be created along with why
     */
    /**
     * @throws Exception
     */
    public function createForUser(User|Authenticatable $user, $data): ?Bookmark {
        $tagNames = $data['tags'] ?? [];
        unset($data['tags']);

        $data['user_id'] = $user->id;
        $bookmark = Bookmark::make($data);

        $wasSaved = $bookmark->saveOrFail();
        if (!$wasSaved) {
            throw new Exception(__('messages.bookmark.create.fail.event_handler'));
        }

        if ($tagNames) {
            $tags = $this->tagRepository->upsertForUser($user, $tagNames);
            $tagIds = $tags->pluck('id');
        }

        if (!empty($tagIds)) {
            $bookmark->tags()->attach($tagIds);
        }

        return $bookmark;
    }

    public function update(Bookmark $bookmark, array $data): Bookmark {
        $tagNames = $data['tags'] ?? [];
        unset($data['tags']);

        $wasUpdated = $bookmark->updateOrFail($data);
        if (!$wasUpdated) {
            throw new Exception(__('messages.bookmark.update.fail.event_handler'));
        }

        if ($tagNames) {
            $tags = $this->tagRepository->upsertForUser($this->auth->user(), $tagNames);
            $tagIds = $tags->pluck('id');
        }

        $bookmark->tags()->sync($tagIds ?? []);

        return $bookmark->fresh();
    }

    public function delete(Bookmark $bookmark): bool {
        $wasDeleted = $bookmark->deleteOrFail();
        if (!$wasDeleted) {
            throw new Exception(__('messages.bookmark.delete.fail.event_handler'));
        }

        return true;
    }
}
