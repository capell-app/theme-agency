<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions;

use Capell\Core\Actions\Install\PublishPackageMigrationsAction;
use Capell\Core\Actions\Install\RunArtisanCommandAction;
use Capell\Core\Contracts\PackageLifecycleAction;
use Capell\Core\Contracts\ProgressReporter;
use Capell\Core\Data\PackageData;
use Capell\Core\Support\Install\NullProgressReporter;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallShopifyCommercePackageAction implements PackageLifecycleAction
{
    use AsObject;

    public function handle(PackageData $package, array $arguments = [], ?ProgressReporter $reporter = null): void
    {
        $reporter ??= new NullProgressReporter;

        RunArtisanCommandAction::run('vendor:publish', [
            '--tag' => 'capell-shopify-commerce-config',
            '--force' => true,
        ], $reporter);

        PublishPackageMigrationsAction::run(new Collection([$package->name => $package]), $reporter);
        InstallShopifyCommercePermissionsAction::run();

        $reporter->report('Capell Shopify Commerce install files published.');
    }
}
