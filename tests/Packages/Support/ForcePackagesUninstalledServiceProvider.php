<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Support;

use Capell\Address\Providers\AddressServiceProvider;
use Capell\AgentBridge\Providers\AgentBridgeServiceProvider;
use Capell\AIOrchestrator\Providers\AIOrchestratorServiceProvider;
use Capell\Blog\Providers\BlogServiceProvider;
use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Diagnostics\Providers\DiagnosticsServiceProvider;
use Capell\FormBuilder\Providers\FormBuilderServiceProvider;
use Capell\FrontendAuthoring\Providers\FrontendAuthoringServiceProvider;
use Capell\Insights\Providers\InsightsServiceProvider;
use Capell\LoginAudit\Providers\LoginAuditServiceProvider;
use Capell\MediaLibrary\MediaLibraryServiceProvider;
use Capell\MigrationAssistant\Providers\MigrationAssistantServiceProvider;
use Capell\Navigation\Providers\NavigationServiceProvider;
use Capell\PublishingStudio\Providers\PublishingStudioServiceProvider;
use Capell\Search\Providers\SearchServiceProvider;
use Capell\SeoSuite\Providers\SeoSuiteServiceProvider;
use Capell\Tags\Providers\TagsServiceProvider;
use Illuminate\Support\ServiceProvider;
use Override;

class ForcePackagesUninstalledServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        foreach ($this->packageNames() as $packageName) {
            CapellCore::forcePackageInstalled($packageName, false);
        }
    }

    /** @return list<string> */
    private function packageNames(): array
    {
        return [
            AddressServiceProvider::$packageName,
            InsightsServiceProvider::$packageName,
            AIOrchestratorServiceProvider::$packageName,
            LoginAuditServiceProvider::$packageName,
            MigrationAssistantServiceProvider::$packageName,
            BlogServiceProvider::$packageName,
            CampaignStudioServiceProvider::$packageName,
            DiagnosticsServiceProvider::$packageName,
            FormBuilderServiceProvider::$packageName,
            AgentBridgeServiceProvider::$packageName,
            MediaLibraryServiceProvider::$packageName,
            NavigationServiceProvider::$packageName,
            SeoSuiteServiceProvider::$packageName,
            SearchServiceProvider::$packageName,
            TagsServiceProvider::$packageName,
            FrontendAuthoringServiceProvider::$packageName,
            PublishingStudioServiceProvider::$packageName,
            'capell-app/theme-open-studio',
            'capell-app/theme-ink-press',
            'capell-app/theme-night-shift',
        ];
    }
}
