<?php

declare(strict_types=1);

namespace Pantry\Repositories;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\DatabaseManager;
use Pantry\Bookmark;
use Pantry\User;
use Psr\Log\LoggerInterface;
use Throwable;

// TODO handle specific SQL errors
// TODO add folder support
// TODO add tag support
class BookmarkRepository {
    private DatabaseManager $db;
    private LoggerInterface $log;
    private ?Authenticatable $user;

    public function __construct(LoggerInterface $log, DatabaseManager $db, ?Authenticatable $user) {
        $this->db = $db;
        $this->log = $log;
        $this->user = $user;
    }

    public function createForUser(User $user, $data): Bookmark|null {
        $bookmark = Bookmark::make($data);
        $bookmark->user()->associate($user);

        try {
            $wasSaved = $bookmark->saveOrFail();
            if (!$wasSaved) {
                throw new Exception(__('messages.bookmark.create.fail.event_handler'));
            }
        } catch (Throwable $e) {
            $this->log->error(__('messages.bookmark.create.fail'), [
                'user' => $this->user?->getAuthIdentifier(),
                'bookmark' => $bookmark->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $bookmark;
    }

    public function update(Bookmark $bookmark, array $data): Bookmark|null {
        try {
            $wasUpdated = $bookmark->updateOrFail($data);
            if (!$wasUpdated) {
                throw new Exception(__('messages.bookmark.update.fail.event_handler'));
            }
        } catch (Throwable $e) {
            $this->log->error(__('messages.bookmark.update.fail'), [
                'user' => $this->user?->getAuthIdentifier(),
                'bookmark' => $bookmark->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $bookmark;
    }

    /**
     * @param Bookmark $bookmark
     * @return bool
     */
    public function delete(Bookmark $bookmark): bool {
        try {
            $this->db->transaction(function() use ($bookmark) {
                $wasDeleted = $bookmark->delete();
                if (!$wasDeleted) {
                    throw new Exception(__('messages.bookmark.delete.fail.event_handler'));
                }
            });
        } catch(Throwable $e) {
            $this->log->error(__('bookmark.delete.fail', ['name' => $bookmark->name]), [
                'user' => $this->user?->getAuthIdentifier(),
                'bookmark' => $bookmark->id,
                'message' => $e->getMessage()
            ]);

            return false;
        }

        return true;
    }
}
