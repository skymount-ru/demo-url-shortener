<?php

namespace App\Http\Services;

use App\AppDefaults;
use App\Exceptions\SafeBrowsingFailedException;
use App\Models\UnsafeUrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SafeBrowsing
{
    private const GOOGLE_SAFE_BROWSING_URL = 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=%s';

    private array $urls = [];
    private array $requestBody = [];

    /**
     * @param string $url
     * @throws SafeBrowsingFailedException
     */
    public static function validateUrl(string $url): void
    {
        $handler = new self();
        $handler->addUrl($url);
        $handler->validate();
    }

    /**
     * @throws SafeBrowsingFailedException
     */
    private function addUrl(string $url): void
    {
        if (empty($url)) {
            return;
        }

        if (str_contains($url, '#')) {
            [$url, $_] = explode('#', $url, 2);
        }

        DB::transaction(function () use ($url) {
            if (UnsafeUrl::query()->where('url_hash', UrlShortenerService::calculateUrlHash($url)['full'])->exists()) {
                throw new SafeBrowsingFailedException('The URL is not safe (cached).');
            }
        }, AppDefaults::DB_TRANSACTION_RETRIES);

        $this->urls[] = $url;
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
                    if (!$url) { continue; }
                    UnsafeUrl::create([
                        'url_hash' => UrlShortenerService::calculateUrlHash($url)['full'],
                    ]);
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
                'threatEntries' => array_map(static function ($el) {
                    return ['url' => $el];
                }, $this->urls),
            ],
        ];
    }
}
