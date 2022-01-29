<?php

namespace Larder\Services;

use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Larder\Resources\LarderFolderResource;
use Larder\Resources\LarderTagResource;
use Symfony\Component\HttpFoundation\Response;

/**
 * FIXME
 * If a save fails, due to a 404 error when pushing back to larder we probably need to flag the bookmark for delete
 */
class LarderService
{
    private string $baseUrl = 'https://larder.io/api/1/@me';

    const DEFAULT_LIMIT = 20;

    private string $token;

    public function getDefaultLimit(): int
    {
        return self::DEFAULT_LIMIT;
    }

    /**
     * Use personal access token for API authorization
     *
     * Token authentication is ignored if any OAuth2.0 credentials are given
     *
     * @param $token string personal access token
     * @return $this
     */
    public function withToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function withOauth($access_token, $refresh_token): static {
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
        return $this;
    }

    /**
     * @return array
     *
     * TODO
     * Send updated User-Agent header
     */
    private function getDefaultHeaders(): array
    {
        return [];
    }

    /**
     * Get the appropriate HTTP Authorization header depending on if $this::withOauth or $this::withToken() is set
     * OAuth2.0 credentials always override simple token authorization.
     *
     * @return string[]
     *
     * TODO
     * should probably use `HTTP::withToken` instead of this
     *
     * TODO
     * should probably use HttpClient middleware to set headers for the requests
     */
    #[ArrayShape(['Authorization' => "string"])] private function getAuthHeaders(): array
    {
        return [
            'Authorization' =>  $this->access_token ? "Bearer {$this->access_token}" : "Token {$this->token}"
        ];
    }

    /**
     * To loop through, use `while(['meta']['next'])` and pass in the next offset
     *
     * @throws \Illuminate\Http\Client\RequestException
     *
     * FIXME
     * should this use/return a ResourceCollection?
     *
     * TODO
     * use `error` attribute from API response if encountered
     *
     * TODO
     * handle errors better
     *
     * TODO
     * might make sense to use a Paginate object instead of $limit and $offset
     */
    public function getAllFolders(int $limit = 20, int $offset = 0) {
        $path = 'folders/';

        $response = Http::withHeaders(array_merge($this->getDefaultHeaders(), $this->getAuthHeaders()))
            ->get("{$this->baseUrl}/{$path}", ['limit' => $limit, 'offset' => $offset]);

        if ($response->status() === Response::HTTP_FORBIDDEN) {
            // TODO
            $response->throw();

        } else if (!$response->successful()) {
            $response->throw();
        }

        $foldersJson = $response->json();
        if ($foldersJson === null) {
            $response->throw();
        }

        // get info for next page
        if ($foldersJson['next']) {
            parse_str(parse_url($foldersJson['next'])['query'], $q);
            $limit = $q['limit'];
            $offset = $q['offset'];
        }

        return [
            'data' => LarderFolderResource::collection($foldersJson['results']),
            'links' => [
                'next' => $foldersJson['next'],
                'prev' => $foldersJson['previous'],
            ],
            'meta' => [
                'count' => $foldersJson['count'],
                'offset' => $foldersJson['next'] ? $offset : $foldersJson['count'],
                'limit' => $limit,
            ],
        ];
    }

    /**
     * To loop through, use `while(['meta']['next'])` and pass in the next offset
     *
     * @throws \Illuminate\Http\Client\RequestException
     *
     * FIXME
     * should this use/return a ResourceCollection?
     *
     * TODO
     * use `error` attribute from API response if encountered
     *
     * TODO
     * handle errors better
     *
     * TODO
     * might make sense to use a Paginate object instead of $limit and $offset
     */
    public function getAllTags(int $limit = 20, int $offset = 0) {
        $path = 'tags/';

        $response = Http::withHeaders(array_merge($this->getDefaultHeaders(), $this->getAuthHeaders()))
            ->get("{$this->baseUrl}/{$path}", ['limit' => $limit, 'offset' => $offset]);

        if ($response->status() === Response::HTTP_FORBIDDEN) {
            // TODO
            $response->throw();

        } else if (!$response->successful()) {
            $response->throw();
        }

        $foldersJson = $response->json();
        if ($foldersJson === null) {
            $response->throw();
        }

        // get info for next page
        if ($foldersJson['next']) {
            parse_str(parse_url($foldersJson['next'])['query'], $q);
            $limit = $q['limit'];
            $offset = $q['offset'];
        }

        return [
            'data' => LarderTagResource::collection($foldersJson['results']),
            'links' => [
                'next' => $foldersJson['next'],
                'prev' => $foldersJson['previous'],
            ],
            'meta' => [
                'count' => $foldersJson['count'],
                'offset' => $foldersJson['next'] ? $offset : $foldersJson['count'],
                'limit' => $limit,
            ],
        ];
    }

}
