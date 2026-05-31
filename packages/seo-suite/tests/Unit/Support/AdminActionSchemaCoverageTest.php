<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Actions\GeneratorPageContentAction;
use Capell\SeoSuite\Actions\SuggestMetaDescriptionsAction;
use Capell\SeoSuite\Actions\SuggestPageTitlesAction;
use Capell\SeoSuite\Contracts\AiActionContextInterface;
use Capell\SeoSuite\Settings\AIOrchestratorSettings;
use Capell\SeoSuite\Support\Admin\PageContentEditorConfigurator;
use Capell\SeoSuite\Support\Admin\PageTitleWithSlugInputExtender;
use Capell\SeoSuite\Support\Admin\SearchMetaDataSectionExtender;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Exceptions\Halt;

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

it('drives seo ai admin action workflows from form input to generated state', function (): void {
    $settings = resolve(AIOrchestratorSettings::class);
    $settings->prompts = [
        ...$settings->prompts,
        'content_generation' => true,
        'title_generation' => true,
        'meta_description' => true,
    ];
    $settings->save();

    $translation = new Translation;
    $translation->forceFill([
        'title' => 'Current title',
        'language_id' => 7,
        'translatable_id' => 42,
        'translatable_type' => 'core_page',
        'meta' => ['description' => 'Current description'],
    ]);

    $generator = Mockery::mock(GeneratorPageContentAction::class);
    $generator->shouldReceive('handle')
        ->once()
        ->withArgs(fn (AiActionContextInterface $context, array $options): bool => $context->getContent() === 'Existing body'
            && $context->getKeywords() === 'coverage, seo'
            && $context->getLanguageId() === 7
            && $options['current_title'] === 'Current title'
            && $options['target_length'] === 900
            && $options['refactor'] === true)
        ->andReturn('<p>Generated page body</p>');
    app()->instance(GeneratorPageContentAction::class, $generator);

    $contentAction = (new ReflectionMethod(PageContentEditorConfigurator::class, 'generateContentAction'))
        ->invoke(new PageContentEditorConfigurator);
    $contentState = [];

    evaluateSeoSuiteAdminAction($contentAction, [
        'set' => seoSuiteAdminFakeSet($contentState),
        'component' => new stdClass,
        'record' => $translation,
        'data' => [
            'keywords' => ' coverage, seo ',
            'content' => ' Existing body ',
            'includeCurrentContent' => true,
            'title' => 'Current title',
            'target_length' => '900',
        ],
    ], [
        Set::class => seoSuiteAdminFakeSet($contentState),
        Translation::class => $translation,
    ]);

    $titleGenerator = Mockery::mock(SuggestPageTitlesAction::class);
    $titleGenerator->shouldReceive('run')
        ->once()
        ->withArgs(fn (AiActionContextInterface $context, array $options): bool => $context->getKeywords() === 'primary keyword'
            && $context->getContent() === 'Article body'
            && $options['current_title'] === 'Current title')
        ->andReturn(['First title', 'Second title']);

    $mountedTitleAction = [];
    $titleLivewire = seoSuiteAdminHasActions($mountedTitleAction);
    $titleComponent = FusedGroup::make([]);
    $titleAction = (new ReflectionMethod(PageTitleWithSlugInputExtender::class, 'titleSuggestionsAction'))
        ->invoke(new PageTitleWithSlugInputExtender($titleGenerator));

    evaluateSeoSuiteAdminAction($titleAction, [
        'livewire' => $titleLivewire,
        'component' => $titleComponent,
        'record' => $translation,
        'data' => [
            'keywords' => ' primary keyword ',
            'content' => ' Article body ',
            'includeCurrentTitle' => true,
            'title' => 'Current title',
        ],
    ], [
        HasActions::class => $titleLivewire,
        FusedGroup::class => $titleComponent,
        Translation::class => $translation,
    ]);

    $metaGenerator = Mockery::mock(SuggestMetaDescriptionsAction::class);
    $metaGenerator->shouldReceive('run')
        ->once()
        ->withArgs(fn (AiActionContextInterface $context, array $options): bool => $context->getKeywords() === 'meta keyword'
            && $context->getContent() === 'Meta body'
            && $options['current_description'] === 'Current description')
        ->andReturn(['First description', 'Second description']);

    $mountedMetaAction = [];
    $metaLivewire = seoSuiteAdminHasActions($mountedMetaAction);
    $metaAction = (new ReflectionMethod(SearchMetaDataSectionExtender::class, 'metaDescriptionSuggestionsAction'))
        ->invoke(new SearchMetaDataSectionExtender($metaGenerator));

    evaluateSeoSuiteAdminAction($metaAction, [
        'livewire' => $metaLivewire,
        'component' => Section::make('Search metadata'),
        'record' => $translation,
        'data' => [
            'keywords' => ' meta keyword ',
            'content' => ' Meta body ',
            'includeCurrentDescription' => true,
            'currentDescription' => 'Current description',
        ],
    ], [
        HasActions::class => $metaLivewire,
        Section::class => Section::make('Search metadata'),
        Translation::class => $translation,
    ]);

    $titleState = [];
    $unusedMountedTitleAction = [];
    $suggestedTitleAction = (new ReflectionMethod(PageTitleWithSlugInputExtender::class, 'suggestedTitlesAction'))
        ->invoke(new PageTitleWithSlugInputExtender(resolve(SuggestPageTitlesAction::class)));
    evaluateSeoSuiteAdminAction($suggestedTitleAction, [
        'set' => seoSuiteAdminFakeSet($titleState),
        'livewire' => seoSuiteAdminHasActions($unusedMountedTitleAction),
        'data' => ['titles' => 'Second title'],
    ], [
        Set::class => seoSuiteAdminFakeSet($titleState),
    ]);

    $descriptionState = [];
    $unusedMountedDescriptionAction = [];
    $suggestedDescriptionAction = (new ReflectionMethod(SearchMetaDataSectionExtender::class, 'suggestedMetaDescriptionsAction'))
        ->invoke(new SearchMetaDataSectionExtender(resolve(SuggestMetaDescriptionsAction::class)));
    evaluateSeoSuiteAdminAction($suggestedDescriptionAction, [
        'set' => seoSuiteAdminFakeSet($descriptionState),
        'livewire' => seoSuiteAdminHasActions($unusedMountedDescriptionAction),
        'data' => ['descriptions' => 'Second description'],
    ], [
        Set::class => seoSuiteAdminFakeSet($descriptionState),
    ]);

    expect($contentState['content'])->toBe('<p>Generated page body</p>')
        ->and($mountedTitleAction)->toBe([
            'name' => 'suggested_titles',
            'arguments' => ['titles' => ['First title', 'Second title']],
        ])
        ->and($mountedMetaAction)->toBe([
            'name' => 'suggested_meta_descriptions',
            'arguments' => ['descriptions' => ['First description', 'Second description']],
        ])
        ->and($titleState['title'])->toBe('Second title')
        ->and($descriptionState['description'])->toBe('Second description');
});

it('prefills seo ai admin actions from the surrounding page and site translation state', function (): void {
    $settings = resolve(AIOrchestratorSettings::class);
    $settings->prompts = [
        ...$settings->prompts,
        'content_generation' => true,
        'title_generation' => true,
        'meta_description' => true,
    ];
    $settings->save();

    $language = Language::factory()->create(['code' => 'en']);
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, [
            'title' => 'Fallback site title',
            'meta' => ['keywords' => 'site fallback keywords'],
        ])
        ->create();

    $get = seoSuiteAdminFakeGet([
        '../../site_id' => $site->getKey(),
        '../../name' => 'Fallback page name',
        'language_id' => $language->getKey(),
        'content' => '<p>Existing <strong>body</strong></p>',
        'title' => '',
        'meta' => [],
        'meta.description' => 'Current search description',
        'meta.keywords' => null,
    ]);

    $contentAction = (new ReflectionMethod(PageContentEditorConfigurator::class, 'generateContentAction'))
        ->invoke(new PageContentEditorConfigurator);
    $titleAction = (new ReflectionMethod(PageTitleWithSlugInputExtender::class, 'titleSuggestionsAction'))
        ->invoke(new PageTitleWithSlugInputExtender(resolve(SuggestPageTitlesAction::class)));
    $descriptionAction = (new ReflectionMethod(SearchMetaDataSectionExtender::class, 'metaDescriptionSuggestionsAction'))
        ->invoke(new SearchMetaDataSectionExtender(resolve(SuggestMetaDescriptionsAction::class)));

    expect(seoSuiteAdminFillFormData($contentAction, $get))->toMatchArray([
        'keywords' => 'site fallback keywords',
        'content' => 'Existing body',
        'title' => 'Fallback page name',
        'includeCurrentContent' => true,
    ])
        ->and(seoSuiteAdminFillFormData($titleAction, $get))->toMatchArray([
            'keywords' => 'site fallback keywords',
            'content' => 'Existing body',
            'title' => 'Fallback page name',
            'includeCurrentTitle' => true,
        ])
        ->and(seoSuiteAdminFillFormData($descriptionAction, $get))->toMatchArray([
            'keywords' => 'site fallback keywords',
            'content' => 'Existing body',
            'currentDescription' => 'Current search description',
            'includeCurrentDescription' => true,
        ]);
});

it('halts seo ai admin actions with a visible failure when upstream generation fails', function (): void {
    $translation = new Translation;
    $translation->forceFill([
        'title' => 'Current title',
        'language_id' => 7,
        'translatable_id' => 42,
        'translatable_type' => 'core_page',
        'meta' => ['description' => 'Current description'],
    ]);

    $generator = Mockery::mock(GeneratorPageContentAction::class);
    $generator->shouldReceive('handle')
        ->once()
        ->andThrow(new RuntimeException('Generation failed'));
    app()->instance(GeneratorPageContentAction::class, $generator);

    $contentAction = (new ReflectionMethod(PageContentEditorConfigurator::class, 'generateContentAction'))
        ->invoke(new PageContentEditorConfigurator);
    $contentState = [];

    expect(fn (): mixed => evaluateSeoSuiteAdminAction($contentAction, [
        'set' => seoSuiteAdminFakeSet($contentState),
        'component' => new stdClass,
        'record' => $translation,
        'data' => [
            'keywords' => 'coverage',
            'content' => 'Existing body',
            'includeCurrentContent' => true,
            'title' => 'Current title',
        ],
    ], [
        Set::class => seoSuiteAdminFakeSet($contentState),
        Translation::class => $translation,
    ]))->toThrow(Halt::class);

    $titleGenerator = Mockery::mock(SuggestPageTitlesAction::class);
    $titleGenerator->shouldReceive('run')
        ->once()
        ->andThrow(new RuntimeException('Title generation failed'));

    $mountedTitleAction = [];
    $titleAction = (new ReflectionMethod(PageTitleWithSlugInputExtender::class, 'titleSuggestionsAction'))
        ->invoke(new PageTitleWithSlugInputExtender($titleGenerator));
    $titleComponent = FusedGroup::make([]);

    expect(fn (): mixed => evaluateSeoSuiteAdminAction($titleAction, [
        'livewire' => seoSuiteAdminHasActions($mountedTitleAction),
        'component' => $titleComponent,
        'record' => $translation,
        'data' => [
            'keywords' => 'coverage',
            'content' => 'Existing body',
            'includeCurrentTitle' => true,
            'title' => 'Current title',
        ],
    ], [
        HasActions::class => seoSuiteAdminHasActions($mountedTitleAction),
        FusedGroup::class => $titleComponent,
        Translation::class => $translation,
    ]))->toThrow(Halt::class);

    $metaGenerator = Mockery::mock(SuggestMetaDescriptionsAction::class);
    $metaGenerator->shouldReceive('run')
        ->once()
        ->andThrow(new RuntimeException('Meta generation failed'));

    $mountedMetaAction = [];
    $metaSection = Section::make('Search metadata');
    $metaAction = (new ReflectionMethod(SearchMetaDataSectionExtender::class, 'metaDescriptionSuggestionsAction'))
        ->invoke(new SearchMetaDataSectionExtender($metaGenerator));

    expect(fn (): mixed => evaluateSeoSuiteAdminAction($metaAction, [
        'livewire' => seoSuiteAdminHasActions($mountedMetaAction),
        'component' => $metaSection,
        'record' => $translation,
        'data' => [
            'keywords' => 'coverage',
            'content' => 'Existing body',
            'includeCurrentDescription' => true,
            'currentDescription' => 'Current description',
        ],
    ], [
        HasActions::class => seoSuiteAdminHasActions($mountedMetaAction),
        Section::class => $metaSection,
        Translation::class => $translation,
    ]))->toThrow(Halt::class);
});

/**
 * @param  array<string, mixed>  $state
 */
function seoSuiteAdminFakeSet(array &$state): Set
{
    return new class($state) extends Set
    {
        /**
         * @param  array<string, mixed>  $state
         */
        public function __construct(private array &$state) {}

        public function __invoke(
            string|Component $path,
            mixed $state,
            bool $isAbsolute = false,
            bool $shouldCallUpdatedHooks = true,
        ): mixed {
            unset($isAbsolute, $shouldCallUpdatedHooks);

            if ($path instanceof Component) {
                $path = $path->getName();
            }

            data_set($this->state, $path, $state);

            return null;
        }
    };
}

/**
 * @param  array<string, mixed>  $state
 */
function seoSuiteAdminFakeGet(array $state): Get
{
    return new class($state) extends Get
    {
        /**
         * @param  array<string, mixed>  $state
         */
        public function __construct(private readonly array $state) {}

        public function __invoke(string|Component $path = '', bool $isAbsolute = false): mixed
        {
            unset($isAbsolute);

            $key = $path instanceof Component ? $path->getName() : $path;

            return array_key_exists($key, $this->state)
                ? $this->state[$key]
                : data_get($this->state, $key);
        }
    };
}

/**
 * @return array<array-key, mixed>
 */
function seoSuiteAdminFillFormData(Action $action, Get $get): array
{
    $mountUsing = $action->getMountUsing();
    $variables = (new ReflectionFunction($mountUsing))->getStaticVariables();
    $data = $variables['data'] ?? null;

    throw_unless($data instanceof Closure, RuntimeException::class, 'Expected action fill form callback.');

    $filled = $action->evaluate($data, ['get' => $get], [Get::class => $get]);

    return is_array($filled) ? $filled : [];
}

/**
 * @param  array{name?: string, arguments?: array<string, mixed>}  $mountedAction
 */
function seoSuiteAdminHasActions(array &$mountedAction): HasActions
{
    $livewire = Mockery::mock(HasActions::class);
    $livewire->shouldReceive('mountAction')
        ->andReturnUsing(function (string $name, array $arguments = []) use (&$mountedAction): null {
            $mountedAction = [
                'name' => $name,
                'arguments' => $arguments,
            ];

            return null;
        });
    $livewire->shouldIgnoreMissing();

    return $livewire;
}

/**
 * @param  array<string, mixed>  $namedInjections
 * @param  array<class-string, mixed>  $typedInjections
 */
function evaluateSeoSuiteAdminAction(Action $action, array $namedInjections, array $typedInjections = []): void
{
    $closure = $action->getActionFunction();

    expect($closure)->not->toBeNull();

    $action->evaluate(
        $closure,
        ['action' => $action, ...$namedInjections],
        [Action::class => $action, ...$typedInjections],
    );
}
