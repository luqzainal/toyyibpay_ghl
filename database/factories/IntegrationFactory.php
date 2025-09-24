<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Integration>
 */
class IntegrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => 'loc_' . $this->faker->uuid(),
            'company_id' => 'comp_' . $this->faker->uuid(),
            'access_token' => 'ghl_' . $this->faker->sha256(),
            'refresh_token' => 'refresh_' . $this->faker->sha256(),
            'api_key' => 'api_' . $this->faker->uuid(),
            'installed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'uninstalled_at' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the integration is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'uninstalled_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the integration is newly installed.
     */
    public function newlyInstalled(): static
    {
        return $this->state(fn (array $attributes) => [
            'installed_at' => now(),
            'uninstalled_at' => null,
            'is_active' => true,
        ]);
    }
}
