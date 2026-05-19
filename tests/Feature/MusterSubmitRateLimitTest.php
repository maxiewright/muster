<?php

use App\Models\User;

test('authenticated user gets 429 after exceeding the muster submit limit', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    for ($requestCount = 0; $requestCount < 5; $requestCount++) {
        $this->get(route('muster.create'))->assertOk();
    }

    $this->get(route('muster.create'))->assertTooManyRequests();
});
