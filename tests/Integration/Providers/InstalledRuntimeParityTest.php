<?php

declare(strict_types=1);

use Capell\Core\Actions\EnablePackageAction;
use Capell\Core\Actions\RuntimeRefresh\RefreshInstalledPackageRuntimeAction;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Support\InstalledRuntimeParity;
use Capell\Tests\Support\InstalledRuntimeState;
use Capell\Tests\Support\InstalledRuntimeTestCase;

it('matches fresh installed boot after in-process installation and repeated refresh', function (): void {
    InstalledRuntimeParity::assertPackage('theme-agency');
});

it('activates a preloaded theme when enabling without legacy callback replay', function (): void {
    InstalledRuntimeState::run(static function (): void {
        $app = InstalledRuntimeTestCase::boot(InstalledRuntimeParity::manifest('theme-agency'), false, sourceRoot: dirname(__DIR__, 3));
        $registry = $app->make(ThemeRegistry::class);
        expect($registry->has('agency'))->toBeFalse();

        $package = CapellCore::getPackage('capell-app/theme-agency');
        EnablePackageAction::run($package);
        $definition = $registry->definition('agency');
        RefreshInstalledPackageRuntimeAction::run($package, replayBootedCallbacks: false);
        RefreshInstalledPackageRuntimeAction::run($package, replayBootedCallbacks: false);

        expect($registry->definition('agency'))->toBe($definition);
    });
});
