<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('loads the standup page and shows the step heading', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('standup.create'));

    $response->assertOk();
    $response->assertSeeText('What did you work on yesterday?');
});
