<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Mission;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Mission>
 */
class MissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->sentence(3);

        return [
            'organization_id' => Organization::factory(),
            'unit_id' => Unit::factory(),
            'mission_commander_user_id' => User::factory()->lead(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
