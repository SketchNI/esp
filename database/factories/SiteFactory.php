<?php

namespace Database\Factories;

use App\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'url' => $this->faker->url(),
            'secure' => mt_rand(0, 1),
            'path' => '/var/www/html',
        ];
    }
}
