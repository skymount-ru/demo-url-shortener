<?php

use App\Http\Controllers\RedirectByHashAction;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/', 'home');

Route::get('/{shortCode}', RedirectByHashAction::class)
    ->where('shortCode', '^[A-z0-9]{6}$')
    ->name('shortCodeRedirect');

Route::get('/something/{shortCode}', RedirectByHashAction::class)
    ->where('shortCode', '^[A-z0-9]{6}$');
