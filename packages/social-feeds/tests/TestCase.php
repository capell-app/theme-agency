<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Tests;

use Capell\BlockLibrary\Providers\BlockLibraryServiceProvider;
use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Providers\CapellServiceProvider;
use Capell\SocialFeeds\Providers\SocialFeedsServiceProvider;
use Capell\Tests\AbstractTestCase;
use Capell\Tests\Support\RegisterLocalPackageManifestsServiceProvider;
use Livewire\LivewireServiceProvider;
use Override;

abstract class TestCase extends AbstractTestCase
{
    protected function getPackageServiceName(): string
    {
        return 'capell-social-feeds';
    }

    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        $providers = array_filter(
            parent::getPackageProviders($app),
            static fn (string $provider): bool => $provider !== RegisterLocalPackageManifestsServiceProvider::class,
        );

        return [
            ...array_values($providers),
            CapellServiceProvider::class,
            LivewireServiceProvider::class,
            BlockLibraryServiceProvider::class,
            SocialFeedsServiceProvider::class,
        ];
    }

    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        CapellCore::registerPackage(
            BlockLibraryServiceProvider::$packageName,
            type: PackageTypeEnum::Plugin,
            serviceProviderClass: BlockLibraryServiceProvider::class,
            path: dirname(__DIR__, 2) . '/block-library',
        );
        CapellCore::registerPackage(
            SocialFeedsServiceProvider::$packageName,
            type: PackageTypeEnum::Plugin,
            serviceProviderClass: SocialFeedsServiceProvider::class,
            path: dirname(__DIR__),
        );

        CapellCore::forcePackageInstalled(BlockLibraryServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(SocialFeedsServiceProvider::$packageName);
    }
}
