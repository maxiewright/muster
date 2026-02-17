<?php

declare(strict_types=1);

use App\Events\BadgeEarned;
use App\Events\PointsEarned;
use App\Events\StandupCreated;
use App\Events\TrainingCheckinLogged;
use App\Models\Badge;
use App\Models\TrainingCheckin;
use App\Models\TrainingGoal;
use App\Models\User;
use App\Services\TrainingGamificationService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Event;

test('StandupCreated implements ShouldBroadcast and uses muster channel', function (): void {
    $user = User::factory()->create();
    $standup = \App\Models\Standup::factory()->create(['user_id' => $user->id]);

    $event = new StandupCreated($standup);

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
    expect($event->broadcastOn())->toHaveCount(1);
    expect($event->broadcastOn()[0])->toBeInstanceOf(Channel::class);
    expect($event->broadcastOn()[0]->name)->toBe('muster');
});

test('PointsEarned implements ShouldBroadcast and uses muster channel', function (): void {
    $user = User::factory()->create();
    $event = new PointsEarned($user, 10);

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
    expect($event->broadcastOn())->toHaveCount(1);
    expect($event->broadcastOn()[0])->toBeInstanceOf(Channel::class);
    expect($event->broadcastOn()[0]->name)->toBe('muster');
});

test('BadgeEarned implements ShouldBroadcast and uses muster channel', function (): void {
    $user = User::factory()->create();
    $badge = Badge::query()->create([
        'slug' => 'broadcast-badge',
        'name' => 'Broadcast Badge',
        'description' => 'Verifies broadcasts',
        'icon' => '🏅',
        'color' => '#22c55e',
        'points_reward' => 5,
    ]);

    $event = new BadgeEarned($user, $badge);

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
    expect($event->broadcastOn())->toHaveCount(1);
    expect($event->broadcastOn()[0])->toBeInstanceOf(Channel::class);
    expect($event->broadcastOn()[0]->name)->toBe('muster');
});

test('TrainingCheckinLogged broadcasts on recipient private channel', function (): void {
    $owner = User::factory()->create();
    $partner = User::factory()->create();

    $goal = TrainingGoal::query()->create([
        'slug' => 'checkin-broadcast-goal',
        'user_id' => $owner->id,
        'accountability_partner_id' => $partner->id,
        'title' => 'Checkin Broadcast Goal',
        'start_date' => now()->toDateString(),
        'target_date' => now()->addDays(30)->toDateString(),
        'status' => 'active',
        'partner_status' => 'accepted',
    ]);

    $checkin = TrainingCheckin::query()->create([
        'training_goal_id' => $goal->id,
        'user_id' => $owner->id,
        'progress_update' => 'Made progress on drills.',
        'minutes_logged' => 45,
    ]);

    $event = new TrainingCheckinLogged($checkin, $partner);
    $channels = $event->broadcastOn();

    expect($event)->toBeInstanceOf(ShouldBroadcast::class);
    expect($channels)->toHaveCount(1);
    expect($channels[0])->toBeInstanceOf(PrivateChannel::class);
    expect($channels[0]->name)->toBe("private-App.Models.User.{$partner->id}");
});

test('training checkin logging dispatches realtime event for partner', function (): void {
    Event::fake([TrainingCheckinLogged::class]);

    $owner = User::factory()->create();
    $partner = User::factory()->create();

    $goal = TrainingGoal::query()->create([
        'slug' => 'checkin-dispatch-goal',
        'user_id' => $owner->id,
        'accountability_partner_id' => $partner->id,
        'title' => 'Checkin Dispatch Goal',
        'start_date' => now()->toDateString(),
        'target_date' => now()->addDays(30)->toDateString(),
        'status' => 'active',
        'partner_status' => 'accepted',
    ]);

    $checkin = TrainingCheckin::query()->create([
        'training_goal_id' => $goal->id,
        'user_id' => $owner->id,
        'progress_update' => 'Completed scenario practice.',
        'minutes_logged' => 60,
    ]);

    app(TrainingGamificationService::class)->onCheckinLogged($checkin->fresh(['goal', 'user']));

    Event::assertDispatched(TrainingCheckinLogged::class, function (TrainingCheckinLogged $event) use ($partner): bool {
        $channels = $event->broadcastOn();

        return count($channels) === 1 && $channels[0]->name === "private-App.Models.User.{$partner->id}";
    });
});
