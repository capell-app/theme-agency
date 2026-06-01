<?php

declare(strict_types=1);

use Capell\Admin\Enums\PageTranslationSchemaHookEnum;
use Capell\Admin\Testing\Filament\ReadsRawSchemaComponents;
use Capell\Core\Models\Page;
use Capell\SeoSuite\Enums\RobotsDirectiveEnum;
use Capell\SeoSuite\Filament\Extenders\Page\PageSeoSettingsTabExtender;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;

it('adds seo settings as a page editor tab', function (): void {
    $extender = resolve(PageSeoSettingsTabExtender::class);

    $tabs = $extender->extendTabs(Schema::make(), []);
    $seoTab = $tabs[0] ?? null;
    $section = $seoTab instanceof Tab ? (ReadsRawSchemaComponents::childComponents($seoTab)[0] ?? null) : null;
    $components = $section instanceof Section ? ReadsRawSchemaComponents::childComponents($section) : [];
    $componentNames = collect($components)
        ->filter(fn (mixed $component): bool => method_exists($component, 'getName'))
        ->map(fn (mixed $component): string => $component->getName())
        ->all();
    $robotsField = collect($components)->first(fn (mixed $component): bool => $component instanceof CheckboxList);
    $priorityField = collect($components)->first(fn (mixed $component): bool => $component instanceof Select && $component->getName() === 'priority');
    $metaTagsField = collect($components)->first(fn (mixed $component): bool => $component instanceof Textarea);
    $aiDiscoverySection = collect($components)->first(fn (mixed $component): bool => $component instanceof Section);
    $aiDiscoveryComponents = $aiDiscoverySection instanceof Section ? ReadsRawSchemaComponents::childComponents($aiDiscoverySection) : [];

    expect($tabs)->toHaveCount(1)
        ->and($seoTab)->toBeInstanceOf(Tab::class)
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
    $section = $seoTab instanceof Tab ? (ReadsRawSchemaComponents::childComponents($seoTab)[0] ?? null) : null;
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
