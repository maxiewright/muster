<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

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
            'role' => Role::Member->value,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Refresh to load database-defaulted columns (points, current_streak, longest_streak)
            // that are not mass-assignable, ensuring Model::shouldBeStrict() compatibility.
            $user->refresh();
        });
    }

    /**
     * Set gamification stats (points, streaks) on the user.
     * These fields are guarded and set via forceFill after creation.
     */
    public function withStats(int $points = 0, int $currentStreak = 0, int $longestStreak = 0): static
    {
        return $this->afterCreating(function (User $user) use ($points, $currentStreak, $longestStreak) {
            $user->forceFill([
                'points' => $points,
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
            ])->save();
        });
    }

    public function lead(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Lead->value,
        ]);
    }

    public function platformAdmin(): static
    {
        return $this->lead()->state(fn (array $attributes) => [
            'is_platform_admin' => true,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
