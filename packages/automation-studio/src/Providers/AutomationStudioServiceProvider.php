<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Providers;

use Capell\AutomationStudio\Actions\RegisterAutomationStudioDefaultsAction;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromAccessApproval;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromCampaignConversion;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromFormSubmission;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromWorkspaceStateChanged;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Capell\AutomationStudio\Support\AutomationTriggerRegistry;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Illuminate\Support\Facades\Event;
use Override;
use Spatie\LaravelPackageTools\Package;

final class AutomationStudioServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-automation-studio';

    public static string $packageName = 'capell-app/automation-studio';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasTranslations()
            ->hasMigrations([
                '2026_05_31_170000_01_create_automation_rules_table',
                '2026_05_31_170000_02_create_automation_runs_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->register(AdminServiceProvider::class);

        $this->app->singleton(AutomationActionRegistry::class);
        $this->app->singleton(AutomationRuleRegistry::class);
        $this->app->singleton(AutomationTriggerRegistry::class);
    }

    public function packageBooted(): void
    {
        RegisterAutomationStudioDefaultsAction::run();

        if (! $this->isPackageInstalled()) {
            return;
        }

        $this
            ->registerModels()
            ->registerProtectedTables();

        $this->registerPackageEventListeners();
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerPackageEventListeners(): void
    {
        $this->listenIfClassExists(implode('\\', ['Capell', 'FormBuilder', 'Events', 'FormSubmitted']), DispatchAutomationFromFormSubmission::class);
        $this->listenIfClassExists(implode('\\', ['Capell', 'AccessGate', 'Events', 'RegistrationApproved']), DispatchAutomationFromAccessApproval::class);
        $this->listenIfClassExists(implode('\\', ['Capell', 'PublishingStudio', 'Events', 'WorkspaceStateChanged']), DispatchAutomationFromWorkspaceStateChanged::class);
        $this->listenIfClassExists(implode('\\', ['Capell', 'CampaignStudio', 'Events', 'CampaignConverted']), DispatchAutomationFromCampaignConversion::class);
    }

    private function registerModels(): self
    {
        $this->surface()->models([
            AutomationRule::class,
            AutomationRun::class,
        ]);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable('automation_rules');
        CapellCore::registerProtectedTable('automation_runs');

        return $this;
    }

    /**
     * @param  class-string  $listener
     */
    private function listenIfClassExists(string $eventClass, string $listener): void
    {
        if (! class_exists($eventClass)) {
            return;
        }

        Event::listen($eventClass, $listener);
    }
}
