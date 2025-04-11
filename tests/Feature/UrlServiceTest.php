<?php

namespace Tests\Feature;

use App\Services\UrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlServiceTest extends TestCase
{
    protected $urlService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlService = app(UrlService::class);
        session()->flush();
    }

    public function test_encodes_valid_url()
    {
        $longUrl = 'https://www.example.com';
        $response = $this->postJson('/api/encode', ['url' => $longUrl]);
        $response->assertStatus(200)
                 ->assertJsonStructure(['shortUrl'])
                 ->assertJsonFragment(['shortUrl' => $this->urlService->encode($longUrl)]);
    }

    public function test_decodes_short_url()
    {
        $longUrl = 'https://www.example.com';
        $shortUrl = $this->urlService->encode($longUrl);
        $response = $this->postJson('/api/decode', ['url' => $shortUrl]);
        $response->assertStatus(200)
                 ->assertJson(['longUrl' => $longUrl]);
    }

    public function test_returns_same_short_url_for_duplicate_long_url()
    {
        $longUrl = 'https://www.example.com';
        $shortUrl1 = $this->urlService->encode($longUrl);
        $shortUrl2 = $this->urlService->encode($longUrl);
        $this->assertEquals($shortUrl1, $shortUrl2);
    }

    public function test_fails_for_invalid_url()
    {
        $response = $this->postJson('/api/encode', ['url' => 'invalid-url']);
        $response->assertStatus(400)
                 ->assertJsonStructure(['error']);
    }

    public function test_fails_for_non_existent_short_url()
    {
        $response = $this->postJson('/api/decode', ['url' => 'http://short.est/999']);
        $response->assertStatus(400)
                 ->assertJson(['error' => 'Short URL not found']);
    }

    public function test_handles_multiple_urls()
    {
        $url1 = 'https://www.example1.com';
        $url2 = 'https://www.example2.com';
        $short1 = $this->urlService->encode($url1);
        $short2 = $this->urlService->encode($url2);
        $this->assertEquals($url1, $this->urlService->decode($short1));
        $this->assertEquals($url2, $this->urlService->decode($short2));
        $this->assertNotEquals($short1, $short2);
    }
}