<?php
declare(strict_types=1);

namespace Pantry\Http\Requests;

class FolderUpdateRequest extends FolderStoreRequest
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
