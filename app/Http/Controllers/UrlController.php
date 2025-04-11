<?php

namespace App\Http\Controllers;

use App\Services\UrlService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class UrlController extends Controller
{
    protected $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    public function encode(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url' => 'required|url'
            ]);

            $shortUrl = $this->urlService->encode($request->input('url'));
            return response()->json(['shortUrl' => $shortUrl]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid URL'], 400);
        }
    }

    public function decode(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url' => 'required|url'
            ]);

            $longUrl = $this->urlService->decode($request->input('url'));
            return response()->json(['longUrl' => $longUrl]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid short URL'], 400);
        }
    }
}