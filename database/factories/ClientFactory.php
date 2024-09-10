<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'surnom' => $this->faker->word,
            'telephone' => $this->faker->phoneNumber,
            'address' => $this->faker->optional()->address,
            'user_id' => null,
        ];
    }
}