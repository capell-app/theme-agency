<?php

declare(strict_types=1);

namespace Capell\Tests\Packages;

use Capell\Address\Providers\AddressServiceProvider;
use Capell\Admin\Providers\AdminServiceProvider;
use Capell\Admin\Providers\Filament\AdminPanelProvider;
use Capell\AgentBridge\Providers\AgentBridgeServiceProvider;
use Capell\AIOrchestrator\Providers\AIOrchestratorServiceProvider;
use Capell\Blog\Providers\BlogServiceProvider;
use Capell\Blog\Providers\FrontendServiceProvider as BlogFrontendServiceProvider;
use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Capell\Core\Providers\CapellServiceProvider;
use Capell\Diagnostics\Providers\DiagnosticsServiceProvider;
use Capell\FormBuilder\Providers\FormBuilderServiceProvider as CapellFormBuilderServiceProvider;
use Capell\FoundationTheme\Providers\FoundationThemeServiceProvider;
use Capell\Frontend\Providers\FrontendServiceProvider;
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
use Capell\Tests\AbstractTestCase;
use Capell\Tests\Packages\Support\ForcePackagesUninstalledServiceProvider;
use Capell\ThemeStudio\CaseStudyPlatform\CaseStudyPlatformThemeServiceProvider;
use Capell\ThemeStudio\DarkProductSystem\DarkProductSystemThemeServiceProvider;
use Capell\ThemeStudio\DenseNewsAnalysis\DenseNewsAnalysisThemeServiceProvider;
use Illuminate\Foundation\Application;
use Livewire\LivewireServiceProvider;
use Override;

class UninstalledPackagesTestCase extends AbstractTestCase
{
    protected function getPackageServiceName(): string
    {
        return 'capell-uninstalled-packages';
    }

    /**
     * @param  Application  $app
     * @return class-string[]
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ...parent::getPackageProviders($app),
            ForcePackagesUninstalledServiceProvider::class,
            AddressServiceProvider::class,
            InsightsServiceProvider::class,
            AIOrchestratorServiceProvider::class,
            LoginAuditServiceProvider::class,
            MigrationAssistantServiceProvider::class,
            NavigationServiceProvider::class,
            BlogServiceProvider::class,
            BlogFrontendServiceProvider::class,
            CampaignStudioServiceProvider::class,
            CapellFormBuilderServiceProvider::class,
            DiagnosticsServiceProvider::class,
            SeoSuiteServiceProvider::class,
            SearchServiceProvider::class,
            TagsServiceProvider::class,
            FrontendAuthoringServiceProvider::class,
            PublishingStudioServiceProvider::class,
            MediaLibraryServiceProvider::class,
            AgentBridgeServiceProvider::class,
            CaseStudyPlatformThemeServiceProvider::class,
            DarkProductSystemThemeServiceProvider::class,
            DenseNewsAnalysisThemeServiceProvider::class,
            FrontendServiceProvider::class,
            CapellServiceProvider::class,
            AdminPanelProvider::class,
            AdminServiceProvider::class,
            FoundationThemeServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }
}
