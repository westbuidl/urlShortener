<?php

namespace App\Services;

use App\Utils\Base62;
use InvalidArgumentException;

class UrlService 
{
    private $storageFile;
    private $urlMap;
    private $counter;
    private $shortCodeLength = 6;  // Default length for short codes

    public function __construct()
    {
        // Create a storage directory if it doesn't exist
        $storageDir = storage_path('app/url_shortener');
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $this->storageFile = $storageDir . '/url_data.json';
        $this->loadData();
    }

    /**
     * Load data from storage file
     */
    private function loadData()
    {
        if (file_exists($this->storageFile)) {
            $data = json_decode(file_get_contents($this->storageFile), true);
            $this->urlMap = $data['urlMap'] ?? [];
            $this->counter = $data['counter'] ?? 0;
        } else {
            $this->urlMap = [];
            $this->counter = 0;
        }
    }

    /**
     * Save data to storage file
     */
    private function saveData()
    {
        $data = [
            'urlMap' => $this->urlMap,
            'counter' => $this->counter
        ];
        file_put_contents($this->storageFile, json_encode($data));
    }

    /**
     * Encode a long URL to a short URL
     *
     * @param string $longUrl
     * @return string
     */
    public function encode($longUrl)
    {
        if (!$this->isValidUrl($longUrl)) {
            throw new InvalidArgumentException('Invalid URL');
        }
        
        // Check if URL already exists in the map
        foreach ($this->urlMap as $shortCode => $original) {
            if ($original === $longUrl) {
                return "http://short.est/{$shortCode}";
            }
        }
        
        // Generate new short code
        $this->counter++;
        
        // Use the improved Base62 encoding to get a more distributed code
        $shortCode = Base62::encode($this->counter, $this->shortCodeLength);
        
        // Ensure the short code is unique
        while (isset($this->urlMap[$shortCode])) {
            // If we somehow got a collision, try again with a different approach
            $shortCode = Base62::random($this->shortCodeLength);
        }
        
        $this->urlMap[$shortCode] = $longUrl;
        
        // Save data
        $this->saveData();
        
        return "http://short.est/{$shortCode}";
    }

    /**
     * Decode a short URL to its original long URL
     *
     * @param string $shortUrl
     * @return string
     */
    public function decode($shortUrl)
    {
        $shortCode = basename($shortUrl);
        
        if (!isset($this->urlMap[$shortCode])) {
            throw new InvalidArgumentException('Short URL not found');
        }
        
        return $this->urlMap[$shortCode];
    }

    /**
     * Check if a URL is valid
     *
     * @param string $url
     * @return bool
     */
    private function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}