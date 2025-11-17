<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'street_address' => fake()->streetAddress(),
            'email_verified_at' => null,
            'verification_token' => null,
            'verification_token_expires_at' => null,
            'residency_status' => 'pending',
            'residency_validated_at' => null,
            'residency_validated_by' => null,
            'party_affiliation' => null,
            'party_assigned_at' => null,
            'party_assigned_by' => null,
            'user_id' => null,
        ];
    }

    /**
     * Indicate that the application's email is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the application has an approved residency status.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'residency_status' => 'approved',
            'residency_validated_at' => now(),
        ]);
    }

    /**
     * Indicate that the application has a rejected residency status.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'residency_status' => 'rejected',
            'residency_validated_at' => now(),
        ]);
    }
}
