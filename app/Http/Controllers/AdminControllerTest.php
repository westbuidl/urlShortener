<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Seller;
use App\Models\CompanySeller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminCanViewAllSellers()
    {
        // Create an admin
        $admin = Admin::factory()->create();

        // Acting as the authenticated admin
        $this->actingAs($admin, 'admin');

        // Hit the getAllSellers endpoint
        $response = $this->getJson('/admin/sellers');

        // Assert the response
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'individual_sellers',
                         'company_sellers',
                         'total_individual_sellers',
                         'total_company_sellers',
                     ]
                 ]);
    }

    public function testNonAdminCannotViewAllSellers()
    {
        // Create a regular user
        $user = User::factory()->create();

        // Acting as the authenticated user
        $this->actingAs($user);

        // Hit the getAllSellers endpoint
        $response = $this->getJson('/admin/sellers');

        // Assert the response
        $response->assertStatus(403)
                 ->assertJson([
                     'message' => 'Unauthorized access.'
                 ]);
    }
}
