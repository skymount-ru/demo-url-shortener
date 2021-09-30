<?php

namespace App\Http\Controllers\API\v1;

use App\Exceptions\UrlExistsException;
use App\Http\Controllers\Controller;
use App\Http\Services\UrlShortenerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UrlEntriesController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = Validator::make($request->post(), [
                'url' => ['required', 'string', 'min:11', 'max:2048', 'url'],
            ], [], [
                'url' => 'URL',
            ])->validate();

            $urlEntry = UrlShortenerService::createNewShort($validated['url']);

            $respData = [
                'code' => 200,
                'link' => route('shortCodeRedirect', ['shortCode' => $urlEntry->short_code]),
            ];
        } catch (ValidationException $e) {
            $respData = [
                'code' => 400,
                'message' => $e->errors(),
            ];
        } catch (UrlExistsException $e) {
            $respData = [
                'code' => 400,
                'message' => $e->getMessage(),
                'link' => route('shortCodeRedirect', ['shortCode' => $e->getUrlEntry()->short_code]),
            ];
        } catch (\Exception $e) {
            $respData = [
                'code' => 400,
                'message' => $e->getMessage(),
            ];
        }

        return response()->json($respData);
    }
}
