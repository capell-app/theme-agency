<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\UserFactory;
use Capell\SeoSuite\Actions\GenerateAiLayoutAction;
use Capell\SeoSuite\Actions\SubmitAiCreatorDraftAction;
use Capell\SeoSuite\Data\AiContentBriefData;
use Capell\SeoSuite\DataObjects\AiCreatorData;
use Capell\SeoSuite\Filament\Actions\AiContentBriefAction;
use Capell\SeoSuite\Filament\Actions\AiCreatorAction;
use Capell\SeoSuite\Filament\Actions\AiImageGeneratorAction;
use Capell\SeoSuite\Models\AiCreatorContext;
use Capell\SeoSuite\Models\AiCreatorSession;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;

/**
 * @param  array<array-key, mixed>  $state
 */
function seoSuiteFakeGet(array $state): Get
{
    return new class($state) extends Get
    {
        /**
         * @param  array<array-key, mixed>  $state
         */
        public function __construct(private readonly array $state) {}

        public function __invoke(string|Component $path = '', bool $isAbsolute = false): mixed
        {
            if ($path instanceof Component) {
                $path = $path->getName();
            }

            return data_get($this->state, $path);
        }
    };
}

/**
 * @param  array<string, mixed>  $state
 */
function seoSuiteFakeSet(array &$state): Set
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

            $updatedState = $this->state;
            data_set($updatedState, $path, $state);

            if (is_array($updatedState)) {
                $this->state = seoSuiteFilamentStringKeyedState($updatedState);
            }

            return null;
        }
    };
}

/**
 * @param  array<array-key, mixed>  $state
 * @return array<string, mixed>
 */
function seoSuiteFilamentStringKeyedState(array $state): array
{
    $normalizedState = [];

    foreach ($state as $key => $value) {
        $normalizedState[(string) $key] = $value;
    }

    return $normalizedState;
}

/**
 * @param  array<array-key, mixed>  $state
 * @return array<int, mixed>
 */
function seoSuiteRawActionSchema(Action $action, array $state = []): array
{
    $property = new ReflectionProperty($action, 'schema');
    $schema = $property->getValue($action);

    if ($schema instanceof Closure) {
        return $schema(seoSuiteFakeGet($state));
    }

    return is_array($schema) ? $schema : [];
}

it('builds the ai image generator action schema and target field name', function (): void {
    $action = AiImageGeneratorAction::make('hero_image', [
        'title' => 'Title',
        'summary' => 'Summary',
    ]);

    $components = seoSuiteRawActionSchema($action, [
        'title' => 'Landing page',
        'summary' => 'A concise product page.',
    ]);

    expect($action->getName())->toBe('hero_image')
        ->and($components)->toHaveCount(3)
        ->and($components[0])->toBeInstanceOf(Textarea::class)
        ->and($components[0]->getName())->toBe('prompt')
        ->and($components[1])->toBeInstanceOf(Actions::class)
        ->and($components[2])->toBeInstanceOf(ViewField::class)
        ->and($components[2]->getName())->toBe('preview_url');
});

it('builds ai content brief result placeholders and list markup', function (): void {
    $action = AiContentBriefAction::make();
    $method = new ReflectionMethod(AiContentBriefAction::class, 'resultsSchema');
    $schema = $method->invoke($action, new AiContentBriefData(
        contentAngle: 'Compare implementation tradeoffs.',
        missingTopics: ['Performance budget', ['owner' => 'Editor', 'required' => true]],
        suggestedHeadings: ['Implementation plan'],
        faqIdeas: [],
        schemaOpportunities: ['FAQPage'],
        internalLinks: ['Docs'],
        metaTitleAlternatives: ['Coverage plan'],
        metaDescriptionAlternatives: ['A concise summary.'],
    ));

    expect(AiContentBriefAction::getDefaultName())->toBe('ai_content_brief')
        ->and($schema)->toHaveCount(8)
        ->and($schema[0])->toBeInstanceOf(TextEntry::class)
        ->and($schema[0]->getName())->toBe('content_angle')
        ->and($schema[1]->getName())->toBe('missing_topics');

    $listHtml = new ReflectionMethod(AiContentBriefAction::class, 'listHtml');

    expect((string) $listHtml->invoke($action, []))->toContain('text-gray-500')
        ->and((string) $listHtml->invoke($action, [['enabled' => true, 'count' => 2]]))
        ->toContain('Enabled: true | Count: 2');
});

it('builds ai content brief modal actions and readonly input schema', function (): void {
    $action = AiContentBriefAction::make();

    $components = seoSuiteRawActionSchema($action, ['language_id' => 5]);

    expect(collect($components)->map(fn (mixed $component): string => $component->getName())->all())->toBe([
        'language_id',
        'readonly_notice',
    ]);

    $resultsActionMethod = new ReflectionMethod(AiContentBriefAction::class, 'resultsAction');
    $resultsAction = $resultsActionMethod->invoke($action);

    expect($resultsAction->getName())->toBe('ai_content_brief_results');
});

it('builds the ai creator wizard and resolves site ids from supported record shapes', function (): void {
    $action = AiCreatorAction::make();
    $buildWizardForm = new ReflectionMethod(AiCreatorAction::class, 'buildWizardForm');
    $resolveSiteId = new ReflectionMethod(AiCreatorAction::class, 'resolveSiteId');
    $isMountedOnSiteResource = new ReflectionMethod(AiCreatorAction::class, 'isMountedOnSiteResource');

    $schema = $buildWizardForm->invoke($action);

    expect($schema)->toHaveCount(2)
        ->and($schema[1])->toBeInstanceOf(Wizard::class)
        ->and($resolveSiteId->invoke($action))->toBeNull()
        ->and($isMountedOnSiteResource->invoke($action))->toBeFalse();
});

it('builds ai creator brand step defaults without existing site context', function (): void {
    $action = AiCreatorAction::make();
    $method = new ReflectionMethod(AiCreatorAction::class, 'buildBrandStep');
    $schema = $method->invoke($action);

    expect($schema)->toHaveCount(4)
        ->and($schema[0]->getName())->toBe('tone')
        ->and($schema[1]->getName())->toBe('industry')
        ->and($schema[2]->getName())->toBe('target_audience')
        ->and($schema[3]->getName())->toBe('brand_voice_notes');
});

it('generates ai creator layout preview state from brand inputs', function (): void {
    $user = UserFactory::new()->create();
    auth()->login($user);

    $generator = Mockery::mock(GenerateAiLayoutAction::class);
    $generator->shouldReceive('handle')
        ->once()
        ->withArgs(function (AiCreatorData $data) use ($user): bool {
            AiCreatorSession::query()->create([
                'site_id' => $data->siteId,
                'user_id' => $user->getKey(),
                'status' => 'review',
                'stage' => 2,
                'intent' => $data->intent,
                'layout_proposal' => [],
                'generated_output' => [],
                'ai_messages' => [],
            ]);

            return $data->siteId === 0
                && $data->userId === $user->getKey()
                && $data->intent === 'Build a service landing page.'
                && $data->pageCount === 2;
        })
        ->andReturn([
            [
                'section_type' => 'hero',
                'fields' => ['headline' => 'Launch faster'],
            ],
        ]);
    app()->instance(GenerateAiLayoutAction::class, $generator);

    $action = AiCreatorAction::make();
    $state = [];
    $generateLayout = new ReflectionMethod(AiCreatorAction::class, 'generateLayout');

    $generateLayout->invoke($action, seoSuiteFakeGet([
        'intent' => 'Build a service landing page.',
        'page_count' => 2,
        'tone' => 'friendly',
        'industry' => 'consulting',
        'target_audience' => 'Founders',
        'brand_voice_notes' => 'Concise and practical.',
    ]), seoSuiteFakeSet($state));

    $context = AiCreatorContext::query()->firstOrFail();

    expect($context->site_id)->toBe(0)
        ->and($context->tone)->toBe('friendly')
        ->and($state['ai_session_id'])->toBeInt()
        ->and($state['layout_preview'][0]['section_type'])->toBe('hero')
        ->and($state['layout_preview'][0]['fields_preview'])->toContain('Launch faster');
});

it('submits ai creator review sessions for the authenticated user', function (): void {
    $user = UserFactory::new()->create();
    auth()->login($user);

    $session = AiCreatorSession::query()->create([
        'site_id' => 0,
        'user_id' => $user->getKey(),
        'status' => 'review',
        'stage' => 3,
        'intent' => 'Submit this draft.',
        'layout_proposal' => [],
        'generated_output' => [],
        'ai_messages' => [],
    ]);

    $submit = Mockery::mock(SubmitAiCreatorDraftAction::class);
    $submit->shouldReceive('handle')
        ->once()
        ->withArgs(fn (AiCreatorSession $handledSession, int $userId, ?int $siteId): bool => $handledSession->is($session)
            && $userId === $user->getKey()
            && $siteId === null);
    app()->instance(SubmitAiCreatorDraftAction::class, $submit);

    $action = AiCreatorAction::make();
    $runCreator = new ReflectionMethod(AiCreatorAction::class, 'runCreator');

    $runCreator->invoke($action, ['ai_session_id' => $session->getKey()]);
});
