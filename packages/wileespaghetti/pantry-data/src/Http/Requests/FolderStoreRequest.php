<?php
declare(strict_types=1);

namespace Pantry\Http\Requests;

use App\Http\Requests\ArrayShape;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class FolderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
     */
    #[ArrayShape(['name' => "string"])] public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * @return array|UploadedFile|UploadedFile[]|null
     */
    public function getFile(): array|UploadedFile|null
    {
        return $this->file('bookmark');
    }
}
