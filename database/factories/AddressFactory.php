<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Otomatis buat user jika tidak dipassing
            'label' => $this->faker->randomElement(['Rumah', 'Kantor', 'Kost']),
            'recipient_name' => $this->faker->name(),
            'recipient_phone' => $this->faker->phoneNumber(),
            'street' => $this->faker->streetAddress(),
            'subdistrict' => $this->faker->citySuffix(), // Kecamatan
            'district' => $this->faker->city(),       // Kabupaten
            'city' => $this->faker->city(),
            'province' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'is_primary' => false,
        ];
    }
}
