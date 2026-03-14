<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_products_list()
    {
        $user = User::factory()->create();
        Product::factory()->count(3)->create();

        $response = $this->actingAsJWT($user)->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_validates_required_fields_on_store()
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)->postJson('/api/v1/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'category_id',
                'brand_id',
                'name',
                'slug',
                'price',
                'stock',
            ]);
    }

    /** @test */
    public function user_can_update_product_via_endpoint()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $payload = [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'New Product',
            'slug' => 'new-product',
            'description' => 'Updated description',
            'price' => 250000,
            'stock' => 15,
            'sku' => 'SKU-9999',
            'image_url' => 'https://example.test/new-product.png',
            'is_active' => true,
        ];

        $response = $this->actingAsJWT($user)
            ->putJson("/api/v1/products/{$product->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('New Product', $product->fresh()->name);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_products()
    {
        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(401);
    }
}
