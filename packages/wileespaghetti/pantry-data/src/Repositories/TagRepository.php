<?php

declare(strict_types=1);

namespace Pantry\Repositories;

use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Pantry\Tag;
use Pantry\User;
use Psr\Log\LoggerInterface;
use Throwable;


/**
 * TODO
 * handle specific SQL errors
 *
 * TODO
 * Tag names should be slugs.
 * Does it make sense to do this at the model attribute layer instead the service layer?
 *
 * FIXME
 * $this->user doesn't get set properly. probably not available until later
 *
 */
class TagRepository {
    private AuthManager $auth;
    private DatabaseManager $db;
    private LoggerInterface $log;
    private ?Authenticatable $user;

    public function __construct(LoggerInterface $log, DatabaseManager $db, AuthManager $auth, ?Authenticatable $user) {
        $this->auth = $auth;
        $this->db = $db;
        $this->log = $log;
        $this->user = $user;
    }

    private function sanitize(array $tagData): array {
        $sanitized = $tagData;
        $sanitized['name'] = Str::slug($tagData['name']); // FIXME might want to tweak this so that you can have tags like '.net'
        return $sanitized;
    }

    public function createForUser(User $user, array $data): Tag|null {
        $cleanTagData = $this->sanitize($data);

        $tag = Tag::make($cleanTagData);
        $tag->user()->associate($user);

        try {
            $wasSaved = $tag->saveOrFail();
            if (!$wasSaved) {
                throw new Exception(__('messages.tag.create.fail.event_handler'));
            }
        } catch (Throwable $e) {
            $this->log->error(__('messages.tag.create.fail'), [
                'user' => $this->user?->getAuthIdentifier(),
                'tag' => $tag->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $tag;
    }

    /**
     * @param User $user
     * @param array $data
     * @return Collection
     *
     * FIXME
     * should this be in a transaction?
     *
     * FIXME
     * this should work similarly to the laravel createMany functions
     */
    public function createManyForUser(User $user, array $data): Collection {
        return collect($data)->map(function ($tagData) use ($user) {
            return $this->createForUser($user, $tagData); // FIXME fails silently and returns null if the tag is already created
        });
    }

    /**
     * @param User $user
     * @param string[] $tagNames
     * @return Collection
     */
    public function upsertForUser(User $user, array $tagNames): Collection {
        // FIXME not sure if this function fires events
        $tagData = collect($tagNames)
            ->map(function ($tagName) use ($user) {
                return [
                    'name' => $tagName,
                    'user_id' => $user->id,
//                        'color' => $this->faker->hexColor(), // FIXME this will overwrite previously assigned colors, but is used as a work around of the `NOT NULL` constraint
                ];
            })->all();

        try {
            $this->db->transaction(function () use ($user, $tagData) {
                $affectedRows = Tag::upsert($tagData, ['user_id', 'name'], ['name']); // FIXME see if upsert is already run in a transaction
                if (!$affectedRows) { // FIXME check effected rows match input length
                    throw new Exception(__('messages.tag.upsert.fail')); // FIXME might add an expected/actual with our utility func to calculate inserts/updates
                }
            });
        } catch (Throwable $e) {
            $this->log->error(__('messages.tag.upsert.fail'), [
                'user' => $this->user?->getAuthIdentifier(),
                'tags' => $tagData,
                'message' => $e->getMessage()
            ]);
        }

        return Tag::whereBelongsTo($user)->whereIn('name', $tagNames)->get(); // FIXME what is the result of this when no results?
    }

    /**
     * @param Tag $tag
     * @param array $data
     * @return Tag|null fresh Model
     */
    public function update(Tag $tag, array $data): Tag|null {
        try {
            $wasUpdated = $tag->updateOrFail($data);
            if (!$wasUpdated) {
                throw new Exception(__('messages.tag.update.fail.event_handler'));
            }
        } catch (Throwable $e) {
            $this->log->error(__('messages.tag.update.fail'), [
                'user' => $this->user?->getAuthIdentifier(),
                'tag' => $tag->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $tag->fresh();
    }

    /*
     * FIXME
     * needs to integrate with TagPolicy/Gates to ensure policy doesn't allow deleting other user's data
     * and so that we don't have to pass in the user_id
     *
     * TODO
     * test what happens when only a few of the passed in tags can be deleted
     *
     * FIXME
     * not sure what the return type of this function should be
     *
     * FIXME
     * does it make sense to allow using tag names instead of/in addition to IDs?
     */
    public function deleteMany(array $tagIds): bool {
        $this->user = $this->auth->user(); // FIXME hacky workaround for user not being null in the constructor

        try {
            $allowedTags = Tag::where('user_id', $this->user->getAuthIdentifier())
                ->whereIn('id', $tagIds)
                ->get('id')
                ->pluck('id');

            // FIXME hacky workaround instead of using policy/gate checks in the query
            $unauthorizedTags = collect($tagIds)->diff($allowedTags);
            if ($unauthorizedTags->isNotEmpty()) {
                $this->log->error(__('tag.delete_many.unauthorized'), [
                    'tags' => $tagIds,
                    'user' => $this->user->getAuthIdentifier()
                ]);
                throw new Exception(__('tag.delete_many.unauthorized')); // FIXME is this the correct exception type?
            }

            Tag::whereIn('id', $tagIds)->delete();
        } catch (Throwable $e) {
            $tagIds = implode(', ', $tagIds);

            $this->log->error(__('tag.delete_many.fail', ['tags' => $tagIds]), [
                'user' => $this->user?->getAuthIdentifier(),
                'tags' => $tagIds,
                'message' => $e->getMessage()
            ]);

            return false;
        }

        return true;
    }

    // FIXME ensure policy doesn't allow deleting other user's data
    public function delete(Tag $tag): bool {
        try {
            $this->db->transaction(function() use ($tag) {
                $tag->bookmarks()->detach();
                $wasDeleted = $tag->delete();
                if (!$wasDeleted) {
                    throw new Exception(__('messages.tag.delete.fail.event_handler'));
                }
            });
        } catch(Throwable $e) {
            $this->log->error(__('tag.delete.fail', ['name' => $tag->name]), [
                'user' => $this->user?->getAuthIdentifier(),
                'tag' => $tag->id,
                'message' => $e->getMessage()
            ]);

            return false;
        }

        return true;
    }
}
