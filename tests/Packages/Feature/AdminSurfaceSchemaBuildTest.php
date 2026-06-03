<?php

declare(strict_types=1);

use Capell\AgentBridge\Filament\Settings\AgentBridgeSettingsSchema;
use Capell\Blog\Filament\Configurators\Widgets\ArticleWidgetConfigurator;
use Capell\Blog\Filament\Configurators\Widgets\RelatedWidgetConfigurator;
use Capell\CampaignStudio\Filament\Configurators\Widgets\CampaignCtaWidgetWidgetConfigurator;
use Capell\CampaignStudio\Filament\Configurators\Widgets\CampaignHeroWidgetConfigurator;
use Capell\CampaignStudio\Filament\Configurators\Widgets\CampaignLeadFormWidgetConfigurator;
use Capell\Comments\Filament\Settings\CommentSettingsSchema;
use Capell\ContentSections\Filament\Components\Forms\ActionsRepeater;
use Capell\ContentSections\Filament\Components\Forms\AssetsRepeater;
use Capell\ContentSections\Filament\Configurators\Sections\AccordionSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\CallToActionSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\ComparisonSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\CounterSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\FaqSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\FeaturesSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\HeroSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\LogosSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\PricingSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\StatsSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\TableSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\TabsSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\TeamSectionConfigurator;
use Capell\ContentSections\Filament\Configurators\Sections\TimelineSectionConfigurator;
use Capell\FoundationTheme\Filament\Settings\FoundationThemeSettingsSchema;
use Capell\FrontendOptimizer\Filament\Settings\FrontendOptimizerSettingsSchema;
use Capell\GA4Reports\Filament\Settings\GA4ReportsSettingsSchema;
use Capell\LayoutBuilder\Filament\Configurators\Widgets\ModernCardGridConfigurator;
use Capell\LayoutBuilder\Filament\Configurators\Widgets\ModernFeatureListConfigurator;
use Capell\LayoutBuilder\Filament\Configurators\Widgets\ModernHeroBannerConfigurator;
use Capell\LayoutBuilder\Filament\Configurators\Widgets\ModernPricingTableConfigurator;
use Capell\LayoutBuilder\Filament\Configurators\Widgets\ModernProcessStepsConfigurator;
use Capell\LayoutBuilder\Filament\Configurators\Widgets\ModernTestimonialsConfigurator;
use Capell\LoginAudit\Filament\Settings\LoginAuditSettingsSchema;
use Capell\Newsletter\Filament\Settings\NewsletterSettingsSchema;
use Capell\PasswordPolicy\Filament\Settings\PasswordPolicySettingsSchema;
use Capell\PublishingStudio\Filament\Settings\PublishingStudioSettingsSchema;
use Capell\ShopifyCommerce\Filament\Settings\ShopifyCommerceSettingsSchema;
use Capell\Tests\Packages\Fixtures\PackageAdminSurfaceSchemaLivewireHarness;
use Capell\WelcomeTour\Filament\Settings\WelcomeTourSettingsSchema;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

/**
 * @return list<mixed>
 */
function packageAdminSurfaceConfiguratorComponents(string $className, Schema $schema): array
{
    $configurator = new $className;
    $callable = [$configurator, 'make'];

    if (! is_callable($callable)) {
        return [];
    }

    $components = $callable($schema);

    return is_array($components) ? array_values($components) : [];
}

/**
 * @return list<mixed>
 */
function packageAdminSurfaceStaticComponents(string $className, Schema $schema): array
{
    $callable = [$className, 'make'];

    if (! is_callable($callable)) {
        return [];
    }

    $components = $callable($schema);

    return is_array($components) ? array_values($components) : [];
}

/**
 * @return list<mixed>
 */
function packageAdminSurfaceStaticFormSchema(string $className): array
{
    $callable = [$className, 'getFormSchema'];

    if (! is_callable($callable)) {
        return [];
    }

    $components = $callable();

    return is_array($components) ? array_values($components) : [];
}

/**
 * @return array<string, mixed>
 */
function packageAdminSurfaceStaticDefaults(string $className): array
{
    $callable = [$className, 'getDefaults'];

    if (! is_callable($callable)) {
        return [];
    }

    $defaults = $callable();

    return is_array($defaults) ? $defaults : [];
}

it('builds package-owned admin configurator schemas for representative workflows', function (string $className, array $operations, array $expectedNames): void {
    $allComponents = [];

    foreach ($operations as $operation) {
        $schema = packageAdminSurfaceSchema($operation);
        $components = packageAdminSurfaceConfiguratorComponents($className, $schema);

        expect($components)->not->toBeEmpty();

        array_push($allComponents, ...$components);
    }

    $flattenedComponents = flattenPackageAdminSurfaceComponents(
        packageAdminSurfacePreparedComponents($allComponents),
    );

    expect($flattenedComponents)
        ->not->toBeEmpty()
        ->and(count($flattenedComponents))->toBeGreaterThanOrEqual(count($expectedNames));
})->with([
    'blog article widget' => [
        ArticleWidgetConfigurator::class,
        ['create', 'edit', 'editOption'],
        ['meta.with_date', 'meta.with_next_prev', 'meta.with_author'],
    ],
    'blog related widget' => [
        RelatedWidgetConfigurator::class,
        ['createOption', 'edit'],
        ['exclude_parent', 'exclude_types', 'limit', 'pagination', 'cache_frequency'],
    ],
    'campaign cta widget' => [
        CampaignCtaWidgetWidgetConfigurator::class,
        ['edit'],
        ['meta.cta_widget_id'],
    ],
    'campaign hero widget' => [
        CampaignHeroWidgetConfigurator::class,
        ['edit'],
        ['campaign_hero'],
    ],
    'campaign lead form widget' => [
        CampaignLeadFormWidgetConfigurator::class,
        ['edit'],
        ['campaign_form'],
    ],
    'content hero section' => [
        HeroSectionConfigurator::class,
        ['create', 'edit', 'createOption', 'editOption'],
        ['translations', 'assets', 'related', 'actions', 'image', 'page_id', 'link_text'],
    ],
    'content accordion section' => [
        AccordionSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'items', 'first_open'],
    ],
    'content call to action section' => [
        CallToActionSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'image', 'color', 'alignment', 'actions'],
    ],
    'content comparison section' => [
        ComparisonSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'columns', 'rows'],
    ],
    'content counter section' => [
        CounterSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'counters', 'animate'],
    ],
    'content faq section' => [
        FaqSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'questions', 'first_open'],
    ],
    'content features section' => [
        FeaturesSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'features', 'columns'],
    ],
    'content logos section' => [
        LogosSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'logos', 'columns'],
    ],
    'content pricing section' => [
        PricingSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'plans'],
    ],
    'content stats section' => [
        StatsSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'stats', 'columns'],
    ],
    'content table section' => [
        TableSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'caption', 'headers', 'rows'],
    ],
    'content tabs section' => [
        TabsSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'tabs'],
    ],
    'content team section' => [
        TeamSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'members', 'columns'],
    ],
    'content timeline section' => [
        TimelineSectionConfigurator::class,
        ['create', 'editOption'],
        ['translations', 'milestones'],
    ],
]);

it('builds package settings schemas with their persisted control names', function (string $className, array $expectedNames): void {
    $components = packageAdminSurfaceStaticComponents($className, packageAdminSurfaceSchema('edit'));

    $flattenedComponents = flattenPackageAdminSurfaceComponents(
        packageAdminSurfacePreparedComponents($components),
    );

    expect($components)->not->toBeEmpty()
        ->and(count($flattenedComponents))->toBeGreaterThanOrEqual(count($expectedNames));
})->with([
    'agent bridge' => [
        AgentBridgeSettingsSchema::class,
        ['agent_bridge_settings'],
    ],
    'comments' => [
        CommentSettingsSchema::class,
        ['enabled', 'moderation_required', 'allow_anonymous'],
    ],
    'foundation theme' => [
        FoundationThemeSettingsSchema::class,
        ['primary_color', 'secondary_color', 'font_family'],
    ],
    'frontend optimizer' => [
        FrontendOptimizerSettingsSchema::class,
        ['enabled', 'scope', 'playwright_browser'],
    ],
    'ga4 reports' => [
        GA4ReportsSettingsSchema::class,
        ['enabled', 'property_id', 'credentials_path', 'sync_days', 'route_slug'],
    ],
    'login audit' => [
        LoginAuditSettingsSchema::class,
        ['enabled', 'track_successful_logins', 'track_failed_logins'],
    ],
    'newsletter' => [
        NewsletterSettingsSchema::class,
        ['default_provider', 'double_opt_in', 'sync_batch_size'],
    ],
    'password policy' => [
        PasswordPolicySettingsSchema::class,
        ['min_length', 'require_uppercase', 'require_numbers'],
    ],
    'publishing studio' => [
        PublishingStudioSettingsSchema::class,
        ['publishing_studio_settings'],
    ],
    'shopify commerce' => [
        ShopifyCommerceSettingsSchema::class,
        ['enabled', 'api_version', 'scopes'],
    ],
    'welcome tour' => [
        WelcomeTourSettingsSchema::class,
        ['enabled', 'steps'],
    ],
]);

it('builds modern layout widget configurator schemas and defaults', function (string $className, array $expectedNames, array $expectedDefaultKeys): void {
    $components = packageAdminSurfaceStaticFormSchema($className);
    $defaults = packageAdminSurfaceStaticDefaults($className);

    $flattenedComponents = flattenPackageAdminSurfaceComponents(
        packageAdminSurfacePreparedComponents($components),
    );

    expect($components)->not->toBeEmpty()
        ->and(count($flattenedComponents))->toBeGreaterThanOrEqual(count($expectedNames))
        ->and(array_keys($defaults))->toContain(...$expectedDefaultKeys);
})->with([
    'modern hero banner' => [
        ModernHeroBannerConfigurator::class,
        ['data.title', 'data.subtitle', 'data.primaryCta.label', 'data.height', 'data.backgroundImage'],
        ['title', 'subtitle', 'primaryCta', 'height'],
    ],
    'modern card grid' => [
        ModernCardGridConfigurator::class,
        ['data.title', 'data.cards', 'data.columns', 'data.cardStyle'],
        ['title', 'cards', 'columns'],
    ],
    'modern feature list' => [
        ModernFeatureListConfigurator::class,
        ['data.title', 'data.features', 'data.layout'],
        ['title', 'layout', 'columns'],
    ],
    'modern pricing table' => [
        ModernPricingTableConfigurator::class,
        ['data.title', 'data.plans', 'data.billingToggle'],
        ['title', 'currency', 'billingOptions'],
    ],
    'modern testimonials' => [
        ModernTestimonialsConfigurator::class,
        ['data.title', 'data.testimonials', 'data.layout'],
        ['title', 'displayMode', 'columns'],
    ],
    'modern process steps' => [
        ModernProcessStepsConfigurator::class,
        ['data.title', 'data.steps', 'data.orientation'],
        ['title', 'subtitle', 'layout'],
    ],
]);

it('builds package asset repeaters and action repeaters used inside admin form workflows', function (string $className, array $expectedNames): void {
    $component = $className::make('coverage_component');
    $schemaComponents = [];

    if (method_exists($className, 'getFormSchema')) {
        $formSchema = new ReflectionMethod($className, 'getFormSchema');
        $schemaComponents = $formSchema->invoke(null);
    } elseif (method_exists($component, 'getDefaultChildComponents')) {
        $schemaComponents = $component->getDefaultChildComponents();
    }

    $preparedNames = packageAdminSurfaceComponentNames(
        is_array($schemaComponents) ? $schemaComponents : [],
    );

    expect($component)->toBeInstanceOf(Repeater::class)
        ->and($preparedNames)->toContain(...$expectedNames);

    if (method_exists($component, 'getAddAssetAction')) {
        expect($component->getAddAssetAction()->getName())->toBe('add_asset');
    }
})->with([
    'content section assets' => [
        AssetsRepeater::class,
        ['asset_type', 'asset_id'],
    ],
    'layout builder assets' => [
        Capell\LayoutBuilder\Filament\Components\Forms\AssetsRepeater::class,
        ['asset_type', 'asset_id'],
    ],
    'content section actions' => [
        ActionsRepeater::class,
        ['type'],
    ],
    'layout builder actions' => [
        Capell\LayoutBuilder\Filament\Components\Forms\ActionsRepeater::class,
        ['type'],
    ],
]);

/**
 * @param  array<int, mixed>  $components
 * @return array<int, mixed>
 */
function flattenPackageAdminSurfaceComponents(array $components): array
{
    $flattenedComponents = [];

    foreach ($components as $component) {
        $flattenedComponents[] = $component;
        if (! is_object($component)) {
            continue;
        }

        if (! method_exists($component, 'getDefaultChildComponents')) {
            continue;
        }

        $childComponents = $component->getDefaultChildComponents();

        if (is_array($childComponents)) {
            array_push($flattenedComponents, ...flattenPackageAdminSurfaceComponents($childComponents));
        }
    }

    return $flattenedComponents;
}

/**
 * @param  array<int, mixed>  $components
 * @return array<int, string>
 */
function packageAdminSurfaceComponentNames(array $components): array
{
    return collect(flattenPackageAdminSurfaceComponents(packageAdminSurfacePreparedComponents($components)))
        ->filter(fn (mixed $component): bool => is_object($component) && method_exists($component, 'getName'))
        ->map(fn (mixed $component): string => $component->getName())
        ->values()
        ->all();
}

/**
 * @param  array<int, mixed>  $components
 * @return array<int, mixed>
 */
function packageAdminSurfacePreparedComponents(array $components): array
{
    return packageAdminSurfaceSchema('inspect')
        ->components($components)
        ->getComponents();
}

function packageAdminSurfaceSchema(string $operation): Schema
{
    return Schema::make(new PackageAdminSurfaceSchemaLivewireHarness)->operation($operation);
}
