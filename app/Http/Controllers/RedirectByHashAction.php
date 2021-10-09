<?php

namespace App\Http\Controllers;

use App\Services\UrlShortenerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RedirectByHashAction extends Controller
{
    private const AUTO_DELETE_MAX_LIMIT = 500;

    public function __invoke(UrlShortenerService $urlShortenerService, string $shortCode): RedirectResponse
    {
        $redirectUrl = Cache::remember($shortCode, new \DateInterval('P1D'), function () use ($urlShortenerService, $shortCode) {
            return $urlShortenerService->getUrlByShortCode($shortCode);
        });

        if (empty($redirectUrl)) {
            throw new NotFoundHttpException('Sorry! No URL found for this short link.');
        }

        $this->makeCleanUp();

        return response()->redirectTo($redirectUrl);
    }

    /**
     * Delete the old urls.
     */
    private function makeCleanUp(): void
    {
        if (random_int(0, 100) > 2) {
            return;
        }

        DB::table('url_entries')
            ->where('valid_until', '<=', Carbon::now()->toDateTimeString())
            ->limit(self::AUTO_DELETE_MAX_LIMIT)
            ->delete();
    }
}
