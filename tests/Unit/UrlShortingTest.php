<?php

namespace Tests\Unit;

use App\Models\UrlEntry;
use App\Services\SafeBrowserUrl;
use App\Services\SafeBrowsing;
use App\Services\UrlShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class UrlShortingTest extends TestCase
{
    use RefreshDatabase;

    private string $url;

    protected function setUp(): void
    {
        parent::setUp();

        $this->url = 'https://test.local/' . uniqid('', true);
    }

    public function test_urlShortener_makesHash()
    {
        $urlShortenerService = app()->make(UrlShortenerService::class);

        $hashPack = $urlShortenerService->calculateUrlHash($this->url);

        $this->assertArrayHasKey('short', $hashPack);
        $this->assertArrayHasKey('full', $hashPack);
        $this->assertEquals(crc32($this->url), $hashPack['short'] ?? null);
    }

    public function test_urlShortener_makesShortcode()
    {
        $urlShortenerService = app()->make(UrlShortenerService::class);

        $shortCode = $urlShortenerService->makeShortCode($this->url, 6);

        $this->assertEquals(6, strlen($shortCode), 'Shortcode length is not as expected.');
        $this->assertEmpty(preg_replace('/[A-z20-9]/', '', $shortCode), 'Shortcode contains something else than [A-z0-9].');
    }

    public function test_urlShortener_createsEntry()
    {
        $this->mock(SafeBrowsing::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('validateUrl')
                ->withArgs(function ($arg) {
                    return $arg instanceof SafeBrowserUrl;
                })
                ->once();
        });
        $urlShortenerService = app()->make(UrlShortenerService::class);

        $hashPack = $urlShortenerService->calculateUrlHash($this->url);
        $urlEntry = $urlShortenerService->createNewShort($this->url);

        $this->assertNotNull($urlEntry);
        $this->assertEquals($this->url, $urlEntry->url);
        $this->assertEquals($hashPack['short'], $urlEntry->url_short_hash);
        $this->assertEquals($hashPack['full'], $urlEntry->url_hash);
        $this->assertNotEmpty($urlEntry->short_code);
    }

    public function test_urlShortener_returnsUrlByShortcode()
    {
        $urlShortenerService = app()->make(UrlShortenerService::class);

        /** @var UrlEntry $urlEntry */
        $urlEntry = UrlEntry::factory()->create();

        $this->assertNull($urlShortenerService->getUrlByShortCode('ERR' . random_int(111, 999)));
        $redirectUrl = $urlShortenerService->getUrlByShortCode($urlEntry->short_code);

        $this->assertEquals($urlEntry->url, $redirectUrl);
    }

//    public function test_safe_browsing()
//    {
//        $url = 'https://test.local/' . time();
//        SafeBrowsing::validateUrl($url);
//        // $this->expectException(SafeBrowsingFailedException::class);
//        // SafeBrowsing::validateUrl('http://www..i-a-n-f-e-t-t-e..org/');
//        $this->assertTrue(true);
//    }
}
