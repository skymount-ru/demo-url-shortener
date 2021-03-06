<?php

namespace App\Services;

use App\Exceptions\SafeBrowsingFailedException;
use App\Exceptions\UrlExistsException;
use App\Models\UrlEntry;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use RuntimeException;
use Tuupola\Base62;

class UrlShortenerService
{
    private const SHORTCODE_REGEN_MAX_TRIES = 10;
    private const DB_EXCEPTION_SAYS_URL_EXISTS = 'url_entries_url_short_hash_url_hash_unique';
    private SafeBrowsing $safeBrowsing;

    public function __construct(SafeBrowsing $safeBrowsing)
    {
        $this->safeBrowsing = $safeBrowsing;
    }

    public function getUrlByShortCode(string $shortCode): string|null
    {
        return $this->findByShortCode($shortCode)?->url;
    }

    /**
     * @throws UrlExistsException|RuntimeException|SafeBrowsingFailedException
     */
    public function createNewShort(string $url): UrlEntry
    {
        /** @var UrlEntry|null $urlEntry */
        $urlEntry = $this->findByUrl($url);

        if ($urlEntry) {
            throw new UrlExistsException($urlEntry);
        }

        $urlHash = $this->calculateUrlHash($url);

        $this->safeBrowsing->validateUrl(new SafeBrowserUrl($url, $urlHash['full']));

        return DB::transaction(function () use ($url, $urlHash) {

            $urlEntry = new UrlEntry();
            $urlEntry->url = $url;
            $urlEntry->url_hash = $urlHash['full'];
            $urlEntry->url_short_hash = $urlHash['short'];
            $urlEntry->valid_until = Carbon::now()->addMonth();

            $shortCodeRegenTry = 0;
            do {
                $urlEntry->short_code = $this->makeShortCode($url, 6);
                try {
                    if ($urlEntry->save()) {
                        return $urlEntry;
                    }

                } catch (Exception $e) {
                    if ((int)$e->getCode() === 23000 && (int)@$e->errorInfo[1] === 1062) {
                        if (str_contains($e->getMessage(), self::DB_EXCEPTION_SAYS_URL_EXISTS)) {
                            throw new UrlExistsException($urlEntry, 'The URL has already been added.');
                        }
                    } else {
                        break;
                    }
                }
            } while (++$shortCodeRegenTry < self::SHORTCODE_REGEN_MAX_TRIES);

            throw new RuntimeException('Failed to store the URL.');
        });
    }

    private function findByUrl(string $url): ?UrlEntry
    {
        $urlHash = $this->calculateUrlHash($url);

        /** @var UrlEntry|null $entry */
        $entry = UrlEntry::query()
            ->where('url_short_hash', $urlHash['short'])
            ->where('url_hash', $urlHash['full'])
            ->first();

        return $entry;
    }

    private function findByShortCode(string $shortCode): ?UrlEntry
    {
        /** @var UrlEntry|null $entry */
        $entry = UrlEntry::query()
            ->where('short_code', $shortCode)
            ->first();

        return $entry;
    }

    public function makeShortCode(string $url, int $codeLength = 10): string
    {
        $md5UrlHashPart = substr(md5(uniqid($url, true)), 0, $codeLength);
        return substr((new Base62)->encode($md5UrlHashPart), 0, $codeLength);
    }

    #[ArrayShape(['short' => "int", 'full' => "string"])]
    public function calculateUrlHash(string $url): array
    {
        return [
            'short' => crc32($url),
            'full' => hash('sha256', $url) ?: md5($url),
        ];
    }
}
