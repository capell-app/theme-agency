<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Providers;

use Capell\AccessGate\Events\RegistrationApproved;
use Capell\AutomationStudio\Actions\RegisterAutomationStudioDefaultsAction;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromAccessApproval;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromCampaignConversion;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromFormSubmission;
use Capell\AutomationStudio\Listeners\DispatchAutomationFromWorkspaceStateChanged;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Capell\AutomationStudio\Support\AutomationTriggerRegistry;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\PublishingStudio\Events\WorkspaceStateChanged;
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
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
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
        $this->listenIfClassExists(RegistrationApproved::class, DispatchAutomationFromAccessApproval::class);
        $this->listenIfClassExists(WorkspaceStateChanged::class, DispatchAutomationFromWorkspaceStateChanged::class);
        $this->listenIfClassExists('Capell\\CampaignStudio\\Events\\CampaignConverted', DispatchAutomationFromCampaignConversion::class);
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
