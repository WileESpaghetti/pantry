<?php
declare(strict_types=1);

namespace HtmlBookmarks\Http\Requests;

use HtmlBookmarks\Rules\SimpleFileName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use JetBrains\PhpStorm\ArrayShape;

/**
 * TODO
 * custom error messages for file upload errors (ex. exceeded maximum size)
 */
class BookmarkFileImportRequest extends FormRequest
{
    private const PARAM_UPLOADED_FILE = 'bookmark';

    /**
     * Determine if the user is authorized to make this request.
     *
     * TODO
     * check user can_upload and can_write (demo users)
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * run a file through some basic checks to try and reduce the number of errors in parsing
     * returns whether the file passed validation.
     *
     * @return array
     *
     * TODO
     * add parameter for which folder to import into when the import gets queued
     */
    #[ArrayShape(['bookmark' => "string"])] public function rules(): array
    {
        return [
            'bookmark' => ['required', 'mimes:htm,html', new SimpleFileName()],
        ];
    }

    public function hasBookmarkFile(): bool {
        return $this->hasFile(self::PARAM_UPLOADED_FILE) && $this->getFile() != null;
    }

    /**
     * @return UploadedFile|null
     */
    public function getFile(): UploadedFile|null
    {
        $uploadedFile = $this->file(self::PARAM_UPLOADED_FILE);
        if (is_array($uploadedFile)) {
            $uploadedFile = null;
        }

        return $uploadedFile;
    }
}
