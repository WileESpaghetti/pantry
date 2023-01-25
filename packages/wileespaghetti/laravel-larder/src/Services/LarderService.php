<?php

namespace Larder\Services;

use App\SocialFolder;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Larder\Resources\LarderBookmarkResource;
use Larder\Resources\LarderFolderResource;
use Larder\Resources\LarderTagResource;
use Pantry\Folder;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

/**
 * FIXME
 * If a save fails, due to a 404 error when pushing back to larder we probably need to flag the bookmark for delete
 */
class LarderService
{
    private HttpFactory $httpFactory;

    private ConfigRepository $config;

    public string $baseUrl;

    const DEFAULT_LIMIT = 20; // TODO move to config value

    private string $token; // Token Based authentication

    private string $access_token; // OAuth based authentication

    private string $refresh_token;

    public function __construct(ConfigRepository $configRepository, HttpFactory $httpFactory)
    {
        $this->config = $configRepository;
        $this->httpFactory = $httpFactory;

        $this->baseUrl = $this->config->get('larder.url', '');
    }

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
    #[ArrayShape(['Authorization' => 'string'])]
 private function getAuthHeaders(): array
 {
     return [
         'Authorization' => $this->access_token ? "Bearer {$this->access_token}" : "Token {$this->token}",
     ];
 }

    /**
     * Sub folders are not supported
     *
     * @param  Folder  $folder
     * @return array
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createFolder(Folder $folder)
    {
        $path = 'folders/add/';

//        $response = Http::withHeaders(array_merge($this->getDefaultHeaders(), $this->getAuthHeaders()))
//            ->post("{$this->baseUrl}/{$path}", ['name' => $folder->name, 'parent' => null]);
//
        $response = $this->httpFactory->withHeaders(array_merge($this->getDefaultHeaders(), $this->getAuthHeaders()))
            ->post("{$this->baseUrl}/{$path}", ['name' => $folder->name, 'parent' => null]);

        if ($response->status() === Response::HTTP_FORBIDDEN) {
            // TODO
            $response->throw();
        } elseif (! $response->successful()) {
            // TODO 400 errors should have an `error` attribute in the returned JSON
            $response->throw();
        }

        $foldersJson = $response->json();
        if ($foldersJson === null) {
            $response->throw();
        }

        return LarderFolderResource::make($foldersJson)->resolve();
    }

    /**
     * TODO
     * Folder edit endpoint seems very finicky. I tried a couple of test updates and I kept getting 500 errors
     * from the server. Either I haven't come up with the appropriate headers/data combination, or the endpoint
     * is borked.
     *
     * @param  Folder  $folder
     * @return void
     */
    public function updateFolder(Folder $folder)
    {
        throw new RuntimeException('Not Implemented');
    }

    /**
     * @param  Folder  $folder
     * @param  string  $emptyTo when set to null or an empty string then all bookmarks in that folder will be deleted
     * @return void
     *
     * FIXME
     * for stuff like $emptyTo, I read somewhere it's more flexible to pass in the actual Folder
     * object and use it's id instead of just passing the ID in directly
     */
    public function deleteFolder(Folder $folder, string $emptyTo)
    {
        /*
         * The documentation only states that an empty string will delete all bookmarks when
         * using x-www-form-urlencoded data. I'm too lazy to test if the behavior is the same
         * for JSON data, so we'll just convert it instead of relying on undocumented
         * behavior.
         *
         * @see http://developer.larder.io/?shell#delete-a-folder
         */
        if ($emptyTo === '') {
            $emptyTo = null;
        }
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
    public function getAllBookmarks(Folder $folder, int $limit = 20, int $offset = 0)
    {
        $sf = SocialFolder::where('folder_id', $folder->id)->first();
        $path = "folders/{$sf->social_id}/";

        $response = Http::withHeaders(array_merge($this->getDefaultHeaders(), $this->getAuthHeaders()))
            ->get("{$this->baseUrl}/{$path}", ['limit' => $limit, 'offset' => $offset]);

        if ($response->status() === Response::HTTP_FORBIDDEN) {
            // TODO
            $response->throw();
        } elseif (! $response->successful()) {
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
            'data' => LarderBookmarkResource::collection($foldersJson['results']),
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
        if (! empty($foldersJson['next'])) {
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
    public function getAllTags(int $limit = 20, int $offset = 0)
    {
        $path = 'tags/';

        $response = Http::withHeaders(array_merge($this->getDefaultHeaders(), $this->getAuthHeaders()))
            ->get("{$this->baseUrl}/{$path}", ['limit' => $limit, 'offset' => $offset]);

        if ($response->status() === Response::HTTP_FORBIDDEN) {
            // TODO
            $response->throw();
        } elseif (! $response->successful()) {
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
