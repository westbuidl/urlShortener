<?php

namespace App\Services;

use App\Utils\Base62;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class UrlService
{
    private $counterKey = 'url_counter';
    private $urlMapKey = 'url_map';

    public function encode($longUrl)
    {
        if (!$this->isValidUrl($longUrl)) {
            throw new InvalidArgumentException('Invalid URL');
        }

        
        if (!Session::has($this->urlMapKey)) {
            Session::put($this->urlMapKey, []);
        }
        if (!Session::has($this->counterKey)) {
            Session::put($this->counterKey, 0);
        }

        
        $urlMap = Session::get($this->urlMapKey);
        foreach ($urlMap as $shortCode => $original) {
            if ($original === $longUrl) {
                return "http://tiny.test/{$shortCode}";
            }
        }

        
        $counter = Session::get($this->counterKey) + 1;
        Session::put($this->counterKey, $counter);
        $shortCode = Base62::encode($counter);
        $urlMap[$shortCode] = $longUrl;
        Session::put($this->urlMapKey, $urlMap);

        return "http://tiny.test/{$shortCode}";
    }

    public function decode($shortUrl)
    {
        $shortCode = basename($shortUrl);
        $urlMap = Session::get($this->urlMapKey, []);

        if (!isset($urlMap[$shortCode])) {
            throw new InvalidArgumentException('Short URL not found');
        }

        return $urlMap[$shortCode];
    }

    private function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}