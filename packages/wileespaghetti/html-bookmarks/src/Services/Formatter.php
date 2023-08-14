<?php
// FIXME remake into a static Fmt class

declare(strict_types=1);

namespace HtmlBookmarks\Services;

/**
 * Only does up to Gigabytes
 *
 * @param int $bytes
 * @param int $decimals
 * @return string
 */
function humanBytes(int $bytes, int $decimals = 1): string
{
    // FIXME strip zeros after the decimal point
    if ($bytes === PHP_INT_MAX) {
        return __('htmlbookmarks::max_file_size_unknown');
    }

    $sBytes = strval($bytes);
    $size = array('B', 'kB', 'MB', 'GB');
    $factor = floor((strlen($sBytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $sBytes / pow(1024, $factor)) . " {$size[$factor]}";
}
