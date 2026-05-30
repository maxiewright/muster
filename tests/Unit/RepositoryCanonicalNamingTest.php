<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

test('repository uses canonical muster naming', function (): void {
    $repositoryRoot = dirname(__DIR__, 2);
    $patterns = ['*.php', '*.md', '*.json', '*.yml', '*.yaml', '*.toml', '*.html', '*.svg', '*.txt', '*.webmanifest', '*.js', '*.ts', '*.css', '*.xml', '*.lock'];
    $legacyPattern = '/\bstandup\b|Standup|standups\b|standup_/';

    $files = Finder::create()
        ->files()
        ->in($repositoryRoot)
        ->exclude([
            '.git',
            '.claude',
            '.gemini',
            'vendor',
            'node_modules',
            'storage',
            'bootstrap/cache',
        ])
        ->name($patterns)
        ->notName('RepositoryCanonicalNamingTest.php');

    $legacyMatches = collect(iterator_to_array($files))
        ->map(function (SplFileInfo $file) use ($legacyPattern): ?string {
            $contents = $file->getContents();

            if (preg_match($legacyPattern, $contents) !== 1) {
                return null;
            }

            return $file->getRelativePathname();
        })
        ->filter()
        ->values()
        ->all();

    expect($legacyMatches)->toBeEmpty();
});
