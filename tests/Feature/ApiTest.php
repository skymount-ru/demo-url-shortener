<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiTest extends TestCase
{
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
