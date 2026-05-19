<?php

declare(strict_types=1);

test('migration directory only keeps canonical muster and tenant baseline migrations', function (): void {
    $migrationFiles = collect(glob(__DIR__.'/../../database/migrations/*.php'))
        ->map(fn (string $path): string => basename($path))
        ->values();

    expect($migrationFiles->contains(fn (string $file): bool => str_contains($file, 'backfill')))->toBeFalse()
        ->and($migrationFiles->contains(fn (string $file): bool => str_contains($file, 'add_tenant_columns')))->toBeFalse()
        ->and($migrationFiles->contains('2026_02_05_102641_create_musters_table.php'))->toBeTrue();
});
