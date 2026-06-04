<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Knowledge\Health\ThemeKnowledgeHealthCheck;
use Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider;

uses(PackagesTestCase::class);

it('passes knowledge diagnostics when the theme is installed and booted', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(KnowledgeThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $this->app->instance(ThemeRegistry::class, $registry);

    (new KnowledgeThemeServiceProvider($this->app))->boot($registry);

    $diagnostics = ThemeKnowledgeHealthCheck::runDiagnostics();

    expect($diagnostics)->toHaveCount(3)
        ->and($diagnostics->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue()
        ->and($diagnostics->pluck('label')->all())->toBe([
            'Theme Knowledge Studio definition',
            'Theme Knowledge render views',
            'Theme Knowledge vendor assets',
        ])
        ->and($diagnostics->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue()
        ->and(ThemeKnowledgeHealthCheck::passed())->toBeTrue();
});

it('fails knowledge diagnostics when the theme definition is not registered', function (): void {
    CapellCore::clearPackages();
    $this->app->instance(ThemeRegistry::class, new ThemeRegistry);

    $definitionCheck = ThemeKnowledgeHealthCheck::runDiagnostics()
        ->first(static fn (DoctorCheckResultData $result): bool => $result->label === 'Theme Knowledge Studio definition');

    throw_unless($definitionCheck instanceof DoctorCheckResultData, RuntimeException::class, 'Knowledge theme definition diagnostic was not returned.');

    expect($definitionCheck->passed)->toBeFalse()
        ->and(ThemeKnowledgeHealthCheck::passed())->toBeFalse();
});
