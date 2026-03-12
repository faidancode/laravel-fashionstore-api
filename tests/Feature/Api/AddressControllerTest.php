<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Address;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddressControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_their_own_addresses()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Address::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAsJWT($user)->getJson('/api/v1/addresses');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_validates_required_fields_on_store()
    {
        $user = User::factory()->create();

        // Kirim data kosong untuk memicu error DTO/Validation
        $response = $this->actingAsJWT($user)->postJson('/api/v1/addresses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['label', 'receiver', 'phone']);
    }

    /** @test */
    public function user_can_set_address_as_primary_via_endpoint()
    {
        $user = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'is_primary' => false
        ]);

        $response = $this->actingAsJWT($user)
            ->patchJson("/api/v1/addresses/{$address->id}/set-primary");

        $response->assertStatus(200);
        $this->assertTrue($address->fresh()->is_primary);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_addresses()
    {
        $response = $this->getJson('/api/v1/addresses');
        $response->assertStatus(401);
    }
}