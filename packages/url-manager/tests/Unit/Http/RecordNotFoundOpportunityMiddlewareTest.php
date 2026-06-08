<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\UrlManager\Actions\UpsertRedirectRuleAction;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Http\Middleware\RecordNotFoundOpportunityMiddleware;
use Capell\UrlManager\Http\Middleware\ServeGoneRedirectRuleMiddleware;
use Capell\UrlManager\Models\NotFoundOpportunity;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

it('records frontend 404 responses as not-found opportunities', function (): void {
    config([
        'capell-url-manager.not_found.capture_middleware_enabled' => true,
        'capell-url-manager.not_found.ignored_path_prefixes' => ['/admin'],
    ]);

    $request = Request::create('/missing-page?utm_source=test');
    $request->attributes->set('site', tap(new Site, fn (Site $site): Site => $site->forceFill(['id' => 1])));
    $request->attributes->set('language', tap(new Language, fn (Language $language): Language => $language->forceFill(['id' => 2])));

    $response = (new RecordNotFoundOpportunityMiddleware)->handle(
        $request,
        static fn (): Response => new Response('', Response::HTTP_NOT_FOUND),
    );

    $opportunity = NotFoundOpportunity::query()->first();

    expect($response->getStatusCode())->toBe(404)
        ->and($opportunity?->source_url)->toBe('/missing-page')
        ->and($opportunity?->site_id)->toBe(1)
        ->and($opportunity?->language_id)->toBe(2)
        ->and($opportunity?->context['source'] ?? null)->toBe('frontend_404_middleware');
});

it('does not record ignored 404 paths', function (): void {
    config([
        'capell-url-manager.not_found.capture_middleware_enabled' => true,
        'capell-url-manager.not_found.ignored_path_prefixes' => ['/admin'],
    ]);

    (new RecordNotFoundOpportunityMiddleware)->handle(
        Request::create('/admin/missing'),
        static fn (): Response => new Response('', Response::HTTP_NOT_FOUND),
    );

    expect(NotFoundOpportunity::query()->count())->toBe(0);
});

it('serves managed 410 gone rules before the frontend 404 response is recorded', function (): void {
    config(['capell-url-manager.hit_recording.defer' => false]);

    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/retired-page',
        targetUrl: '',
        siteId: 1,
        languageId: 2,
        statusCode: Response::HTTP_GONE,
    ));

    $request = Request::create('/retired-page?utm_source=test');
    $request->attributes->set('site', tap(new Site, fn (Site $site): Site => $site->forceFill(['id' => 1])));
    $request->attributes->set('language', tap(new Language, fn (Language $language): Language => $language->forceFill(['id' => 2])));

    $response = (new ServeGoneRedirectRuleMiddleware)->handle(
        $request,
        static fn (): Response => new Response('', Response::HTTP_NOT_FOUND),
    );

    expect($response->getStatusCode())->toBe(Response::HTTP_GONE)
        ->and(NotFoundOpportunity::query()->count())->toBe(0);
});
