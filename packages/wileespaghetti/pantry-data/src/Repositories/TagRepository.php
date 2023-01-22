<?php

declare(strict_types=1);

namespace Pantry\Repositories;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\DatabaseManager;
use Pantry\Tag;
use Pantry\User;
use Psr\Log\LoggerInterface;
use Throwable;

// TODO handle specific SQL errors
class TagRepository {
    private DatabaseManager $db;
    private LoggerInterface $log;
    private ?Authenticatable $user;

    public function __construct(LoggerInterface $log, DatabaseManager $db, ?Authenticatable $user) {
        $this->db = $db;
        $this->log = $log;
        $this->user = $user;
    }

    public function createForUser(User $user, $data): Tag|null {
        $tag = Tag::make($data);
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

        return $tag;
    }

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
