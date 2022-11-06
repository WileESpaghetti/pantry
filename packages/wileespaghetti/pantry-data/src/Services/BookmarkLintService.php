<?php
declare(strict_types=1);

namespace Pantry\Services;

/**
 * TODO
 * there should be a way to extend and dynamically register custom linters
 */
class BookmarkLintService {
    public function isNotWebLink(string $url): bool {
        // FIXME should I throw an exception if FALSE is returned?
        return preg_match('/^https?:\/\//', $url) === 0;
    }
}
