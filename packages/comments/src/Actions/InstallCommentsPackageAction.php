<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Core\Actions\Install\PublishPackageMigrationsAction;
use Capell\Core\Actions\Install\RunArtisanCommandAction;
use Capell\Core\Contracts\PackageLifecycleAction;
use Capell\Core\Contracts\ProgressReporter;
use Capell\Core\Data\PackageData;
use Capell\Core\Support\Install\NullProgressReporter;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

final class InstallCommentsPackageAction implements PackageLifecycleAction
{
    use AsObject;

    public function handle(PackageData $package, array $arguments = [], ?ProgressReporter $reporter = null): void
    {
        $reporter ??= new NullProgressReporter;

        RunArtisanCommandAction::run('vendor:publish', [
            '--tag' => 'capell-comments-config',
            '--force' => true,
        ], $reporter);

        PublishPackageMigrationsAction::run(new Collection([$package->name => $package]), $reporter);

        $reporter->report('Capell Comments install files published.');
    }
}
