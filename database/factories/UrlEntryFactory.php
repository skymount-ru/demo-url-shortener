<?php

namespace Database\Factories;

use App\Models\UrlEntry;
use App\Services\UrlShortenerService;
use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Factories\Factory;

class UrlEntryFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = UrlEntry::class;

    /**
     * @return array
     * @throws BindingResolutionException
     */
    public function definition()
    {
        $urlShortenerService = app()->make(UrlShortenerService::class);

        $url = $this->faker->url();
        $urlHash = $urlShortenerService->calculateUrlHash($url);
        $shortCode = $urlShortenerService->makeShortCode($url, 6);

        return [
            'url' => $url,
            'url_hash' => $urlHash['full'],
            'short_code' => $shortCode,
            'url_short_hash' => $urlHash['short'],
            'valid_until' => Carbon::now()->addMonth(),
        ];
    }
}
