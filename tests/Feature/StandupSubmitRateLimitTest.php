<?php

use App\Models\User;

test('authenticated user gets 429 after exceeding standup submit limit', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    for ($requestCount = 0; $requestCount < 5; $requestCount++) {
        $this->get(route('standup.create'))->assertOk();
    }

    $this->get(route('standup.create'))->assertTooManyRequests();
});
