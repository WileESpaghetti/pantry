<?php
declare(strict_types=1);

namespace Pantry\Http\Requests;

class BookmarkUpdateRequest extends BookmarkStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
