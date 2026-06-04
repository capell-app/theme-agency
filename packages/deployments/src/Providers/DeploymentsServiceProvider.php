<?php

declare(strict_types=1);

namespace Capell\Deployments\Providers;

use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Deployments\Actions\PublishComposerRequirementAction;
use Capell\Deployments\Contracts\PublishesComposerChanges;
use Capell\Deployments\Data\ComposerRequirementData;
use Capell\Deployments\Data\PublishComposerChangeResultData;
use Capell\Deployments\Filament\Pages\DeploymentConnectionPage;
use Capell\Deployments\Filament\Widgets\DeploymentConnectionWidget;
use Capell\Deployments\Models\DeploymentConnection;
use LogicException;
use Spatie\LaravelPackageTools\Package;

class DeploymentsServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-deployments';

    public static string $packageName = 'capell-app/deployments';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasRoute('oauth')
            ->hasViews(self::$name)
            ->hasTranslations();
    }

    public function registeringPackage(): void
    {
        $this->app->bind(PublishesComposerChanges::class, fn (): object => new class implements PublishesComposerChanges
        {
            public function publish(ComposerRequirementData $requirement): PublishComposerChangeResultData
            {
                $connections = DeploymentConnection::query()
                    ->where('is_active', true)
                    ->limit(2)
                    ->get();

                if ($connections->count() > 1) {
                    throw new LogicException('Deployments cannot choose a Composer publishing repository because multiple active deployment connections exist. Publish with PublishComposerRequirementAction and an explicit DeploymentConnection.');
                }

                $connection = $connections->firstOrFail();

                return PublishComposerRequirementAction::run($requirement, $connection);
            }
        });

        if (config('capell-deployments.enabled', true) === true) {
            CapellAdmin::registerExtensionPage(static::$packageName, DeploymentConnectionPage::class);
            CapellAdmin::registerDashboardWidget(DeploymentConnectionWidget::class, DashboardEnum::SystemHealth);
        }
    }
}
