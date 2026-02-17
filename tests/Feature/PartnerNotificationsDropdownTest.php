<?php

declare(strict_types=1);

use App\Livewire\Training\PartnerNotificationsDropdown;
use App\Models\User;
use Livewire\Livewire;

test('shows toast when training checkin notification is received', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PartnerNotificationsDropdown::class)
        ->call('onTrainingCheckinLogged', [
            'from_user_name' => 'Taylor',
            'goal_title' => 'Leadership Sprint',
            'message' => 'Completed two drills.',
        ])
        ->assertDispatched('toast-show');
});

test('shows toast when task assignment notification is received', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PartnerNotificationsDropdown::class)
        ->call('onTaskAssigned', [
            'task' => [
                'title' => 'Prepare standup summary',
                'creator_name' => 'Jordan',
            ],
        ])
        ->assertDispatched('toast-show');
});

test('shows toast when assigned task status changes', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(PartnerNotificationsDropdown::class)
        ->call('onTaskStatusChanged', [
            'to_status' => 'completed',
            'changed_by' => 'Jordan',
            'task' => [
                'title' => 'Prepare standup summary',
            ],
        ])
        ->assertDispatched('toast-show');
});
