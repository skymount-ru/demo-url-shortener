<?php

namespace App\Services;

class SafeBrowserUrl
{
    public string $url;
    public string $urlHash;

    public function __construct(string $url, string $urlHash)
    {
        if (str_contains($url, '#')) {
            [$url, $_] = explode('#', $url, 2);
        }

        if (empty($url) || empty($urlHash)) {
            throw new \RuntimeException('Both parameters url and urlHash are requried.');
        }

        $this->url = $url;
        $this->urlHash = $urlHash;
    }
}
