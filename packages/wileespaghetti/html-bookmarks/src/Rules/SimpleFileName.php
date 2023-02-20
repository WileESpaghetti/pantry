<?php

namespace HtmlBookmarks\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Validates that a file contains only word letters, numbers, and word separators that are not repeated
 */
class SimpleFileName implements InvokableRule
{
    private string $allowedFileNameFormat = '/^\w+([ \-_.]*\w+)*\w+$/';
    private string $fileNameFormatError = 'The :attribute file name contains unsupported characters. File names should contain only letters, numbers, spaces, hyphens, and underscores.';

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
    {
        if (!($value instanceof UploadedFile) || !$value->isValid()) {
            $fail($this->fileNameFormatError);
        }

        $matches = preg_match($this->allowedFileNameFormat, $value->getClientOriginalName());
        if (empty($matches)) {
            $fail($this->fileNameFormatError);
        }
    }
}
