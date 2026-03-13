<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_categories_list()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Category::factory()->count(3)->create();

        $response = $this->actingAsJWT($user)->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_validates_required_fields_on_store()
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)->postJson('/api/v1/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'slug']);
    }

    /** @test */
    public function user_can_update_category_via_endpoint()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'name' => 'Old Category',
            'slug' => 'old-category',
        ]);

        $payload = [
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'Updated description',
            'image_url' => 'https://example.test/new-category.png',
            'is_active' => true,
        ];

        $response = $this->actingAsJWT($user)
            ->putJson("/api/v1/categories/{$category->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('New Category', $category->fresh()->name);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_categories()
    {
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(401);
    }
}
