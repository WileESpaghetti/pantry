<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookmarkStoreRequest extends FormRequest
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
     * FIXME string lengths are off. URLs can be over 2048 characters
     *
     * @return array
     */
    #[ArrayShape(['url' => 'string', 'title' => 'string', 'description' => 'string'])] public function rules(): array
    {
        return [
            'url' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ];
    }
}
