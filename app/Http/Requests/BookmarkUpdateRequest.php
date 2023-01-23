<?php
declare(strict_types=1);

namespace App\Http\Requests;

class BookmarkUpdateRequest extends BookmarkStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * FIXME only allow if the user owns the folder
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
