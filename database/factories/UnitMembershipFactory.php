<?php

namespace Database\Factories;

use App\Enums\UnitMembershipRole;
use App\Models\Unit;
use App\Models\UnitMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitMembership>
 */
class UnitMembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'unit_id' => Unit::factory(),
            'role' => UnitMembershipRole::Member,
        ];
    }
}
