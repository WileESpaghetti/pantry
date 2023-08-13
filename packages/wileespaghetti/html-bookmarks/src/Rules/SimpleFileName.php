<?php

namespace HtmlBookmarks\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Validates that a file contains only word characters, numbers, and word separators that are not repeated
 *
 * TODO should support strings and not just serverInfoed file names
 */
class SimpleFileName implements InvokableRule
{
    private string $allowedFileNameFormat = '/^\w+([ \-_.]?\w+)*$/';
    private string $fileNameFormatError = 'htmlbookmarks::rules.simple_file_name.failure';

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
            $fail($this->fileNameFormatError)->translate(['attribute' => $attribute]);
        }

        $matches = preg_match($this->allowedFileNameFormat, $value->getClientOriginalName());
        if (empty($matches)) {
            $fail($this->fileNameFormatError)->translate(['attribute' => $attribute]);
        }
    }
}
