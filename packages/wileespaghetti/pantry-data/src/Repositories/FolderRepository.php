<?php

declare(strict_types=1);

namespace Pantry\Repositories;

use Exception;
use Faker\Generator;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\DatabaseManager;
use Pantry\Folder;
use Pantry\User;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * TODO
 * handle specific SQL errors
 */
class FolderRepository {
    private DatabaseManager $db;
    private LoggerInterface $log;
    private ?Authenticatable $user;
    private Generator $faker;

    public function __construct(LoggerInterface $log, DatabaseManager $db, Generator $faker, ?Authenticatable $user) {
        $this->db = $db;
        $this->log = $log;
        $this->faker = $faker;
        $this->user = $user;
    }

    public function findOrCreateForUser(User $user, string $name, array $folderData = []): Folder|null {
        $folderData = array_merge([
            'name' => $name,
            'color' => $this->faker->hexColor(),
        ], $folderData);

        return Folder::where([['name', $name], ['user_id', $user->id]])
            ->firstOr(function () use ($user, $folderData) { // FIXME maybe this can be replaced with firstOrCreate()
                $this->log->info(__('messages.folder.find_or_create.not_found', ['folder' => $folderData['name']]));
                return $this->createForUser($user, $folderData);
            });
    }

    public function createForUser(User $user, $data): Folder|null {
        $folder = Folder::make($data);
        $folder->user()->associate($user);

        try {
            $wasSaved = $folder->saveOrFail();
            if (!$wasSaved) {
                throw new Exception(__('messages.folder.create.fail.event_handler'));
            }
        } catch (Throwable $e) {
            $this->log->error(__('messages.folder.create.fail'), [
                'user' => $this->user?->getAuthIdentifier(),
                'folder' => $folder->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $folder;
    }

    public function update(Folder $folder, array $data): Folder|null {
        try {
            $wasUpdated = $folder->updateOrFail($data);
            if (!$wasUpdated) {
                throw new Exception(__('messages.folder.update.fail.event_handler'));
            }
        } catch (Throwable $e) {
            $this->log->error(__('messages.folder.update.fail'), [
                'user' => $this->user?->getAuthIdentifier(),
                'folder' => $folder->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $folder;
    }

    /**
     * @param Folder $folder
     * @return bool
     *
     * TODO
     * deleting a folder should remove all bookmarks inside unless a different destination folder is specified to move
     * the bookmarks into
     */
    public function delete(Folder $folder): bool {
        try {
            $this->db->transaction(function() use ($folder) {
                // detaching bookmarks is not needed for HasMany relationship
                $wasDeleted = $folder->delete();
                if (!$wasDeleted) {
                    throw new Exception(__('messages.folder.delete.fail.event_handler'));
                }
            });
        } catch(Throwable $e) {
            $this->log->error(__('folder.delete.fail', ['name' => $folder->name]), [
                'user' => $this->user?->getAuthIdentifier(),
                'folder' => $folder->id,
                'message' => $e->getMessage()
            ]);

            return false;
        }

        return true;
    }
}
