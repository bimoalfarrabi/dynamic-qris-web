<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(1000, 1000000);
        $uniqueCode = $this->faker->numberBetween(100, 999);

        return [
            'qrisify_transaction_id' => Str::uuid(),
            'external_id' => 'ORDER-'.$this->faker->unique()->numberBetween(1, 99999),
            'amount_requested' => $amount,
            'unique_code' => $uniqueCode,
            'amount_total' => $amount + $uniqueCode,
            'status' => 'PENDING',
            'qris_string' => '00020101021226'.Str::random(50),
            'webhook_status' => 'UNSENT',
            'payment_provider' => null,
            'expires_at' => now()->addMinutes(15),
            'paid_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function success(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'SUCCESS',
            'webhook_status' => 'SENT_SUCCESS',
            'payment_provider' => 'gopay',
            'paid_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'CANCELLED',
            'cancelled_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'EXPIRED',
            'expires_at' => now()->subMinutes(5),
        ]);
    }
}
