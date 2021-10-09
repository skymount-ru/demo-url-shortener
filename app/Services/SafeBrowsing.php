<?php

namespace App\Services;

use App\AppDefaults;
use App\Exceptions\SafeBrowsingFailedException;
use App\Models\UnsafeUrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SafeBrowsing
{
    private const GOOGLE_SAFE_BROWSING_URL = 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=%s';

    /**
     * @var array|SafeBrowserUrl[]
     */
    private array $urls = [];
    private array $requestBody = [];

    /**
     * @param SafeBrowserUrl $url
     * @throws SafeBrowsingFailedException
     */
    public function validateUrl(SafeBrowserUrl $url): void
    {
        $this->addUrl($url);
        $this->validate();
    }

    /**
     * @param SafeBrowserUrl $safeBrowserUrl
     */
    private function addUrl(SafeBrowserUrl $safeBrowserUrl): void
    {
        DB::transaction(function () use ($safeBrowserUrl) {
            if (UnsafeUrl::query()->where('url_hash', $safeBrowserUrl->urlHash)->exists()) {
                throw new SafeBrowsingFailedException('The URL is not safe (cached).');
            }
        }, AppDefaults::DB_TRANSACTION_RETRIES);

        $this->urls[$safeBrowserUrl->urlHash] = $safeBrowserUrl->url;
    }

    /**
     * @throws SafeBrowsingFailedException
     */
    private function validate(): void
    {
        if (empty($this->urls)) {
            throw new SafeBrowsingFailedException('No URL to check with SafeBrowsing.');
        }

        $apiKey = env('GOOGLE_API_KEY');
        if (empty($apiKey)) {
            throw new SafeBrowsingFailedException('URL Validation is not fully configured.');
        }

        $this->makeRequestBody();

        $response = Http::post(
            sprintf(self::GOOGLE_SAFE_BROWSING_URL, $apiKey),
            $this->requestBody
        );

        if (!$response->successful()) {
            throw new SafeBrowsingFailedException('Failed to check the URL with SafeBrowsing.');
        }

        if (!empty($response->json())) {
            DB::transaction(function () use ($response) {
                foreach ($response->json('matches', []) as $urlMatch) {
                    $url = $urlMatch['threat']['url'] ?? null;
                    if (empty($url)) { continue; }
                    $hash = array_search($url, $this->urls, true);
                    if ($hash) {
                        UnsafeUrl::create(['url_hash' => $hash]);
                    }
                }
            }, AppDefaults::DB_TRANSACTION_RETRIES);

            throw new SafeBrowsingFailedException('The URL is not safe.');
        }
    }

    private function makeRequestBody(): void
    {
        $this->requestBody = [
            'client' => [
                'clientId' => env('APP_NAME', 'DEV_APP'),
                'clientVersion' => app()->version(). '-' . env('APP_VERSION', 'dev'),
            ],
            'threatInfo' => [
                'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING', 'POTENTIALLY_HARMFUL_APPLICATION'],
                'platformTypes' => ['ANY_PLATFORM'],
                'threatEntryTypes' => ['URL'],
                'threatEntries' => array_values(array_map(static function (string $el) {
                    return ['url' => $el];
                }, $this->urls)),
            ],
        ];
    }
}
