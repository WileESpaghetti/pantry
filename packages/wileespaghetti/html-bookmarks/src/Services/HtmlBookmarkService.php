<?php

declare(strict_types=1);

namespace HtmlBookmarks\Services;

use Exception;
use HtmlBookmarks\Jobs\ProcessBookmarks;
use HtmlBookmarks\Models\BookmarkFile;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Pantry\User;
use Psr\Log\LoggerInterface;
use Shaarli\NetscapeBookmarkParser\NetscapeBookmarkParser;
use Throwable;

/**
 *
 * TODO
 * might be able to extend NetscapeBookmarkParser to be streamable by getting line by line and parsingString then
 * listening to the logger and parsing log messages
 *
 * FIXME
 * need to do some testing with tags that contain spaces. Bookmark parser will allow them if a header has a comma,
 * or if one of the tags fields has a comma. Will need to normalize if Larder doesn't allow them.
 */
class HtmlBookmarkService
{
    private FilesystemManager $storage;

    private LoggerInterface $log;
    private NetscapeBookmarkParser $_parser;

    public function __construct(FilesystemManager $storage, LoggerInterface $logger, NetscapeBookmarkParser $parser)
    {
        $this->storage = $storage;
        $this->log = $logger;

        $this->_parser = $parser;
    }

    /**
     * @param UploadedFile $file
     * @param User $user
     * @return BookmarkFile|null
     *
     * TODO
     * see if there are ways to get more details about the failures
     *
     * TODO
     * maybe use putFileAs to improve performance?
     */
    public function store(UploadedFile $file, User $user): BookmarkFile|null
    {
        // return if file stored or whatever is needed to queue job

        $sanitizedFileName = $file->hashName();
        $storedPath = $file->storeAs('/public', $sanitizedFileName);
        if (!$storedPath) {
            $this->log->error(__('could not store file'), [
                'original_name' => $file->getClientOriginalName(),
                'sanitized_name' => $sanitizedFileName,
                'stored_path' => $storedPath
            ]);

            return null;
        }

        $metadata = $this->createMetadata($file, $sanitizedFileName, $storedPath, $user);
        if (!$metadata) {
            $wasDeleted = $this->storage->delete($storedPath);
            if (!$wasDeleted) {
                $this->log->error(__('could not remove file'), [
                    'original_name' => $file->getClientOriginalName(),
                    'sanitized_name' => $sanitizedFileName,
                    'path' => $storedPath
                ]);
            }

            return null;
        }

        ProcessBookmarks::dispatch($metadata->path, $user, null); // FIXME handle failure adding to queue

        return $metadata;
    }

    /**
     * @param $file
     * @param $fileName
     * @param $path
     * @param $user
     * @return BookmarkFile|null
     */
    public function createMetadata($file, $fileName, $path, $user): BookmarkFile|null {
        $metadata = new BookmarkFile([
            'file_name' => $fileName,
            'file_name_original' => $file->getClientOriginalName(), // FIXME might want to strip non-alphanum
            //            'sha256sum' => hash_file('sha256', Storage::get($storedFilePath)), // FIXME causes error in unit test
            'file_size_bytes' => $file->getSize(),
            'path' => $path
        ]);
        $metadata->user()->associate($user);

        try {
            $wasSaved = $metadata->saveOrFail();
            if (!$wasSaved) {
                // occurs when an event handler return false when saving
                // FIXME not sure if even handler will rollback database data, but they need to?
                throw new Exception(__('htmlbookmarks::upload.failure.meta_data_save'));
            }
        } catch (Throwable $e) {
            $this->log->error(__('htmlbookmarks::upload.failure.meta_data_save'), [
                'message' => $e->getMessage(),
                'file' => $metadata->file_name,
                'file_name_original' => $metadata->file_name_original,
                'user_id' => $user->id,
            ]);

            return null;
        }

        return $metadata;
    }

    /**
     * TODO
     * should there be an option to stop parsing on first error
     *
     * @return void
     */
    public function parse()
    {
        // TODO check user has permissions to file
        // TODO get file contents
        // TODO parse file
        // TODO convert []items to []Bookmark

        // FIXME below tasks might be better as a separate function
        // TODO save bookmarks
        // TODO update Bookmark upload meta data
        // TODO notify the user
        // TODO run bookmarks through transformations
        // TODO produce changelog of bookmarks
    }

    public function getImportFolder() {

    }
}
