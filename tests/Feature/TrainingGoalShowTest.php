<?php

declare(strict_types=1);

use App\Enums\PartnerStatus;
use App\Enums\TrainingCategory;
use App\Models\TrainingGoal;
use App\Models\TrainingMilestone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Helper to create a TrainingGoal with sensible defaults.
 *
 * @param  array<string, mixed>  $attributes
 */
function makeGoal(array $attributes = []): TrainingGoal
{
    return TrainingGoal::create(array_merge([
        'slug' => Str::slug('test goal').'-'.Str::random(6),
        'user_id' => User::factory()->create()->id,
        'title' => 'Test Goal',
        'category' => TrainingCategory::Technical->value,
        'start_date' => now()->toDateString(),
        'target_date' => now()->addMonths(3)->toDateString(),
        'status' => 'active',
        'partner_status' => 'pending',
        'is_public' => true,
    ], $attributes));
}

it('redirects unauthenticated users to the login page', function (): void {
    $goal = makeGoal(['is_public' => true]);

    $this->get(route('training.goals.show', $goal))
        ->assertRedirect(route('login'));
});

it('allows the owner to view their private goal', function (): void {
    $owner = User::factory()->create();
    $goal = makeGoal(['user_id' => $owner->id, 'is_public' => false]);

    $this->actingAs($owner)
        ->get(route('training.goals.show', $goal))
        ->assertOk();
});

it('allows the accountability partner to view a private goal', function (): void {
    $partner = User::factory()->create();
    $goal = makeGoal([
        'is_public' => false,
        'accountability_partner_id' => $partner->id,
    ]);

    $this->actingAs($partner)
        ->get(route('training.goals.show', $goal))
        ->assertOk();
});

it('denies other authenticated users access to a private goal', function (): void {
    $otherUser = User::factory()->create();
    $goal = makeGoal(['is_public' => false]);

    $this->actingAs($otherUser)
        ->get(route('training.goals.show', $goal))
        ->assertForbidden();
});

it('allows any authenticated user to view a public goal', function (): void {
    $user = User::factory()->create();
    $goal = makeGoal(['is_public' => true]);

    $this->actingAs($user)
        ->get(route('training.goals.show', $goal))
        ->assertOk();
});

it('prevents verifying a milestone belonging to a different goal', function (): void {
    $partner = User::factory()->create();
    $owner = User::factory()->create();

    $goal = makeGoal([
        'user_id' => $owner->id,
        'is_public' => true,
        'accountability_partner_id' => $partner->id,
        'partner_status' => PartnerStatus::Accepted->value,
        'status' => 'active',
    ]);

    /** @var TrainingGoal $otherGoal */
    $otherGoal = makeGoal(['user_id' => $owner->id, 'is_public' => true]);

    $milestoneBelongingToOtherGoal = TrainingMilestone::create([
        'training_goal_id' => $otherGoal->id,
        'title' => 'Other Milestone',
        'order' => 1,
        'status' => 'pending',
    ]);

    // The scoped findOrFail() correctly rejects the foreign milestone,
    // preventing cross-goal milestone manipulation.
    expect(fn () => \Livewire\Livewire::actingAs($partner)
        ->test(\App\Livewire\Training\TrainingGoalShow::class, ['goal' => $goal])
        ->call('verifyMilestone', $milestoneBelongingToOtherGoal->id, app(\App\Services\TrainingGamificationService::class))
    )->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

it('forbids a non-partner from calling verifyGoal', function (): void {
    $randomUser = User::factory()->create();
    $owner = User::factory()->create();

    $goal = makeGoal([
        'user_id' => $owner->id,
        'is_public' => true,
        'status' => 'completed',
    ]);

    \Livewire\Livewire::actingAs($randomUser)
        ->test(\App\Livewire\Training\TrainingGoalShow::class, ['goal' => $goal])
        ->call('verifyGoal', app(\App\Services\TrainingGamificationService::class))
        ->assertForbidden();
});

it('forbids a non-partner from calling acceptPartnerRequest', function (): void {
    $randomUser = User::factory()->create();
    $owner = User::factory()->create();

    $partner = User::factory()->create();
    $goal = makeGoal([
        'user_id' => $owner->id,
        'is_public' => true,
        'accountability_partner_id' => $partner->id,
        'partner_status' => PartnerStatus::Pending->value,
    ]);

    \Livewire\Livewire::actingAs($randomUser)
        ->test(\App\Livewire\Training\TrainingGoalShow::class, ['goal' => $goal])
        ->call('acceptPartnerRequest', app(\App\Services\TrainingGamificationService::class))
        ->assertForbidden();
});
