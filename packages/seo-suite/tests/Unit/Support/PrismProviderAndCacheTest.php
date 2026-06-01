<?php

declare(strict_types=1);

use Capell\SeoSuite\Exceptions\OpenAICircuitBreakerOpenException;
use Capell\SeoSuite\Support\Cache\AIGenerationCache;
use Capell\SeoSuite\Support\Cache\RateLimitCache;
use Capell\SeoSuite\Support\PrismProvider;
use Illuminate\Support\Facades\Cache;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\Request as PrismTextRequest;
use Prism\Prism\Text\Response as PrismTextResponse;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;

it('stores ai generation values behind the configured cache driver and ttl', function (): void {
    Cache::flush();

    $cache = new AIGenerationCache('array', 120);
    $calls = 0;

    $remembered = $cache->remember('brief:1', function () use (&$calls): string {
        $calls++;

        return 'generated brief';
    });
    $secondRemembered = $cache->remember('brief:1', function () use (&$calls): string {
        $calls++;

        return 'new brief';
    });

    $cache->put('brief:2', 'stored brief', 60);

    expect($remembered)->toBe('generated brief')
        ->and($secondRemembered)->toBe('generated brief')
        ->and($calls)->toBe(1)
        ->and($cache->get('brief:2'))->toBe('stored brief')
        ->and($cache->get('missing', 'fallback'))->toBe('fallback')
        ->and($cache->keyFor('page', 42))->toBe('page:42')
        ->and($cache->ttl())->toBe(120);
});

it('stores rate limit state behind the configured cache driver', function (): void {
    Cache::flush();

    $cache = new RateLimitCache('array');
    $key = $cache->keyFor('user-7');

    $cache->put($key, ['attempts' => 2], 30);

    expect($key)->toBe('ai_rate_limit_user-7')
        ->and($cache->get($key))->toBe(['attempts' => 2])
        ->and($cache->ttl())->toBe(60);

    $cache->forget($key);

    expect($cache->get($key, 'empty'))->toBe('empty');
});

it('maps provider aliases and exposes service metadata', function (): void {
    $provider = new PrismProvider(['max_retries' => 1, 'retry_delay_ms' => 0]);

    expect($provider->handles())->toBe('prism_provider')
        ->and($provider->isAvailable())->toBeTrue();
});

it('sends normalized chat messages through prism and maps the response telemetry', function (): void {
    Cache::flush();
    app()->instance('prism', new \Prism\Prism\Prism);

    $fake = Prism::fake([
        new PrismTextResponse(
            steps: collect(),
            text: 'Generated title',
            finishReason: FinishReason::Stop,
            toolCalls: [],
            toolResults: [],
            usage: new Usage(promptTokens: 11, completionTokens: 7),
            meta: new Meta(id: 'fake-response', model: 'gpt-test'),
            messages: collect(),
        ),
    ]);

    $provider = new PrismProvider([
        'provider' => 'anthropic',
        'model' => 'claude-test',
        'max_retries' => 1,
        'retry_delay_ms' => 0,
    ]);

    $response = $provider->chat([
        'messages' => [
            ['role' => 'system', 'content' => 'Use the brand voice.'],
            ['role' => 'user', 'content' => 'Draft a page title.'],
            ['role' => 'user', 'content' => 'Keep it short.'],
        ],
        'max_tokens' => 128,
        'temperature' => 0.2,
    ]);

    $fake->assertCallCount(1);
    $fake->assertRequest(function (array $requests): void {
        $request = $requests[0] ?? null;

        expect($request)->toBeInstanceOf(PrismTextRequest::class)
            ->and($request->model())->toBe('claude-test')
            ->and(collect($request->systemPrompts())->pluck('content')->all())->toBe(['Use the brand voice.'])
            ->and($request->prompt())->toBe("Draft a page title.\n\nKeep it short.")
            ->and($request->maxTokens())->toBe(128)
            ->and($request->temperature())->toBe(0.2);
    });

    expect($response->content)->toBe('Generated title')
        ->and($response->tokensUsed)->toBe(18)
        ->and($response->model)->toBe('claude-test')
        ->and($response->metadata)->toMatchArray([
            'prompt_tokens' => 11,
            'completion_tokens' => 7,
        ])
        ->and($provider->isAvailable())->toBeTrue();
});

it('resolves configured prism provider names to prism enums', function (string $name, Provider $expected): void {
    $provider = new PrismProvider;
    $resolveProvider = new ReflectionMethod(PrismProvider::class, 'resolveProvider');

    expect($resolveProvider->invoke($provider, $name))->toBe($expected);
})->with([
    'anthropic' => ['anthropic', Provider::Anthropic],
    'gemini' => ['gemini', Provider::Gemini],
    'google' => ['google', Provider::Gemini],
    'ollama' => ['ollama', Provider::Ollama],
    'openai fallback' => ['unknown', Provider::OpenAI],
]);

it('opens and resets the prism circuit breaker after repeated failures', function (): void {
    Cache::flush();

    $provider = new PrismProvider;
    $recordFailure = new ReflectionMethod(PrismProvider::class, 'recordFailure');

    expect($provider->isAvailable())->toBeTrue();

    for ($failure = 1; $failure <= 5; $failure++) {
        $recordFailure->invoke($provider);
    }

    expect($provider->isAvailable())->toBeFalse()
        ->and(fn (): mixed => $provider->chat(['messages' => []]))->toThrow(OpenAICircuitBreakerOpenException::class);

    $provider->resetCircuitBreaker();

    expect($provider->isAvailable())->toBeTrue();
});
