<?php

declare(strict_types=1);

use Capell\AIOrchestrator\Actions\Ai\SuggestMetaDescriptionsAction;
use Capell\AIOrchestrator\Actions\Ai\SuggestPageTitlesAction;
use Capell\AIOrchestrator\Settings\AIOrchestratorSettings;
use Capell\SeoSuite\Filament\Actions\AiCreatorAction;
use Capell\SeoSuite\Support\Admin\PageContentEditorConfigurator;
use Capell\SeoSuite\Support\Admin\PageTitleWithSlugInputExtender;
use Capell\SeoSuite\Support\Admin\SearchMetaDataSectionExtender;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;

beforeEach(function (): void {
    test()->registerAndMigrateSettings(
        ['2026_05_10_190871_01_create_ai-orchestrator_settings'],
        dirname(__DIR__, 4) . '/ai-orchestrator/database/settings',
    );
});

it('exposes ai title and meta description actions when prompts are enabled', function (): void {
    $settings = resolve(AIOrchestratorSettings::class);
    $settings->prompts = [
        ...$settings->prompts,
        'title_generation' => true,
        'meta_description' => true,
    ];
    $settings->save();

    $titleExtender = new PageTitleWithSlugInputExtender(resolve(SuggestPageTitlesAction::class));
    $metaExtender = new SearchMetaDataSectionExtender(resolve(SuggestMetaDescriptionsAction::class));

    expect($titleExtender->actions())->toHaveCount(1)
        ->and($titleExtender->actions()[0]->getName())->toBe('generate')
        ->and($metaExtender->headerActions(Section::make('Search metadata')))->toHaveCount(1)
        ->and($metaExtender->headerActions(Section::make('Search metadata'))[0]->getName())->toBe('generate_meta_descriptions');
});

it('hides ai admin extenders when prompts are disabled', function (): void {
    $settings = resolve(AIOrchestratorSettings::class);
    $settings->prompts = [
        ...$settings->prompts,
        'title_generation' => false,
        'meta_description' => false,
        'content_generation' => false,
    ];
    $settings->save();

    $titleExtender = new PageTitleWithSlugInputExtender(resolve(SuggestPageTitlesAction::class));
    $metaExtender = new SearchMetaDataSectionExtender(resolve(SuggestMetaDescriptionsAction::class));
    $textarea = Textarea::make('content');

    (new PageContentEditorConfigurator)($textarea);

    expect($titleExtender->actions())->toBe([])
        ->and($metaExtender->headerActions(Section::make('Search metadata')))->toBe([]);
});

it('builds the ai creator wizard form schema', function (): void {
    $action = AiCreatorAction::make();
    $buildWizardForm = new ReflectionMethod(AiCreatorAction::class, 'buildWizardForm');

    $schema = $buildWizardForm->invoke($action);

    expect($action->getName())->toBe('ai-creator')
        ->and($schema)->toHaveCount(2)
        ->and($schema[0]->getName())->toBe('ai_session_id')
        ->and($schema[1])->toBeInstanceOf(Wizard::class);
});
