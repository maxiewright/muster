<?php

declare(strict_types=1);

use App\Filament\Resources\BootstrapInvitations\Pages\CreateBootstrapInvitation;
use App\Filament\Widgets\PlatformOverview;
use App\Mail\BootstrapInvitationMail;
use App\Models\Organization;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

test('platform admin can view the private admin dashboard', function (): void {
    $admin = User::factory()->create([
        'is_platform_admin' => true,
    ]);

    actingAs($admin)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertOk()
        ->assertSee('Muster Admin')
        ->assertSee('Organization Invites');
});

test('platform admin dashboard widget shows top-level metrics', function (): void {
    Organization::factory()->count(2)->create();

    Livewire::actingAs(User::factory()->create([
        'is_platform_admin' => true,
    ]))
        ->test(PlatformOverview::class)
        ->assertSee('Organizations')
        ->assertSee('2');
});

test('platform admin can send a bootstrap invitation', function (): void {
    Mail::fake();

    $admin = User::factory()->create([
        'is_platform_admin' => true,
    ]);

    actingAs($admin);

    Livewire::test(CreateBootstrapInvitation::class)
        ->fillForm([
            'email' => 'commander@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(TeamInvitation::class, [
        'email' => 'commander@example.com',
        'kind' => TeamInvitation::KIND_BOOTSTRAP,
    ]);

    Mail::assertQueued(BootstrapInvitationMail::class, 1);
});

test('tenant users cannot access the private admin dashboard', function (): void {
    $user = User::factory()->lead()->create();

    actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertForbidden();
});
