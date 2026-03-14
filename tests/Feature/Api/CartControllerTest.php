<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_cart_detail()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAsJWT($user)->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertStatus(201);

        $response = $this->actingAsJWT($user)->getJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_validates_required_fields_on_add_item()
    {
        $user = User::factory()->create();

        $response = $this->actingAsJWT($user)->postJson('/api/v1/cart/items', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'quantity']);
    }

    /** @test */
    public function user_can_update_cart_item_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAsJWT($user)->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertStatus(201);

        $response = $this->actingAsJWT($user)->putJson("/api/v1/cart/items/{$product->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['quantity' => 5]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_cart()
    {
        $response = $this->getJson('/api/v1/cart');
        $response->assertStatus(401);
    }
}
