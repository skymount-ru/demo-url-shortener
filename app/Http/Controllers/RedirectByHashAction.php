<?php

namespace App\Http\Controllers;

use App\Http\Services\UrlShortenerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectByHashAction extends Controller
{
    public function __invoke(string $shortCode): RedirectResponse
    {
        $redirectUrl = Cache::remember($shortCode, new \DateInterval('P1D'), function () use ($shortCode) {
            return UrlShortenerService::getUrlByShortCode($shortCode);
        });

        if (empty($redirectUrl)) {
            throw new NotFoundHttpException('Sorry! No URL found for this short link.');
        }

        return response()->redirectTo($redirectUrl);
    }
}
