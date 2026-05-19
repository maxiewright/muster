<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Mission;
use App\Models\MissionMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MissionMembership>
 */
class MissionMembershipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'user_id' => User::factory(),
            'membership_type' => 'permanent',
            'added_by_user_id' => null,
            'removed_by_user_id' => null,
            'started_at' => now(),
            'ended_at' => null,
        ];
    }
}
