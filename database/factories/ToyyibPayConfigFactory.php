<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ToyyibPayConfig>
 */
class ToyyibPayConfigFactory extends Factory
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
            'secret_key_live' => 'live_' . $this->faker->sha256(),
            'category_code_live' => $this->faker->randomElement(['0001', '0002', '0003', '0004']),
            'secret_key_sandbox' => 'sandbox_' . $this->faker->sha256(),
            'category_code_sandbox' => $this->faker->randomElement(['0001', '0002', '0003', '0004']),
            'mode_active' => 'sandbox',
            'is_configured' => true,
            'configured_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the config is in production mode.
     */
    public function production(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_active' => 'production',
        ]);
    }

    /**
     * Indicate that the config is not configured yet.
     */
    public function unconfigured(): static
    {
        return $this->state(fn (array $attributes) => [
            'secret_key_live' => null,
            'category_code_live' => null,
            'secret_key_sandbox' => null,
            'category_code_sandbox' => null,
            'is_configured' => false,
            'configured_at' => null,
        ]);
    }

    /**
     * Indicate that only sandbox is configured.
     */
    public function sandboxOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'secret_key_live' => null,
            'category_code_live' => null,
            'mode_active' => 'sandbox',
        ]);
    }
}
