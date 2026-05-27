<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Actions\Install\PublishPackageMigrationsAction;
use Capell\Core\Actions\Install\RunMigrationsAction;
use Capell\Core\Contracts\PackageLifecycleAction;
use Capell\Core\Contracts\ProgressReporter;
use Capell\Core\Data\PackageData;
use Capell\Core\Support\Install\NullProgressReporter;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallSeoSuitePackageAction implements PackageLifecycleAction
{
    use AsObject;

    public function handle(PackageData $package, array $arguments = [], ?ProgressReporter $reporter = null): void
    {
        $reporter ??= new NullProgressReporter;

        PublishPackageMigrationsAction::run(new Collection([$package->name => $package]), $reporter);
        RunMigrationsAction::run($reporter);
        SeedDefaultAiCrawlerRulesAction::run();

        $reporter->report('Capell SEO Suite installed successfully.');
    }
}
