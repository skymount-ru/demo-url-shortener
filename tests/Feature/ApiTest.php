<?php

namespace Tests\Feature;

use App\Services\SafeBrowserUrl;
use App\Services\SafeBrowsing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(SafeBrowsing::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('validateUrl')
                ->withArgs(function ($arg) {
                    return $arg instanceof SafeBrowserUrl;
                })
                ->once();
        });
    }

    public function test_create_shorten_url()
    {
        $url = 'https://local.test/' . time();
        $response = $this->post('/api/v1/urls', ['url' => $url]);
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['link', 'code']);
    }

    public function test_create_short_link_and_redirect()
    {
        $url = 'https://local.test/' . time();

        $responseOnGenerateLink = $this->post('/api/v1/urls', ['url' => $url]);
        $responseOnProcessLink = $this->get($responseOnGenerateLink->json('link'));

        $responseOnProcessLink->assertRedirect($url);
    }
}
