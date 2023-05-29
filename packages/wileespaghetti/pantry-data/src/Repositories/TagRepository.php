<?php

declare(strict_types=1);

namespace Pantry\Repositories;

use Exception;
use Faker\Generator;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Pantry\Models\Tag;
use Pantry\User;
use Psr\Log\LoggerInterface;
use Throwable;


class TagRepository {
    const DEFAULT_PAGE_SIZE = 25; // FIXME read from configuration setting

    private AuthManager $auth;
    private DatabaseManager $db;
    private LoggerInterface $log;
    private Generator $generator;

    public function __construct(LoggerInterface $log, DatabaseManager $db, AuthManager $auth, Generator $generator) {
        $this->auth = $auth;
        $this->db = $db;
        $this->log = $log;
        $this->generator = $generator;
    }

    private function sanitize(array $tagData): array {
        $sanitized = $tagData;
        $sanitized['name'] = $this->normalizeTagName($tagData['name']);
        return $sanitized;
    }

    public function normalizeTagName($tagName): string {
        $slug = Str::slug($tagName);
        if (str_starts_with($tagName, '.')) { // allow for tag names like `.net`
            $slug = ".{$slug}";
        }

        return $slug;
    }

    public function randomColor(): string {
        return $this->generator->hexColor();
    }

    public function getAll(): LengthAwarePaginator {
        return Tag::where('user_id', $this->auth->id())
            ->orderBy('name')
            ->paginate(self::DEFAULT_PAGE_SIZE);
    }

    public function getTagsByNames(User $user, array|Collection $tagNames) {
        if (is_array($tagNames)) {
            $tagNames = collect($tagNames);
        }

        return Tag::whereBelongsTo($user)
            ->whereIn('name', $tagNames)
            ->get();
    }

    /**
     * FIXME
     * should this be in a transaction?
     *
     * FIXME
     * this should work similarly to the laravel createMany functions
     *
     * FIXME
     * should use `insert()` for better performance
     *
     * FIXME
     * should also accept a collection
     *
     * FIXME
     * this isn't actually doing a bulk save
     */
    public function createManyForUser(User $user, array $data): Collection {
        if (empty($data)) {
            return collect();
        }

        return collect($data)->map(fn ($tagData) => $this->createForUser($this->auth->user(), $tagData));
    }

    /**
     * @throws Exception
     */
    public function createOrFail(array $tagData): Tag {
        $user = $this->auth->user();
        return $this->createForUser($user, $tagData);
    }

    /**
     * @throws Exception
     */
    public function createForUser(User|Authenticatable $user, array $data): Tag {
        if (empty($data['color'])) {
            $data['color'] = $this->randomColor();
        }

        $data['user_id'] = $user->id;
        $cleanTagData = $this->sanitize($data);

        $tag = Tag::make($cleanTagData);

        $wasSaved = $tag->saveOrFail();
        if (!$wasSaved) {
            throw new Exception(__('messages.tag.create.fail.event_handler'));
        }

        return $tag;
    }

    /**
     * @throws Throwable
     *
     * FIXME
     * not sure if this function fires events
     *
     * TODO
     * log created/updated/failed tags
     *
     * TODO
     * check effected rows match input length
     *
     * FIXME
     * might add an expected/actual with our utility func to calculate inserts/updates
     *
     * FIXME
     * check if send events
     *
     */
    public function upsertForUser(User $user, array $tagNames): Collection {
        $tagData = collect($tagNames)
            ->map(function ($tagName) use ($user) {
                return [
                    'name' => $tagName,
                    'user_id' => $user->id,
//                    'color' => $this->randomColor(), // FIXME this will overwrite previously assigned colors
                ];
            })->all();

        $this->db->transaction(function () use ($user, $tagData) {
            $affectedRows = Tag::upsert($tagData, ['user_id', 'name'], ['name']);
            if (!$affectedRows) {
                throw new Exception(__('messages.tag.upsert.fail'));
            }
        });

        return $this->getTagsByNames($user, $tagNames);
    }

    public function update(Tag $tag, array $data): Tag {
        if (empty($data['color'] && empty($tag->color))) {
            $data['color'] = $this->randomColor();
        }

        $wasUpdated = $tag->updateOrFail($data);
        if (!$wasUpdated) {
            throw new Exception(__('messages.tag.update.fail.event_handler'));
        }

        return $tag->fresh();
    }

    /**
     * @throws Throwable
     *
     * TODO
     * should log which bookmarks where modified
     */
    public function delete(Tag $tag): bool {
        $this->db->transaction(function() use ($tag) {
            $tag->bookmarks()->detach();

            $wasDeleted = $tag->delete();
            if (!$wasDeleted) {
                throw new Exception(__('messages.tag.delete.fail.event_handler'));
            }
        });

        return true;
    }

    /*
     * FIXME
     * needs to integrate with TagPolicy/Gates to ensure policy doesn't allow deleting other user's data
     * and so that we don't have to pass in the user_id
     * - https://laravel.com/docs/9.x/eloquent#query-scopes
     *
     * TODO
     * test what happens when only a few of the passed in tags can be deleted
     *
     * FIXME
     * not sure what the return type of this function should be. Probably the effected rows, same as delete?
     *
     * FIXME
     * does it make sense to allow using tag names instead of/in addition to IDs?
     *
     * FIXME
     * this could be done with a single whereIn()->delete() query. We could compare the affected rows
     * with the length of the input array, but we could not log out the specific tags that were either
     * not found or unauthorized. Do we care though since we are deleting and if they are not found/authorized
     * they are effectively deleted from the user's perspective.
     */
    public function deleteMany(array $tagIds): bool {
        // FIXME needs to be refactored like the other methods and not use try/catch
        $userId = $this->auth->id();

        try {
            $allowedTags = Tag::where('user_id', $userId)
                ->whereIn('id', $tagIds)
                ->get('id')
                ->pluck('id');

            // FIXME hacky workaround instead of using policy/gate checks in the query
            $unauthorizedTags = collect($tagIds)->diff($allowedTags);
            if ($unauthorizedTags->isNotEmpty()) {
                $this->log->error(__('tag.delete_many.unauthorized'), [
                    'tags' => $tagIds,
                    'user' => $userId
                ]);

                throw new Exception(__('tag.delete_many.unauthorized'));
            }

            Tag::whereIn('id', $tagIds)->delete();
        } catch (Throwable $e) {
            $tagIds = implode(', ', $tagIds);

            $this->log->error(__('tag.delete_many.fail', ['tags' => $tagIds]), [
                'user' => $userId,
                'tags' => $tagIds,
                'message' => $e->getMessage()
            ]);

            return false;
        }

        return true;
    }
}
