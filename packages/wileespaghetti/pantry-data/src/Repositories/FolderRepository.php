<?php

declare(strict_types=1);

namespace Pantry\Repositories;

use Exception;
use Faker\Generator;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;
use Pantry\Models\Folder;
use Pantry\User;
use Psr\Log\LoggerInterface;

/**
 * TODO
 * handle specific SQL errors
 */
class FolderRepository {
    const DEFAULT_PAGE_SIZE = 25; // FIXME read from configuration setting

    private AuthManager $auth;
    private DatabaseManager $db;
    private LoggerInterface $log;
    private Generator $generator;

    public function __construct(AuthManager $auth, DatabaseManager $db, LoggerInterface $log, Generator $faker) {
        $this->auth = $auth;
        $this->db = $db;
        $this->log = $log;
        $this->generator = $faker;
    }

    public function randomColor(): string {
        return $this->generator->hexColor();
    }

    public function getAll(): LengthAwarePaginator {
        return Folder::where('user_id', $this->auth->id())
            ->paginate(self::DEFAULT_PAGE_SIZE);
    }

    /*
     * @throws Exception
     */
    public function createOrFail(array $folderData): Folder {
        $user = $this->auth->user();
        return $this->createForUser($user, $folderData);
    }

    public function createForUser(User|Authenticatable $user, $data): Folder {
        if (empty($data['color'])) {
            $data['color'] = $this->randomColor();
        }

        $data['user_id'] = $user->id;
//        $cleanFolderData = $this->sanitize($data); // FIXME slugify?

        $folder = Folder::make($data);

        $wasSaved = $folder->saveOrFail();
        if (!$wasSaved) {
            throw new Exception(__('messages.folder.create.fail.event_handler'));
        }

        return $folder;
    }

    public function findOrCreateForUser(User $user, string $name, array $folderData = []): ?Folder {
        // FIXME this assumes unique folder names per user which is not true. we allow duplicates
        $folderData = array_merge([
            'name' => $name,
            'color' => $this->faker->hexColor(),
        ], $folderData);

        return Folder::where([
            ['name', $name],
            ['user_id', $user->id]
        ])->firstOr(function () use ($user, $folderData) { // FIXME maybe this can be replaced with firstOrCreate()
                $this->log->info(__('messages.folder.find_or_create.not_found', ['folder' => $folderData['name']]));
                return $this->createForUser($user, $folderData);
            });
    }


    public function update(Folder $folder, array $data): Folder {
        if (empty($data['color']) && empty($folder->color)) {
            $data['color'] = $this->randomColor();
        }

        $wasUpdated = $folder->updateOrFail($data);
        if (!$wasUpdated) {
            throw new Exception(__('messages.folder.update.fail.event_handler'));
        }

        return $folder->fresh();
    }

    /**
     * TODO
     * option to move all bookmarks to an optional folder
     *
     * TODO
     * deleting a folder should remove all bookmarks inside unless a different destination folder is specified to move
     * the bookmarks into
     */
    public function delete(Folder $folder): bool {
        $this->db->transaction(function() use ($folder) {
            // detaching bookmarks is not needed for HasMany relationship
            $wasDeleted = $folder->delete();
            if (!$wasDeleted) {
                throw new Exception(__('messages.folder.delete.fail.event_handler'));
            }
        });

        return true;
    }
}
