<?php

namespace App\Http\Controllers;

use App\Services\UrlService;
use InvalidArgumentException;

class RedirectController extends Controller
{
    protected $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    /**
     * Redirect short URL to original URL
     *
     * @param string $shortCode
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($shortCode)
    {
        try {
            $shortUrl = "http://short.est/{$shortCode}";
            $longUrl = $this->urlService->decode($shortUrl);
            return redirect($longUrl);
        } catch (InvalidArgumentException $e) {
            abort(404, 'Short URL not found');
        } catch (\Exception $e) {
            abort(500, 'An error occurred');
        }
    }
}