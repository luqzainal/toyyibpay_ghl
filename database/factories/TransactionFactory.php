<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'ghl_order_id' => 'order_' . $this->faker->uuid(),
            'ghl_transaction_id' => 'txn_' . $this->faker->uuid(),
            'toyyibpay_billcode' => $this->faker->numerify('##########'),
            'toyyibpay_bill_id' => $this->faker->numerify('####'),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => 'MYR',
            'description' => $this->faker->sentence(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->email(),
            'customer_phone' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'environment' => $this->faker->randomElement(['sandbox', 'production']),
            'toyyibpay_callback_at' => null,
            'ghl_notified_at' => null,
            'toyyibpay_request_data' => [
                'billName' => $this->faker->sentence(3),
                'billDescription' => $this->faker->sentence(),
                'billPriceSetting' => 1,
                'billPayorInfo' => 1,
            ],
            'toyyibpay_response_data' => null,
            'ghl_webhook_data' => null,
        ];
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'toyyibpay_callback_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'ghl_notified_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'toyyibpay_callback_at' => null,
            'ghl_notified_at' => null,
        ]);
    }

    /**
     * Indicate that the transaction has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'toyyibpay_callback_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'ghl_notified_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the transaction is for production environment.
     */
    public function production(): static
    {
        return $this->state(fn (array $attributes) => [
            'environment' => 'production',
        ]);
    }

    /**
     * Indicate that the transaction is for sandbox environment.
     */
    public function sandbox(): static
    {
        return $this->state(fn (array $attributes) => [
            'environment' => 'sandbox',
        ]);
    }
}
