<?php
declare(strict_types=1);

namespace Pantry\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagDeleteManyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tags' => 'required|array',
            'tags.*' => 'numeric|min:1'
        ];
    }
}
