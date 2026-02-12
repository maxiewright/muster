<?php

test('registration screen is unavailable in invite-only mode', function (): void {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('registration endpoint is unavailable in invite-only mode', function (): void {
    $response = $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
    $this->assertGuest();
});
