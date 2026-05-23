<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\LanguageFactory;
use Capell\Core\Database\Factories\PageFactory;
use Capell\Core\Database\Factories\SiteFactory;
use Capell\SeoSuite\Actions\GenerateAiContentBriefAction;
use Capell\SeoSuite\Data\AiContentBriefData;
use Capell\SeoSuite\Models\AIGenerationHistory;
use Capell\SeoSuite\Support\AiResponseParser;
use Capell\SeoSuite\Support\PromptRepository;
use Capell\SeoSuite\Tests\Fixtures\ContentBriefPrismProviderFake;

/**
 * @return array{0: GenerateAiContentBriefAction, 1: ContentBriefPrismProviderFake}
 */
function makeAiContentBriefActionForJson(string $json): array
{
    $provider = new ContentBriefPrismProviderFake($json);

    return [
        new GenerateAiContentBriefAction(
            new PromptRepository([
                'ai_content_brief' => [
                    'system' => 'Return JSON only.',
                    'user_template' => 'Page: {{page}} Report: {{report}} Site: {{site}} Language: {{language}}',
                    'model' => 'test-model',
                ],
            ]),
            $provider,
            new AiResponseParser,
        ),
        $provider,
    ];
}

it('parses an AI content brief JSON response into all fields without a live AI call', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Website design services',
            'content' => '<p>We build practical CMS websites for growing businesses.</p>',
            'meta' => [
                'title' => 'Website design',
                'description' => 'Short description.',
                'keywords' => 'cms websites, laravel cms',
            ],
        ])
        ->create();

    $json = json_encode([
        'contentAngle' => 'Position the page around scalable CMS delivery for growing teams.',
        'missingTopics' => ['Migration process', 'Support packages'],
        'suggestedHeadings' => ['CMS strategy', 'Implementation process'],
        'faqIdeas' => ['How long does a CMS migration take?'],
        'schemaOpportunities' => ['FAQPage', 'Service'],
        'internalLinks' => ['Link to CMS case studies'],
        'metaTitleAlternatives' => ['Scalable CMS Website Design'],
        'metaDescriptionAlternatives' => ['Plan and launch a scalable CMS website with a Laravel specialist.'],
    ], JSON_THROW_ON_ERROR);

    [$action, $provider] = makeAiContentBriefActionForJson($json);

    $brief = $action->handle($page, $site, $language);

    expect($brief)->toBeInstanceOf(AiContentBriefData::class)
        ->and($brief->contentAngle)->toBe('Position the page around scalable CMS delivery for growing teams.')
        ->and($brief->missingTopics)->toBe(['Migration process', 'Support packages'])
        ->and($brief->suggestedHeadings)->toBe(['CMS strategy', 'Implementation process'])
        ->and($brief->faqIdeas)->toBe(['How long does a CMS migration take?'])
        ->and($brief->schemaOpportunities)->toBe(['FAQPage', 'Service'])
        ->and($brief->internalLinks)->toBe(['Link to CMS case studies'])
        ->and($brief->metaTitleAlternatives)->toBe(['Scalable CMS Website Design'])
        ->and($brief->metaDescriptionAlternatives)->toBe(['Plan and launch a scalable CMS website with a Laravel specialist.'])
        ->and($provider->params['model'])->toBe('test-model')
        ->and($provider->params['messages'][1]['content'])->toContain('"score"')
        ->and($provider->params['messages'][1]['content'])->toContain('"search_preview"')
        ->and($provider->params['messages'][1]['content'])->toContain('"issues"');

    $history = AIGenerationHistory::query()->where('action', 'GenerateAiContentBriefAction')->first();

    expect($history)->not->toBeNull()
        ->and($history?->model)->toBe('test-model')
        ->and($history?->total_tokens)->toBe(12)
        ->and($history?->language_id)->toBe($language->id);
});
