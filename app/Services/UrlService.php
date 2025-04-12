<?php

namespace App\Services;

use App\Utils\Base62;
use InvalidArgumentException;

class UrlService 
{
    private $storageFile;
    private $urlMap;
    private $counter;
    private $shortCodeLength = 6;  

    public function __construct()
    {
        
        $storageDir = storage_path('app/url_shortener');
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $this->storageFile = $storageDir . '/url_data.json';
        $this->loadData();
    }

    
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

   
    private function saveData()
    {
        $data = [
            'urlMap' => $this->urlMap,
            'counter' => $this->counter
        ];
        file_put_contents($this->storageFile, json_encode($data));
    }

    /**
    
     *
     * @param string $longUrl
     * @return string
     */
    public function encode($longUrl)
    {
        if (!$this->isValidUrl($longUrl)) {
            throw new InvalidArgumentException('Invalid URL');
        }
        
        
        foreach ($this->urlMap as $shortCode => $original) {
            if ($original === $longUrl) {
                return "http://short.est/{$shortCode}";
            }
        }
        
       
        $this->counter++;
        
        
        $shortCode = Base62::encode($this->counter, $this->shortCodeLength);
        
        
        while (isset($this->urlMap[$shortCode])) {

            $shortCode = Base62::random($this->shortCodeLength);
        }
        
        $this->urlMap[$shortCode] = $longUrl;
        
       
        $this->saveData();
        
        return "http://short.est/{$shortCode}";
    }

    /**
     
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
    
     *
     * @param string $url
     * @return bool
     */
    private function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}