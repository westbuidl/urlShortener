<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UrlShortenerTest extends TestCase
{
    /**
     * Test encoding a valid URL
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
                 
        // Verify the short code is alphanumeric and proper length
        $shortCode = $response->json('short_code');
        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{6}$/', $shortCode);
        
        // Verify the short URL has the correct format
        $shortUrl = $response->json('short_url');
        $this->assertEquals("http://short.est/{$shortCode}", $shortUrl);
    }

    /**
     * Test encoding different URLs produces different codes
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
        
        // The short codes should be different for different URLs
        $this->assertNotEquals($shortCode1, $shortCode2);
    }

    /**
     * Test encoding an invalid URL
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
     * Test decoding a short URL
     *
     * @return void
     */
    public function testDecodeUrl()
    {
        // First encode a URL
        $encodeResponse = $this->postJson('/encode', [
            'url' => 'https://example.com/test-decode'
        ]);
        
        $shortUrl = $encodeResponse->json('short_url');
        $originalUrl = $encodeResponse->json('original_url');
        
        // Now try to decode it
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
     * Test decoding a non-existent short URL
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