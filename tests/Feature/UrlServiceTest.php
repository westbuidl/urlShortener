<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UrlShortenerTest extends TestCase
{
    /**
     
     *
     * @return void
     */
    public function testEncodeValidUrl()
    {
        $response = $this->postJson('/encode', [
            'url' => 'https://example.com/long/path/to/page?param=value'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'original_url',
                     'short_url',
                     'short_code'
                 ]);
                 
        
        $shortCode = $response->json('short_code');
        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{6}$/', $shortCode);
        
        
        $shortUrl = $response->json('short_url');
        $this->assertEquals("http://short.est/{$shortCode}", $shortUrl);
    }

    /**
     
     *
     * @return void
     */
    public function testEncodeProducesDifferentCodes()
    {
        $response1 = $this->postJson('/encode', [
            'url' => 'https://example.com/page1'
        ]);
        
        $response2 = $this->postJson('/encode', [
            'url' => 'https://example.com/page2'
        ]);
        
        $shortCode1 = $response1->json('short_code');
        $shortCode2 = $response2->json('short_code');
        
        
        $this->assertNotEquals($shortCode1, $shortCode2);
    }

    /**
     
     *
     * @return void
     */
    public function testEncodeInvalidUrl()
    {
        $response = $this->postJson('/encode', [
            'url' => 'not-a-valid-url'
        ]);

        $response->assertStatus(400);
    }

    /**
     
     *
     * @return void
     */
    public function testDecodeUrl()
    {
       
        $encodeResponse = $this->postJson('/encode', [
            'url' => 'https://example.com/test-decode'
        ]);
        
        $shortUrl = $encodeResponse->json('short_url');
        $originalUrl = $encodeResponse->json('original_url');
        
       
        $decodeResponse = $this->postJson('/decode', [
            'url' => $shortUrl
        ]);

        $decodeResponse->assertStatus(200)
                       ->assertJson([
                           'original_url' => $originalUrl,
                           'short_url' => $shortUrl
                       ]);
    }

    /**
    
     *
     * @return void
     */
    public function testDecodeNonExistentUrl()
    {
        $response = $this->postJson('/decode', [
            'url' => 'http://short.est/nonexistent'
        ]);

        $response->assertStatus(400);
    }
}