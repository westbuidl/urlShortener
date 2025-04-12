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

    /**
     * Encode a URL
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function encode(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url' => 'required|url'
            ]);

            $longUrl = $request->input('url');
            $shortUrl = $this->urlService->encode($longUrl);
            
            // Extract the short code for display
            $shortCode = basename($shortUrl);
            
            return response()->json([
                'original_url' => $longUrl,
                'short_url' => $shortUrl,
                'short_code' => $shortCode
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Decode a URL
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function decode(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url' => 'required|string'
            ]);

            $shortUrl = $request->input('url');
            $longUrl = $this->urlService->decode($shortUrl);
            $shortCode = basename($shortUrl);
            
            return response()->json([
                'short_url' => $shortUrl,
                'short_code' => $shortCode,
                'original_url' => $longUrl
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}