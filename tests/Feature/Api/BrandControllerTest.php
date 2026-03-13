<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_brands_list()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Brand::factory()->count(3)->create();

        $response = $this->actingAsJWT($user)->getJson('/api/v1/brands');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_validates_required_fields_on_store()
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)->postJson('/api/v1/brands', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug']);
    }

    /** @test */
    public function user_can_update_brand_via_endpoint()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create([
            'name' => 'Old Brand',
            'slug' => 'old-brand',
        ]);

        $payload = [
            'name' => 'New Brand',
            'slug' => 'new-brand',
            'description' => 'Updated description',
            'image_url' => 'https://example.test/new-brand.png',
            'is_active' => true,
        ];

        $response = $this->actingAsJWT($user)
            ->putJson("/api/v1/brands/{$brand->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('New Brand', $brand->fresh()->name);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_brands()
    {
        $response = $this->getJson('/api/v1/brands');
        $response->assertStatus(401);
    }
}
