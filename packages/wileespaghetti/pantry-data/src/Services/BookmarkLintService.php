<?php
declare(strict_types=1);

namespace Pantry\Services;

/**
 * Checks Bookmarks for lint to help users identify Bookmarks that they might want to update or remove
 *
 * TODO
 * there should be a way to extend and dynamically register custom linters. Should these be Laravel validators?
 *
 * TODO
 * should FALSE being returned from preg_match functions throw exceptions?
 */
class BookmarkLintService {
    /**
     * Checks for non-HTTP(S) URLs
     *
     * @param string $url
     * @return bool
     */
    public function isNotHttp(string $url): bool {
        return preg_match('/^https?:\/\//', $url) === 0;
    }

    /**
     * Checks for non-HTTPS URLs
     *
     * @param string $url
     * @return bool
     */
    public function isNotSecure(string $url): bool {
        return preg_match('/^https:\/\//', $url) === 0;
    }
}
