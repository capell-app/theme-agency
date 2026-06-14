<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

it('publishes package migrations through the canonical Capell publisher instead of vendor publish tags', function (): void {
    $actions = (new Finder)
        ->in(__DIR__ . '/../../../packages')
        ->path('src/Actions')
        ->name('*Install*PackageAction.php');

    $offenders = [];

    foreach ($actions as $action) {
        $contents = $action->getContents();

        if (! str_contains($contents, "RunArtisanCommandAction::run('vendor:publish'")) {
            continue;
        }

        if (! collect(explode("\n", $contents))->contains(
            fn (string $line): bool => str_contains($line, "'--tag'")
                && str_contains($line, '-migrations'),
        )) {
            continue;
        }

        $offenders[] = $action->getRelativePathname();
    }

    sort($offenders);

    expect($offenders)->toBe(
        [],
        'Package migrations must be published with PublishPackageMigrationsAction/capell:publish-migrations so filenames stay canonical:' .
        "\n" . json_encode($offenders, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

it('does not register Laravel vendor publish migration tags from package source', function (): void {
    $sourceFiles = (new Finder)
        ->in(__DIR__ . '/../../../packages')
        ->path('/src/')
        ->name('*.php');

    $offenders = [];

    foreach ($sourceFiles as $sourceFile) {
        foreach (explode("\n", $sourceFile->getContents()) as $lineNumber => $line) {
            if (preg_match('/[\'"][^\'"]+-migrations[\'"]/', $line) !== 1) {
                continue;
            }

            if (str_contains($line, 'required-migrations')) {
                continue;
            }

            $offenders[] = sprintf('%s:%d', $sourceFile->getRelativePathname(), $lineNumber + 1);
        }
    }

    sort($offenders);

    expect($offenders)->toBe(
        [],
        'Package source must not expose Laravel vendor publish tags for migrations. Use PublishPackageMigrationsAction/capell:publish-migrations so published filenames stay canonical:' .
        "\n" . json_encode($offenders, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});
