<?php

declare(strict_types=1);

use Capell\SeoSuite\Data\AiContentBriefData;
use Capell\SeoSuite\Filament\Actions\AiContentBriefAction;
use Capell\SeoSuite\Filament\Actions\AiCreatorAction;
use Capell\SeoSuite\Filament\Actions\AiImageGeneratorAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
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
            return data_get($this->state, $path);
        }
    };
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
        ->and($schema[0])->toBeInstanceOf(Placeholder::class)
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
