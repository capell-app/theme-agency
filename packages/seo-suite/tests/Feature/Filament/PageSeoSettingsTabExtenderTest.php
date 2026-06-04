<?php

declare(strict_types=1);

use Capell\Admin\Enums\PageTranslationSchemaHookEnum;
use Capell\Admin\Filament\Components\Forms\Page\TranslationsRepeater;
use Capell\Admin\Testing\Filament\ReadsRawSchemaComponents;
use Capell\Core\Models\Page;
use Capell\SeoSuite\Enums\RobotsDirectiveEnum;
use Capell\SeoSuite\Filament\Components\Forms\Page\PageSeoPanel;
use Capell\SeoSuite\Filament\Extenders\Page\PageSeoSettingsTabExtender;
use Capell\SeoSuite\Support\Admin\RemoveInlineSeoTranslationComponents;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;

it('adds seo settings as a page editor tab', function (): void {
    $extender = resolve(PageSeoSettingsTabExtender::class);

    $tabs = $extender->extendTabs(Schema::make(), []);
    $seoTab = $tabs[0] ?? null;
    $tabComponents = $seoTab instanceof Tab ? ReadsRawSchemaComponents::childComponents($seoTab) : [];
    $auditTabs = $tabComponents[0] ?? null;
    $translationsRepeater = $tabComponents[1] ?? null;
    $section = $tabComponents[2] ?? null;
    $components = $section instanceof Section ? ReadsRawSchemaComponents::childComponents($section) : [];
    $translationComponents = $translationsRepeater instanceof TranslationsRepeater
        ? ReadsRawSchemaComponents::childComponents($translationsRepeater)
        : [];
    $translationSeoSection = $translationComponents[0] ?? null;
    $translationSeoFields = $translationSeoSection instanceof Section
        ? ReadsRawSchemaComponents::childComponents($translationSeoSection)
        : [];
    $componentNames = collect($components)
        ->filter(fn (mixed $component): bool => $component instanceof Field)
        ->map(fn (Field $component): string => $component->getName())
        ->all();
    $robotsField = collect($components)->first(fn (mixed $component): bool => $component instanceof CheckboxList);
    $priorityField = collect($components)->first(fn (mixed $component): bool => $component instanceof Select && $component->getName() === 'priority');
    $metaTagsField = collect($components)->first(fn (mixed $component): bool => $component instanceof Textarea);
    $aiDiscoverySection = collect($components)->first(fn (mixed $component): bool => $component instanceof Section);
    $aiDiscoveryComponents = $aiDiscoverySection instanceof Section ? ReadsRawSchemaComponents::childComponents($aiDiscoverySection) : [];

    expect($tabs)->toHaveCount(1)
        ->and($seoTab)->toBeInstanceOf(Tab::class)
        ->and($auditTabs)->toBeInstanceOf(Livewire::class)
        ->and($auditTabs->getComponent())->toBe('capell-seo-suite.edit-page-audit-tabs')
        ->and($auditTabs->isLazy())->toBeTrue()
        ->and($translationsRepeater)->toBeInstanceOf(TranslationsRepeater::class)
        ->and($translationSeoSection)->toBeInstanceOf(Section::class)
        ->and(collect($translationSeoFields)->map(fn (mixed $component): ?string => $component instanceof Field ? $component->getName() : null)->filter()->all())
        ->toContain('title', 'description', 'keywords')
        ->and($translationComponents[1] ?? null)->toBeInstanceOf(PageSeoPanel::class)
        ->and($section)->toBeInstanceOf(Section::class)
        ->and($componentNames)->toContain('canonical_page_id', 'cache_time', 'priority', 'canonical_url', 'robots', 'meta_tags')
        ->and($priorityField)->toBeInstanceOf(Select::class)
        ->and($robotsField)->toBeInstanceOf(CheckboxList::class)
        ->and($robotsField->getName())->toBe('robots')
        ->and($robotsField->getOptions())->toBe(
            collect(RobotsDirectiveEnum::cases())
                ->mapWithKeys(fn (RobotsDirectiveEnum $directive): array => [$directive->value => $directive->getLabel()])
                ->all(),
        )
        ->and($robotsField->getColumnSpan('lg'))->toBe(2)
        ->and($metaTagsField)->toBeInstanceOf(Textarea::class)
        ->and($metaTagsField->getName())->toBe('meta_tags')
        ->and($aiDiscoverySection)->toBeInstanceOf(Section::class)
        ->and($aiDiscoveryComponents)->toHaveCount(6)
        ->and($aiDiscoveryComponents[0])->toBeInstanceOf(Checkbox::class)
        ->and($aiDiscoveryComponents[0]->getName())->toBe('ai_discovery.include_in_ai_index')
        ->and($aiDiscoveryComponents[1])->toBeInstanceOf(TextInput::class)
        ->and($aiDiscoveryComponents[1]->getName())->toBe('ai_discovery.section');
});

it('removes inline seo components from the main translation editor', function (): void {
    $replacer = new RemoveInlineSeoTranslationComponents;
    $components = [
        TextInput::make('title'),
        Section::make(__('capell-admin::tab.seo_settings')),
        PageSeoPanel::make(),
        TextInput::make('body'),
    ];

    $filteredComponents = $replacer(Schema::make(), $components);

    expect($filteredComponents)->toHaveCount(2)
        ->and($filteredComponents[0])->toBeInstanceOf(TextInput::class)
        ->and($filteredComponents[0]->getName())->toBe('title')
        ->and($filteredComponents[1])->toBeInstanceOf(TextInput::class)
        ->and($filteredComponents[1]->getName())->toBe('body');
});

it('leaves unrelated page schema extension points unchanged', function (): void {
    $extender = resolve(PageSeoSettingsTabExtender::class);
    $page = Page::factory()->create();
    $relationManagers = ['existing'];

    expect($extender->extendTranslationComponentsForHook(Schema::make(), PageTranslationSchemaHookEnum::AfterSearchMeta))
        ->toBe([])
        ->and($extender->extendRelationManagers($page, $relationManagers))->toBe($relationManagers)
        ->and($extender->extendSidebarComponents(Schema::make()))->toBe([]);
});

it('normalizes legacy associative robots meta for the checkbox list', function (): void {
    $extender = resolve(PageSeoSettingsTabExtender::class);
    $normalizer = new ReflectionMethod($extender, 'normalizeRobotsState');

    expect($normalizer->invoke($extender, ['noindex' => false]))->toBe([])
        ->and($normalizer->invoke($extender, ['noindex' => true, 'nofollow' => false]))->toBe(['noindex'])
        ->and($normalizer->invoke($extender, ['noindex', 'max-snippet:-1']))->toBe(['noindex', 'max-snippet:-1'])
        ->and($normalizer->invoke($extender, 'noindex, nofollow'))->toBe(['noindex', 'nofollow']);
});

it('normalizes empty checkbox robots state before validation and dehydration', function (): void {
    $extender = resolve(PageSeoSettingsTabExtender::class);
    $tabs = $extender->extendTabs(Schema::make(), []);
    $seoTab = $tabs[0] ?? null;
    $tabComponents = $seoTab instanceof Tab ? ReadsRawSchemaComponents::childComponents($seoTab) : [];
    $section = collect($tabComponents)->first(
        fn (mixed $component): bool => $component instanceof Section
            && $component->getHeading() === __('capell-seo-suite::generic.seo_settings'),
    );
    $components = $section instanceof Section ? ReadsRawSchemaComponents::childComponents($section) : [];
    $robotsField = collect($components)->first(fn (mixed $component): bool => $component instanceof CheckboxList);

    expect($robotsField)->toBeInstanceOf(CheckboxList::class)
        ->and($robotsField->mutateStateForValidation([false]))->toBe([])
        ->and($robotsField->mutateStateForValidation([null]))->toBe([])
        ->and($robotsField->mutateStateForValidation([]))->toBe([])
        ->and($robotsField->mutateStateForValidation(['noindex']))->toBe(['noindex']);

    expect(Validator::make(
        ['robots' => $robotsField->mutateStateForValidation([false])],
        ['robots.*' => [$robotsField->getInValidationRule()]],
    )->passes())->toBeTrue();
});
