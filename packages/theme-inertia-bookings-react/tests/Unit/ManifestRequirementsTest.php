<?php

declare(strict_types=1);

use Capell\ThemeStudio\InertiaBookingsReact\Health\InertiaBookingsReactHealthCheck;
use Capell\ThemeStudio\InertiaBookingsReact\Providers\InertiaBookingsReactServiceProvider;

require_once __DIR__ . '/../../src/Health/InertiaBookingsReactHealthCheck.php';
require_once __DIR__ . '/../../src/Providers/InertiaBookingsReactServiceProvider.php';

it('keeps the package manifest aligned with the react component pack', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['manifest-version'])->toBe(3)
        ->and($manifest['name'])->toBe(InertiaBookingsReactServiceProvider::$packageName)
        ->and($manifest['kind'])->toBe('plugin')
        ->and($manifest['surfaces'])->toBe(['frontend'])
        ->and($manifest['dependencies']['requires'])->toBe([
            'capell-app/theme-inertia-bookings',
            'capell-app/inertia-react-adapter',
        ])
        ->and($manifest['providers']['runtime'])->toBe([
            InertiaBookingsReactServiceProvider::class,
        ])
        ->and($manifest['healthChecks'][0]['class'])->toBe(InertiaBookingsReactHealthCheck::class)
        ->and($manifest['healthChecks'][0]['surface'])->toBe('frontend')
        ->and($manifest['marketplace']['screenshots'])->toBe([]);
});
