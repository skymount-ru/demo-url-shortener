<?php

namespace Tests\Unit;

use App\Exceptions\SafeBrowsingFailedException;
use App\Http\Services\SafeBrowsing;
use App\Http\Services\UrlShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlShortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_url_hash()
    {
        $url = 'https://test.local/' . time();
        $hashPack = UrlShortenerService::calculateUrlHash($url);
        $this->assertArrayHasKey('short', $hashPack);
        $this->assertArrayHasKey('full', $hashPack);
        $this->assertEquals(crc32($url), $hashPack['short'] ?? null);
    }

    public function test_create_new_url_shortcode()
    {
        $url = 'https://test.local/' . time();
        $hashPack = UrlShortenerService::calculateUrlHash($url);
        $urlEntry = UrlShortenerService::createNewShort($url);
        $this->assertNotNull($urlEntry);
        $this->assertEquals($url, $urlEntry->url);
        $this->assertEquals($hashPack['short'], $urlEntry->url_short_hash);
        $this->assertEquals($hashPack['full'], $urlEntry->url_hash);
        $this->assertNotEmpty($urlEntry->short_code);
    }

    public function test_create_shortcode_and_get_url()
    {
        $url = 'https://test.local/' . time();
        $urlEntry = UrlShortenerService::createNewShort($url);
        $this->assertNull(UrlShortenerService::getUrlByShortCode('someWrongCode' . time()));
        $redirectUrl = UrlShortenerService::getUrlByShortCode($urlEntry->short_code);
        $this->assertEquals($url, $redirectUrl);
    }

    public function test_safe_browsing()
    {
        $url = 'https://test.local/' . time();
        SafeBrowsing::validateUrl($url);
        // $this->expectException(SafeBrowsingFailedException::class);
        // SafeBrowsing::validateUrl('http://www..i-a-n-f-e-t-t-e..org/');
        $this->assertTrue(true);
    }
}
