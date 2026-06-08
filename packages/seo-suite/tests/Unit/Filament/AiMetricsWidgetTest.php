<?php

declare(strict_types=1);

use Capell\SeoSuite\Data\Dashboard\AiMetricsData;
use Capell\SeoSuite\Data\Dashboard\FeatureUsageData;
use Capell\SeoSuite\Filament\Widgets\AiMetricsWidgetAbstract;
use Capell\SeoSuite\Models\AIGenerationHistory;
use Capell\SeoSuite\Settings\AIOrchestratorSettings;
use Capell\SeoSuite\Support\AiRateLimiter;
use Capell\SeoSuite\Support\Cache\RateLimitCache;

it('builds ai metrics widget data from history rows, settings, and rate limits', function (): void {
    require_once dirname(__DIR__, 3) . '/src/Data/Dashboard/AiMetricsData.php';

    test()->registerAndMigrateSettings(
        [
            '2026_05_10_190871_01_create_ai-orchestrator_settings',
        ],
        dirname(__DIR__, 3) . '/database/settings',
    );

    $settings = resolve(AIOrchestratorSettings::class);
    $settings->ai_provider = 'openai';
    $settings->ai_model = 'gpt-4o-mini';
    $settings->page_content_generator = true;
    $settings->page_title_suggestions = false;
    $settings->ai_creator = true;
    $settings->save();

    config()->set('ai-orchestrator.rate_limit.window_seconds', 90);

    app()->instance(AiRateLimiter::class, new AiRateLimiter(
        new RateLimitCache('array'),
        ['enabled' => true, 'requests_per_minute' => 10, 'window_seconds' => 90],
    ));

    AIGenerationHistory::query()->create([
        'action' => 'GeneratePageTitleAction',
        'model' => 'gpt-4o-mini',
        'input' => 'First input',
        'output' => 'First output',
        'prompt_tokens' => 10,
        'completion_tokens' => 20,
        'total_tokens' => 30,
        'cost_micros' => 2000,
        'cost_currency' => 'USD',
        'duration' => 0.5,
        'failed' => false,
    ]);
    AIGenerationHistory::query()->create([
        'action' => 'GeneratePageTitleAction',
        'model' => 'gpt-4o-mini',
        'input' => 'Second input',
        'output' => null,
        'prompt_tokens' => 20,
        'completion_tokens' => 10,
        'total_tokens' => 30,
        'cost_micros' => 3000,
        'cost_currency' => 'USD',
        'duration' => 0.7,
        'failed' => true,
        'error_message' => 'Provider failed',
    ]);
    AIGenerationHistory::query()->create([
        'action' => 'SuggestMetaDescriptionsAction',
        'model' => 'gpt-4o-mini',
        'input' => 'Third input',
        'output' => 'Third output',
        'prompt_tokens' => 7,
        'completion_tokens' => 8,
        'total_tokens' => 15,
        'cost_micros' => 1000,
        'cost_currency' => 'USD',
        'duration' => 0.2,
        'failed' => false,
    ]);

    $method = new ReflectionMethod(AiMetricsWidgetAbstract::class, 'getViewData');
    $viewData = $method->invoke(new AiMetricsWidgetAbstract);

    expect($viewData)->toHaveKey('data')
        ->and($viewData['data'])->toBeInstanceOf(AiMetricsData::class);

    /** @var AiMetricsData $data */
    $data = $viewData['data'];

    expect($data->totalGenerations)->toBe(3)
        ->and($data->totalTokens)->toBe(75)
        ->and($data->totalCostMicros)->toBe(6000)
        ->and($data->currency)->toBe('USD')
        ->and($data->failedGenerations)->toBe(1)
        ->and($data->remainingRequests)->toBe(10)
        ->and($data->windowLimitSeconds)->toBe(90)
        ->and($data->aiProvider)->toBe('openai')
        ->and($data->aiModel)->toBe('gpt-4o-mini')
        ->and($data->pageContentGeneratorEnabled)->toBeTrue()
        ->and($data->pageTitleSuggestionsEnabled)->toBeFalse()
        ->and($data->aiCreatorEnabled)->toBeTrue()
        ->and($data->featureUsage)->toHaveCount(2);

    $topFeature = $data->featureUsage->first();

    throw_unless($topFeature instanceof FeatureUsageData, RuntimeException::class, 'Expected top feature usage data.');

    expect($topFeature->feature)->toBe('GeneratePageTitleAction')
        ->and($topFeature->count)->toBe(2)
        ->and($topFeature->tokens)->toBe(60)
        ->and($topFeature->costMicros)->toBe(5000)
        ->and($topFeature->averageTokensPerRequest)->toBe(30.0);
});
