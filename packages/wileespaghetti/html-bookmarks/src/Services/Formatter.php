<?php

declare(strict_types=1);

namespace Pantry\Services;

/**
 * Only does up to Gigabytes
 *
 * @param int $bytes
 * @param $decimals
 * @return string
 */
function humanBytes(int $bytes, $decimals = 1): string {
    // FIXME strip zeros after the decimal point
    if ($bytes === PHP_INT_MAX) {
        return __('htmlbookmarks::max_file_size_unknown');
    }

    $sBytes = strval($bytes);
    $size = array('B','kB','MB','GB');
    $factor = floor((strlen($sBytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $sBytes / pow(1024, $factor)) . " {$size[$factor]}";
}
