<?php

declare(strict_types=1);

use Capell\SeoSuite\Actions\SuggestMetaDescriptionsAction;
use Capell\SeoSuite\Actions\SuggestPageTitlesAction;
use Capell\SeoSuite\Settings\AIOrchestratorSettings;
use Capell\SeoSuite\Support\Admin\PageContentEditorConfigurator;
use Capell\SeoSuite\Support\Admin\PageTitleWithSlugInputExtender;
use Capell\SeoSuite\Support\Admin\SearchMetaDataSectionExtender;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

beforeEach(function (): void {
    test()->registerAndMigrateSettings(
        ['2026_05_10_190871_01_create_ai-orchestrator_settings'],
        dirname(__DIR__, 3) . '/database/settings',
    );
});

/**
 * @param  array<array-key, mixed>  $arguments
 * @return array<int, mixed>
 */
function seoSuiteAdminActionSchema(Action $action, array $arguments = []): array
{
    $property = new ReflectionProperty($action, 'schema');
    $schema = $property->getValue($action);

    if ($schema instanceof Closure) {
        $reflection = new ReflectionFunction($schema);
        $firstParameter = $reflection->getParameters()[0] ?? null;

        if ($firstParameter?->getName() === 'arguments') {
            return $schema($arguments);
        }

        return $schema();
    }

    return is_array($schema) ? $schema : [];
}

it('builds the page content generator action schema when content generation is enabled', function (): void {
    $settings = resolve(AIOrchestratorSettings::class);
    $settings->prompts = [
        ...$settings->prompts,
        'content_generation' => true,
    ];
    $settings->save();

    $configurator = new PageContentEditorConfigurator;
    $enabledMethod = new ReflectionMethod(PageContentEditorConfigurator::class, 'isEnabled');
    $actionMethod = new ReflectionMethod(PageContentEditorConfigurator::class, 'generateContentAction');
    $action = $actionMethod->invoke($configurator);
    $schema = seoSuiteAdminActionSchema($action);

    expect($enabledMethod->invoke($configurator))->toBeTrue()
        ->and($action->getName())->toBe('generateContent')
        ->and($schema)->toHaveCount(5)
        ->and($schema[0])->toBeInstanceOf(Hidden::class)
        ->and($schema[0]->getName())->toBe('content')
        ->and($schema[1])->toBeInstanceOf(Checkbox::class)
        ->and($schema[1]->getName())->toBe('includeCurrentContent')
        ->and($schema[2])->toBeInstanceOf(Textarea::class)
        ->and($schema[2]->getName())->toBe('keywords')
        ->and($schema[3])->toBeInstanceOf(TextInput::class)
        ->and($schema[3]->getName())->toBe('title')
        ->and($schema[4])->toBeInstanceOf(TextInput::class)
        ->and($schema[4]->getName())->toBe('target_length');
});

it('hides the page content generator action when content generation is disabled', function (): void {
    $settings = resolve(AIOrchestratorSettings::class);
    $settings->prompts = [
        ...$settings->prompts,
        'content_generation' => false,
    ];
    $settings->save();

    $method = new ReflectionMethod(PageContentEditorConfigurator::class, 'isEnabled');

    expect($method->invoke(new PageContentEditorConfigurator))->toBeFalse();
});

it('builds title suggestion action schemas and suggested title modal schema', function (): void {
    $settings = resolve(AIOrchestratorSettings::class);
    $settings->prompts = [
        ...$settings->prompts,
        'title_generation' => true,
    ];
    $settings->save();

    $extender = new PageTitleWithSlugInputExtender(resolve(SuggestPageTitlesAction::class));
    $actionMethod = new ReflectionMethod(PageTitleWithSlugInputExtender::class, 'titleSuggestionsAction');
    $suggestedMethod = new ReflectionMethod(PageTitleWithSlugInputExtender::class, 'suggestedTitlesAction');

    $action = $actionMethod->invoke($extender);
    $schema = seoSuiteAdminActionSchema($action);
    $suggestedAction = $suggestedMethod->invoke($extender);
    $suggestedSchema = seoSuiteAdminActionSchema($suggestedAction, [
        'titles' => ['First SEO title', 'Second SEO title'],
    ]);

    expect($action->getName())->toBe('generate')
        ->and($schema)->toHaveCount(4)
        ->and($schema[0])->toBeInstanceOf(Hidden::class)
        ->and($schema[0]->getName())->toBe('title')
        ->and($schema[1])->toBeInstanceOf(Radio::class)
        ->and($schema[1]->getName())->toBe('includeCurrentTitle')
        ->and($schema[2])->toBeInstanceOf(Textarea::class)
        ->and($schema[2]->getName())->toBe('keywords')
        ->and($schema[3])->toBeInstanceOf(Textarea::class)
        ->and($schema[3]->getName())->toBe('content')
        ->and($suggestedAction->getName())->toBe('suggested_titles')
        ->and($suggestedSchema)->toHaveCount(1)
        ->and($suggestedSchema[0])->toBeInstanceOf(Radio::class)
        ->and($suggestedSchema[0]->getName())->toBe('titles');
});

it('builds meta description suggestion action schemas and suggested description modal schema', function (): void {
    $settings = resolve(AIOrchestratorSettings::class);
    $settings->prompts = [
        ...$settings->prompts,
        'meta_description' => true,
    ];
    $settings->save();

    $extender = new SearchMetaDataSectionExtender(resolve(SuggestMetaDescriptionsAction::class));
    $actionMethod = new ReflectionMethod(SearchMetaDataSectionExtender::class, 'metaDescriptionSuggestionsAction');
    $suggestedMethod = new ReflectionMethod(SearchMetaDataSectionExtender::class, 'suggestedMetaDescriptionsAction');

    $action = $actionMethod->invoke($extender);
    $schema = seoSuiteAdminActionSchema($action);
    $suggestedAction = $suggestedMethod->invoke($extender);
    $suggestedSchema = seoSuiteAdminActionSchema($suggestedAction, [
        'descriptions' => ['First SEO description', 'Second SEO description'],
    ]);

    expect($action->getName())->toBe('generate_meta_descriptions')
        ->and($schema)->toHaveCount(4)
        ->and($schema[0])->toBeInstanceOf(Hidden::class)
        ->and($schema[0]->getName())->toBe('currentDescription')
        ->and($schema[1])->toBeInstanceOf(Radio::class)
        ->and($schema[1]->getName())->toBe('includeCurrentDescription')
        ->and($schema[2])->toBeInstanceOf(Textarea::class)
        ->and($schema[2]->getName())->toBe('keywords')
        ->and($schema[3])->toBeInstanceOf(Textarea::class)
        ->and($schema[3]->getName())->toBe('content')
        ->and($suggestedAction->getName())->toBe('suggested_meta_descriptions')
        ->and($suggestedSchema)->toHaveCount(1)
        ->and($suggestedSchema[0])->toBeInstanceOf(Radio::class)
        ->and($suggestedSchema[0]->getName())->toBe('descriptions');
});
