<?php

declare(strict_types=1);

use Capell\AgentDelivery\Providers\AgentDeliveryServiceProvider;
use Capell\AgentDelivery\Tests\AgentDeliveryTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

uses(AgentDeliveryTestCase::class);

it('registers the agent delivery package metadata', function (): void {
    expect(AgentDeliveryServiceProvider::$name)->toBe('capell-agent-delivery')
        ->and(AgentDeliveryServiceProvider::$packageName)->toBe('capell-app/agent-delivery');
});

it('loads host-configurable public middleware defaults', function (): void {
    expect(config('capell-agent-delivery.middleware'))->toBe(['api'])
        ->and(config('capell-agent-delivery.public_pages.auth_middleware'))->toBeNull()
        ->and(config('capell-agent-delivery.public_pages.rate_limit_middleware'))->toBe('throttle:capell-agent-delivery')
        ->and(config('capell-agent-delivery.public_pages.rate_limit_per_minute'))->toBe(60)
        ->and(config('capell-agent-delivery.public_pages.max_candidate_sites'))->toBe(50)
        ->and(config('capell-agent-delivery.public_pages.middleware'))->toBe([]);
});

it('registers the documented rate limiter', function (): void {
    $limiter = RateLimiter::limiter('capell-agent-delivery');

    throw_unless($limiter instanceof Closure, RuntimeException::class, 'Expected agent delivery rate limiter to be registered.');

    $limits = $limiter(Request::create('/api/capell/agent/v1/pages/manifest'));

    expect($limits->maxAttempts)->toBe(60);
});

it('applies the documented rate limiter to public endpoints by default', function (): void {
    $route = Route::getRoutes()->getByName('capell-agent-delivery.pages.manifest');

    expect($route)->not->toBeNull()
        ->and($route?->gatherMiddleware())->toContain('throttle:capell-agent-delivery');
});
