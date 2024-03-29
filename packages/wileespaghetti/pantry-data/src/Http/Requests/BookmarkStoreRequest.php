<?php
declare(strict_types=1);

namespace Pantry\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * TODO
 * update validation message for tags and tags.*
 */
class BookmarkStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * run a file through some basic checks to try and reduce the number of errors in parsing
     * returns whether the file passed validation.
     */
    public function rules(): array
    {
        return [
            'url' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $formTags = $this->get('tags');
        if (!$formTags) {
            return;
        }

        $tags = preg_split('/,+ */', $formTags, -1, PREG_SPLIT_NO_EMPTY);
        if ($tags === false) {
            return;
        }

        $this->merge([
            'tags' => $tags,
        ]);
    }
}
