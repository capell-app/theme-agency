<?php

declare(strict_types=1);

use Capell\AccessGate\Tests\TestCase as AccessGateTestCase;
use Capell\Address\Tests\AddressTestCase;
use Capell\AgentBridge\Tests\TestCase as AgentBridgeTestCase;
use Capell\AIOrchestrator\Tests\AIOrchestratorTestCase;
use Capell\Api\Tests\ApiTestCase;
use Capell\AutomationStudio\Tests\AutomationStudioTestCase;
use Capell\BlockLibrary\Tests\BlockLibraryTestCase;
use Capell\Blog\Tests\BlogTestCase;
use Capell\CampaignStudio\Tests\CampaignStudioTestCase;
use Capell\Comments\Tests\CommentsTestCase;
use Capell\ContentSections\Tests\ContentSectionsTestCase;
use Capell\DemoKit\Tests\DemoKitTestCase;
use Capell\Deployments\Tests\TestCase as DeploymentsTestCase;
use Capell\Diagnostics\Tests\DiagnosticsTestCase;
use Capell\DocumentLifecycle\Tests\DocumentLifecycleTestCase;
use Capell\EmailStudio\Tests\EmailStudioTestCase;
use Capell\EquestrianClinics\Tests\EquestrianClinicsTestCase;
use Capell\Events\Tests\EventsTestCase;
use Capell\ExceptionReports\Tests\ExceptionReportsTestCase;
use Capell\FilamentPeek\Tests\FilamentPeekTestCase;
use Capell\FormBuilder\Tests\FormBuilderTestCase;
use Capell\FrontendAuthoring\Tests\FrontendAuthoringTestCase;
use Capell\FrontendOptimizer\Tests\FrontendOptimizerTestCase;
use Capell\Insights\Tests\InsightsTestCase;
use Capell\LayoutBuilder\Tests\LayoutBuilderTestCase;
use Capell\MediaAI\Tests\MediaAITestCase;
use Capell\MediaLibrary\Tests\MediaLibraryTestCase;
use Capell\MigrationAssistant\Tests\MigrationAssistantTestCase;
use Capell\Navigation\Tests\NavigationTestCase;
use Capell\Newsletter\Tests\NewsletterTestCase;
use Capell\Notes\Tests\NotesTestCase;
use Capell\PasswordPolicy\Tests\PasswordPolicyTestCase;
use Capell\PublicActions\Tests\PublicActionsTestCase;
use Capell\PublishingStudio\Tests\PublishingStudioTestCase;
use Capell\RecordSwitcher\Tests\RecordSwitcherTestCase;
use Capell\Search\Tests\SearchTestCase;
use Capell\SeoSuite\Tests\SeoSuiteTestCase;
use Capell\ShopifyCommerce\Tests\TestCase as ShopifyCommerceTestCase;
use Capell\SiteMonitor\Tests\SiteMonitorTestCase;
use Capell\Tags\Tests\TagsTestCase;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\Tests\Packages\UninstalledPackagesTestCase;
use Capell\UrlManager\Tests\UrlManagerTestCase;
use Capell\WelcomeTour\Tests\WelcomeTourTestCase;
use Capell\WordPressImporter\Tests\WordPressImporterTestCase;
use Illuminate\Testing\PendingCommand;

/**
 * @param  class-string  $testCase
 */
function extendCapellPackageTests(string $testCase, string $group, string $package): void
{
    pest()
        ->extend($testCase)
        ->group($group, 'package')
        ->in(sprintf('../packages/%s/tests', $package), sprintf('../Packages/%s/tests', $package));
}

function groupCapellPackageTests(string ...$groups): void
{
    pest()->group(...$groups)->in('../packages/*/tests');
}

function groupCapellPackageDirectoriesByName(): void
{
    $packageTestPaths = glob(__DIR__ . '/../packages/*/tests') ?: [];

    foreach ($packageTestPaths as $packageTestPath) {
        $packageName = basename(dirname($packageTestPath));

        pest()->group($packageName, 'package')->in($packageTestPath);
    }
}

function groupCapellPackageSuiteTests(string $suite): void
{
    $group = strtolower($suite);

    pest()->group($group, 'package')->in(sprintf('../packages/*/tests/%s', $suite));
    pest()->group($group, 'workspace')->in(sprintf('Packages/%s', $suite));
}

/**
 * @param  array<string, mixed>  $parameters
 */
function capell_artisan(string $command, array $parameters = []): PendingCommand
{
    $pendingCommand = test()->artisan($command, $parameters);

    throw_unless($pendingCommand instanceof PendingCommand, RuntimeException::class, 'Capell package command tests expect console output mocking to remain enabled.');

    return $pendingCommand;
}

/**
 * @return array<array-key, mixed>
 */
function capell_json_array(string $json): array
{
    $decoded = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);

    throw_unless(is_array($decoded), RuntimeException::class, 'JSON payload must decode to an array.');

    return $decoded;
}

/**
 * @return array<array-key, mixed>
 */
function capell_json_file_array(string $path): array
{
    $contents = file_get_contents($path);

    throw_unless(is_string($contents), RuntimeException::class, sprintf('Unable to read JSON file [%s].', $path));

    return capell_json_array($contents);
}

groupCapellPackageTests('package');
groupCapellPackageDirectoriesByName();
groupCapellPackageSuiteTests('Arch');
groupCapellPackageSuiteTests('Feature');
groupCapellPackageSuiteTests('Integration');
groupCapellPackageSuiteTests('Unit');
pest()->group('feature', 'workspace')->in('Feature');
pest()->group('uninstalled-packages', 'workspace', 'integration')->in('UninstalledPackages');

extendCapellPackageTests(AddressTestCase::class, 'address', 'address');
extendCapellPackageTests(AccessGateTestCase::class, 'access-gate', 'access-gate');
extendCapellPackageTests(AgentBridgeTestCase::class, 'agent-bridge', 'agent-bridge');
extendCapellPackageTests(AIOrchestratorTestCase::class, 'ai-orchestrator', 'ai-orchestrator');
extendCapellPackageTests(ApiTestCase::class, 'api', 'api');
extendCapellPackageTests(AutomationStudioTestCase::class, 'automation-studio', 'automation-studio');
extendCapellPackageTests(BlogTestCase::class, 'blog', 'blog');
extendCapellPackageTests(BlockLibraryTestCase::class, 'block-library', 'block-library');
extendCapellPackageTests(CampaignStudioTestCase::class, 'campaign-studio', 'campaign-studio');
extendCapellPackageTests(CommentsTestCase::class, 'comments', 'comments');
extendCapellPackageTests(ContentSectionsTestCase::class, 'content-sections', 'content-sections');
extendCapellPackageTests(DemoKitTestCase::class, 'demo-kit', 'demo-kit');
extendCapellPackageTests(DeploymentsTestCase::class, 'deployments', 'deployments');
extendCapellPackageTests(DiagnosticsTestCase::class, 'diagnostics', 'diagnostics');
extendCapellPackageTests(ExceptionReportsTestCase::class, 'exception-reports', 'exception-reports');
extendCapellPackageTests(DocumentLifecycleTestCase::class, 'document-lifecycle', 'document-lifecycle');
extendCapellPackageTests(EmailStudioTestCase::class, 'email-studio', 'email-studio');
extendCapellPackageTests(EquestrianClinicsTestCase::class, 'equestrian-clinics', 'equestrian-clinics');
extendCapellPackageTests(EventsTestCase::class, 'events', 'events');
extendCapellPackageTests(FilamentPeekTestCase::class, 'filament-peek', 'filament-peek');
extendCapellPackageTests(FormBuilderTestCase::class, 'form-builder', 'form-builder');
extendCapellPackageTests(FrontendAuthoringTestCase::class, 'frontend-authoring', 'frontend-authoring');
extendCapellPackageTests(FrontendOptimizerTestCase::class, 'frontend-optimizer', 'frontend-optimizer');
extendCapellPackageTests(PackagesTestCase::class, 'hero', 'hero');
extendCapellPackageTests(InsightsTestCase::class, 'insights', 'insights');
extendCapellPackageTests(LayoutBuilderTestCase::class, 'layout-builder', 'layout-builder');
extendCapellPackageTests(PackagesTestCase::class, 'login-audit', 'login-audit');
extendCapellPackageTests(MediaAITestCase::class, 'media-ai', 'media-ai');
extendCapellPackageTests(MediaLibraryTestCase::class, 'media-library', 'media-library');
extendCapellPackageTests(MigrationAssistantTestCase::class, 'migration-assistant', 'migration-assistant');
extendCapellPackageTests(NavigationTestCase::class, 'navigation', 'navigation');
extendCapellPackageTests(NewsletterTestCase::class, 'newsletter', 'newsletter');
extendCapellPackageTests(NotesTestCase::class, 'notes', 'notes');
pest()->extend(PackagesTestCase::class)->in('Packages');
extendCapellPackageTests(PackagesTestCase::class, 'foundation-theme', 'foundation-theme');
extendCapellPackageTests(PasswordPolicyTestCase::class, 'password-policy', 'password-policy');
extendCapellPackageTests(PublishingStudioTestCase::class, 'publishing-studio', 'publishing-studio');
extendCapellPackageTests(PublicActionsTestCase::class, 'public-actions', 'public-actions');
extendCapellPackageTests(RecordSwitcherTestCase::class, 'record-switcher', 'record-switcher');
extendCapellPackageTests(SearchTestCase::class, 'search', 'search');
extendCapellPackageTests(SeoSuiteTestCase::class, 'seo-suite', 'seo-suite');
extendCapellPackageTests(ShopifyCommerceTestCase::class, 'shopify-commerce', 'shopify-commerce');
extendCapellPackageTests(SiteMonitorTestCase::class, 'site-monitor', 'site-monitor');
extendCapellPackageTests(TagsTestCase::class, 'tags', 'tags');
extendCapellPackageTests(UrlManagerTestCase::class, 'url-manager', 'url-manager');
extendCapellPackageTests(PackagesTestCase::class, 'theme-agency', 'theme-agency');
extendCapellPackageTests(PackagesTestCase::class, 'theme-commerce', 'theme-commerce');
extendCapellPackageTests(PackagesTestCase::class, 'theme-corporate', 'theme-corporate');
extendCapellPackageTests(PackagesTestCase::class, 'theme-estate-agents', 'theme-estate-agents');
extendCapellPackageTests(PackagesTestCase::class, 'theme-healthcare', 'theme-healthcare');
extendCapellPackageTests(PackagesTestCase::class, 'theme-liquid-glass', 'theme-liquid-glass');
extendCapellPackageTests(PackagesTestCase::class, 'theme-restaurant', 'theme-restaurant');
extendCapellPackageTests(PackagesTestCase::class, 'theme-saas', 'theme-saas');
extendCapellPackageTests(PackagesTestCase::class, 'theme-ai-lab', 'theme-ai-lab');
extendCapellPackageTests(PackagesTestCase::class, 'theme-api-platform', 'theme-api-platform');
extendCapellPackageTests(PackagesTestCase::class, 'theme-ai-agent', 'theme-ai-agent');
extendCapellPackageTests(PackagesTestCase::class, 'theme-aeo-analytics', 'theme-aeo-analytics');
extendCapellPackageTests(PackagesTestCase::class, 'theme-fintech-trust', 'theme-fintech-trust');
extendCapellPackageTests(PackagesTestCase::class, 'theme-crypto-defi', 'theme-crypto-defi');
extendCapellPackageTests(PackagesTestCase::class, 'theme-quant-trading', 'theme-quant-trading');
extendCapellPackageTests(PackagesTestCase::class, 'theme-devtool-oss', 'theme-devtool-oss');
extendCapellPackageTests(PackagesTestCase::class, 'theme-robotics-hardware', 'theme-robotics-hardware');
extendCapellPackageTests(PackagesTestCase::class, 'theme-manufacturing', 'theme-manufacturing');
extendCapellPackageTests(PackagesTestCase::class, 'theme-packaging-supplier', 'theme-packaging-supplier');
extendCapellPackageTests(PackagesTestCase::class, 'theme-conference-event', 'theme-conference-event');
extendCapellPackageTests(PackagesTestCase::class, 'theme-podcast-show', 'theme-podcast-show');
extendCapellPackageTests(PackagesTestCase::class, 'theme-newsroom-magazine', 'theme-newsroom-magazine');
extendCapellPackageTests(PackagesTestCase::class, 'theme-design-studio', 'theme-design-studio');
extendCapellPackageTests(PackagesTestCase::class, 'theme-product-studio', 'theme-product-studio');
extendCapellPackageTests(PackagesTestCase::class, 'theme-personal-dev', 'theme-personal-dev');
extendCapellPackageTests(PackagesTestCase::class, 'theme-creator-newsletter', 'theme-creator-newsletter');
extendCapellPackageTests(PackagesTestCase::class, 'theme-law-firm', 'theme-law-firm');
extendCapellPackageTests(PackagesTestCase::class, 'theme-financial-advisory', 'theme-financial-advisory');
extendCapellPackageTests(PackagesTestCase::class, 'theme-construction-trades', 'theme-construction-trades');
extendCapellPackageTests(PackagesTestCase::class, 'theme-fitness-wellness', 'theme-fitness-wellness');
extendCapellPackageTests(PackagesTestCase::class, 'theme-beauty-spa', 'theme-beauty-spa');
extendCapellPackageTests(PackagesTestCase::class, 'theme-travel-tourism', 'theme-travel-tourism');
extendCapellPackageTests(PackagesTestCase::class, 'theme-outdoor-mission', 'theme-outdoor-mission');
extendCapellPackageTests(PackagesTestCase::class, 'theme-minimal-fashion', 'theme-minimal-fashion');
extendCapellPackageTests(PackagesTestCase::class, 'theme-bold-sport-commerce', 'theme-bold-sport-commerce');
extendCapellPackageTests(PackagesTestCase::class, 'theme-premium-product-story', 'theme-premium-product-story');
extendCapellPackageTests(PackagesTestCase::class, 'theme-product-company-editorial', 'theme-product-company-editorial');
extendCapellPackageTests(PackagesTestCase::class, 'theme-design-led-magazine', 'theme-design-led-magazine');
extendCapellPackageTests(PackagesTestCase::class, 'theme-global-culture-magazine', 'theme-global-culture-magazine');
extendCapellPackageTests(PackagesTestCase::class, 'theme-dense-news-analysis', 'theme-dense-news-analysis');
extendCapellPackageTests(PackagesTestCase::class, 'theme-creative-culture-editorial', 'theme-creative-culture-editorial');
extendCapellPackageTests(PackagesTestCase::class, 'theme-premium-portfolio-collection', 'theme-premium-portfolio-collection');
extendCapellPackageTests(PackagesTestCase::class, 'theme-character-portfolio-index', 'theme-character-portfolio-index');
extendCapellPackageTests(PackagesTestCase::class, 'theme-case-study-platform', 'theme-case-study-platform');
extendCapellPackageTests(PackagesTestCase::class, 'theme-creative-marketplace', 'theme-creative-marketplace');
extendCapellPackageTests(PackagesTestCase::class, 'theme-portfolio-directory', 'theme-portfolio-directory');
extendCapellPackageTests(PackagesTestCase::class, 'theme-quiet-luxury-retail', 'theme-quiet-luxury-retail');
extendCapellPackageTests(PackagesTestCase::class, 'theme-automotive-dealer', 'theme-automotive-dealer');
extendCapellPackageTests(PackagesTestCase::class, 'theme-property-developer', 'theme-property-developer');
extendCapellPackageTests(PackagesTestCase::class, 'theme-recruitment-jobs', 'theme-recruitment-jobs');
extendCapellPackageTests(PackagesTestCase::class, 'theme-editorial-serif', 'theme-editorial-serif');
pest()->extend(UninstalledPackagesTestCase::class)->in('UninstalledPackages');
extendCapellPackageTests(WelcomeTourTestCase::class, 'welcome-tour', 'welcome-tour');
extendCapellPackageTests(WordPressImporterTestCase::class, 'wordpress-importer', 'wordpress-importer');
