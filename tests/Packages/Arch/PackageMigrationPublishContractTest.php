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
