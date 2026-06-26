<?php

declare(strict_types=1);

use Capell\AIOrchestrator\Filament\Settings\AIOrchestratorSettingsSchema;
use Capell\SeoSuite\Enums\AiDiscoveryCrawlerPolicyEnum;
use Capell\SeoSuite\Enums\AiDiscoveryStatusEnum;
use Capell\SeoSuite\Filament\Components\Forms\Page\TranslationSeoMetaSchema;
use Capell\SeoSuite\Filament\Components\Forms\Site\MetaSchema;
use Capell\SeoSuite\Filament\Components\Forms\Site\TranslationMetaSchema;
use Capell\SeoSuite\Filament\Settings\SeoSettingsSchema;
use Capell\SeoSuite\Filament\Settings\StructuredDataSettingsSchema;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * @param  array<int, mixed>  $components
 * @return array<int, mixed>
 */
function flattenSeoSuiteSchemaComponents(array $components): array
{
    $flattenedComponents = [];

    foreach ($components as $component) {
        $flattenedComponents[] = $component;

        if (is_object($component) && method_exists($component, 'getDefaultChildComponents')) {
            /** @var array<int, mixed> $childComponents */
            $childComponents = $component->getDefaultChildComponents();
            $flattenedComponents = [
                ...$flattenedComponents,
                ...flattenSeoSuiteSchemaComponents($childComponents),
            ];
        }
    }

    return $flattenedComponents;
}

/**
 * @param  array<int, mixed>  $components
 * @return array<int, string>
 */
function seoSuiteComponentNames(array $components): array
{
    return collect(flattenSeoSuiteSchemaComponents($components))
        ->filter(fn (mixed $component): bool => is_object($component) && method_exists($component, 'getName'))
        ->map(fn (object $component): string => $component->getName())
        ->values()
        ->all();
}

it('builds page translation SEO metadata fields and social sharing section', function (): void {
    $schema = TranslationSeoMetaSchema::make();

    expect($schema)->toHaveCount(3)
        ->and($schema[0])->toBeInstanceOf(TextInput::class)
        ->and($schema[0]->getName())->toBe('title')
        ->and($schema[1])->toBeInstanceOf(Textarea::class)
        ->and($schema[1]->getName())->toBe('description')
        ->and($schema[2])->toBeInstanceOf(Section::class);

    expect(seoSuiteComponentNames($schema))->toContain(
        'title',
        'description',
        'social_title',
        'social_description',
    );
});

it('builds site translation meta schema with ai discovery controls and appended components', function (): void {
    $schema = TranslationMetaSchema::make([
        TextInput::make('custom_fixture_field'),
    ]);

    expect($schema)->toHaveCount(1)
        ->and($schema[0])->toBeInstanceOf(Group::class);

    expect(seoSuiteComponentNames($schema))->toContain(
        'title_after_text',
        'description',
        'footer_copy',
        'ai_discovery.llms_txt_enabled',
        'ai_discovery.llms_full_txt_enabled',
        'ai_discovery.markdown_pages_enabled',
        'ai_discovery.accept_markdown_enabled',
        'ai_discovery.default_include_pages',
        'ai_discovery.status',
        'ai_discovery.default_section',
        'ai_discovery.max_full_txt_pages',
        'ai_discovery.max_full_txt_bytes',
        'ai_discovery.cache_ttl_seconds',
        'ai_discovery.intro_markdown',
        'custom_fixture_field',
    );

    $statusSelect = collect(flattenSeoSuiteSchemaComponents($schema))
        ->first(fn (mixed $component): bool => $component instanceof Select && $component->getName() === 'ai_discovery.status');

    expect($statusSelect)->toBeInstanceOf(Select::class)
        ->and(array_keys($statusSelect->getOptions()))->toBe([
            AiDiscoveryStatusEnum::Enabled->value,
            AiDiscoveryStatusEnum::Disabled->value,
            AiDiscoveryStatusEnum::Fresh->value,
            AiDiscoveryStatusEnum::Stale->value,
            AiDiscoveryStatusEnum::Failed->value,
        ]);
});

it('builds seo settings schema with audit toggles and crawler policy enum options', function (): void {
    $schema = SeoSettingsSchema::make(Schema::make());

    expect($schema)->toHaveCount(1)
        ->and($schema[0])->toBeInstanceOf(Grid::class);

    expect(seoSuiteComponentNames($schema))->toContain(
        'seo_audit_enabled',
        'seo_check_meta_description',
        'seo_check_meta_title',
        'seo_check_duplicate_title',
        'ai_discovery_default_enabled',
        'ai_discovery_audit_enabled',
        'ai_discovery_crawler_policy',
    );

    $crawlerPolicySelect = collect(flattenSeoSuiteSchemaComponents($schema))
        ->first(fn (mixed $component): bool => $component instanceof Select && $component->getName() === 'ai_discovery_crawler_policy');

    expect($crawlerPolicySelect)->toBeInstanceOf(Select::class)
        ->and(array_keys($crawlerPolicySelect->getOptions()))->toBe([
            AiDiscoveryCrawlerPolicyEnum::SearchVisibleTrainingRestricted->value,
            AiDiscoveryCrawlerPolicyEnum::Open->value,
            AiDiscoveryCrawlerPolicyEnum::Restrictive->value,
        ]);
});

it('builds structured data settings around the site meta schema', function (): void {
    $schema = StructuredDataSettingsSchema::make(Schema::make());

    expect($schema)->toHaveCount(1)
        ->and($schema[0])->toBeInstanceOf(Grid::class)
        ->and(collect(flattenSeoSuiteSchemaComponents($schema))->contains(
            fn (mixed $component): bool => $component instanceof MetaSchema,
        ))->toBeTrue();
});

it('keeps ai discovery boolean settings as checkbox components', function (): void {
    $schema = TranslationMetaSchema::make();
    $checkboxNames = collect(flattenSeoSuiteSchemaComponents($schema))
        ->filter(fn (mixed $component): bool => $component instanceof Checkbox)
        ->map(fn (Checkbox $component): string => $component->getName())
        ->values()
        ->all();

    expect($checkboxNames)->toContain(
        'ai_discovery.llms_txt_enabled',
        'ai_discovery.llms_full_txt_enabled',
        'ai_discovery.markdown_pages_enabled',
        'ai_discovery.accept_markdown_enabled',
        'ai_discovery.default_include_pages',
    );
});

it('builds ai orchestrator prompt settings with gated prompt templates', function (): void {
    config()->set('capell-seo-suite.openai.default_model', 'gpt-4o-mini');
    config()->set('capell-ai-orchestrator.rate_limiting.requests_per_minute', 25);

    $schema = AIOrchestratorSettingsSchema::make(Schema::make());

    expect($schema)->toHaveCount(1)
        ->and($schema[0])->toBeInstanceOf(Grid::class);

    expect(seoSuiteComponentNames($schema))->toContain(
        'model',
        'rate_limiting_requests_per_minute',
        'title_generation',
        'title_generation_system',
        'title_generation_user_template',
        'meta_description',
        'meta_description_system',
        'meta_description_user_template',
        'content_generation',
        'content_generation_system',
        'content_generation_user_template',
    );

    $checkboxNames = collect(flattenSeoSuiteSchemaComponents($schema))
        ->filter(fn (mixed $component): bool => $component instanceof Checkbox)
        ->map(fn (Checkbox $component): string => $component->getName())
        ->values()
        ->all();

    expect($checkboxNames)->toBe([
        'title_generation',
        'meta_description',
        'content_generation',
    ]);
});
