<?php

declare(strict_types=1);

use Capell\Admin\Enums\PageTranslationSchemaHookEnum;
use Capell\Core\Models\Page;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Filament\Extenders\Site\SiteTranslationMetaExtender;
use Capell\SeoSuite\Handlers\ClearCircuitBreakerHandler;
use Capell\SeoSuite\Policies\AiCreatorPolicy;
use Capell\SeoSuite\Settings\AIOrchestratorSettings;
use Capell\SeoSuite\Support\AiFeatureRegistry;
use Capell\SeoSuite\Support\Context\ContentActionContext;
use Capell\SeoSuite\Support\Context\TranslationActionContext;
use Capell\SeoSuite\Support\PrismProvider;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

it('exposes scalar content action context values with sensible fallbacks', function (): void {
    $context = new ContentActionContext(
        content: '<p>Search content</p>',
        keywords: 'cms, seo',
        pageId: 'page-uuid',
        pageType: Page::class,
        languageId: 7,
    );
    $emptyContext = new ContentActionContext(content: 'Only content');

    expect($context->getContent())->toBe('<p>Search content</p>')
        ->and($context->getKeywords())->toBe('cms, seo')
        ->and($context->getPageId())->toBe('page-uuid')
        ->and($context->getPageType())->toBe(Page::class)
        ->and($context->getLanguageId())->toBe(7)
        ->and($emptyContext->getKeywords())->toBe('')
        ->and($emptyContext->getPageId())->toBe(0)
        ->and($emptyContext->getPageType())->toBe('')
        ->and($emptyContext->getLanguageId())->toBe(0);
});

it('reads ai context data from legacy translations', function (): void {
    $translation = new Translation;
    $translation->forceFill([
        'content' => '<p>Legacy body</p>',
        'meta' => ['keywords' => 'legacy keywords'],
        'translatable_id' => 42,
        'translatable_type' => Page::class,
        'language_id' => 3,
    ]);

    $context = new TranslationActionContext($translation);

    expect($context->getContent())->toBe('<p>Legacy body</p>')
        ->and($context->getKeywords())->toBe('legacy keywords')
        ->and($context->getPageId())->toBe(42)
        ->and($context->getPageType())->toBe(Page::class)
        ->and($context->getLanguageId())->toBe(3)
        ->and($context->getTranslation())->toBe($translation);
});

it('registers configured ai features and filters enabled features', function (): void {
    $registry = new AiFeatureRegistry([
        'content' => [
            'enabled' => true,
            'handler' => 'content-handler',
            'label' => 'Original label',
        ],
        'titles' => [
            'enabled' => false,
            'handler' => 'title-handler',
        ],
        'ignored' => [
            'enabled' => true,
        ],
    ]);

    $registry->register('content', [
        'enabled' => false,
        'label' => 'Runtime label',
    ]);
    $registry->register('images', [
        'enabled' => true,
        'handler' => 'image-handler',
    ]);

    expect($registry->get('content'))->toMatchArray([
        'name' => 'content',
        'enabled' => true,
        'handler' => 'content-handler',
        'label' => 'Original label',
    ])
        ->and($registry->get('missing'))->toBeNull()
        ->and(array_keys($registry->all()))->toBe(['content', 'titles', 'images'])
        ->and(array_keys($registry->all(enabledOnly: true)))->toBe(['content', 'images'])
        ->and($registry->is('content'))->toBeTrue()
        ->and($registry->is('titles'))->toBeFalse()
        ->and($registry->getHandler('images'))->toBe('image-handler')
        ->and($registry->getHandler('missing'))->toBeNull()
        ->and($registry->getConfig('missing'))->toBe([]);
});

it('honours site ai creator overrides before global policy settings', function (): void {
    $settings = (new ReflectionClass(AIOrchestratorSettings::class))->newInstanceWithoutConstructor();
    $settings->ai_creator = true;

    $policy = new AiCreatorPolicy($settings);

    expect($policy->isEnabledFor((object) []))->toBeTrue()
        ->and($policy->isEnabledFor((object) ['ai_creator_enabled' => false]))->toBeFalse()
        ->and($policy->isEnabledFor((object) ['ai_creator_enabled' => true]))->toBeTrue();

    $settings->ai_creator = false;

    expect($policy->isEnabledFor((object) []))->toBeFalse();
});

it('returns schema components only for matching seo suite extension hooks', function (): void {
    $siteTranslationExtender = resolve(SiteTranslationMetaExtender::class);

    $siteComponents = $siteTranslationExtender->extendTranslationComponentsForHook(
        Schema::make(),
        PageTranslationSchemaHookEnum::AfterTitle,
    );

    expect($siteComponents)->not->toBe([])
        ->and($siteComponents[0])->toBeInstanceOf(Group::class)
        ->and($siteTranslationExtender->extendTranslationComponentsForHook(Schema::make(), PageTranslationSchemaHookEnum::BeforeSearchMeta))->toBe([]);
});

it('clears the ai provider circuit breaker from the admin handler', function (): void {
    Cache::put('ai_circuit_breaker_state:openai', ['failures' => 5], 300);

    $component = new class extends Component
    {
        public function render(): string
        {
            return '';
        }
    };

    expect(resolve(PrismProvider::class)->isAvailable())->toBeFalse();

    resolve(ClearCircuitBreakerHandler::class)->handle([], $component);

    expect(resolve(PrismProvider::class)->isAvailable())->toBeTrue();
});
