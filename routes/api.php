<?php

use App\Http\Controllers\API\v1\UrlEntriesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'as' => 'urls.',
    'prefix' => 'v1/urls',
    'middleware' => [
        'throttle:20,20',
    ],
], function () {
    Route::post('/', [UrlEntriesController::class, 'store'])->name('store');
});
