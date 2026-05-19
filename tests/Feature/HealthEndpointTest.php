<?php

declare(strict_types=1);

it('rate limits the health endpoint', function (): void {
    foreach (range(1, 10) as $requestNumber) {
        $this->get(route('health'))->assertSuccessful();
    }

    $this->get(route('health'))->assertStatus(429);
});
