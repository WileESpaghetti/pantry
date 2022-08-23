<?php
declare(strict_types=1);

namespace HtmlBookmarks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use JetBrains\PhpStorm\ArrayShape;

/**
 * TODO
 * custom error messages for file upload errors (ex. exceeded maximum size)
 *
 * FIXME
 * handle normal PHP upload issues (ex. file too large, wrong mime type, etc.)
 *
 */
class BookmarkFileImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     *
     * TODO
     * add a role to allow uploads
     * add a role to allow write
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
     * limit to one file
     */
    #[ArrayShape(['bookmark' => "string"])] public function rules(): array
    {
        return [
            'bookmark' => 'required|mimes:htm,html',
        ];
    }

    /**
     * @return array|UploadedFile|UploadedFile[]|null
     *
     * FIXME
     * might need to tweak this if we allow multiple files in the upload
     */
    public function getFile(): array|UploadedFile|null
    {
        return $this->file('bookmark');
    }
}
