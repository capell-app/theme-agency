<?php

declare(strict_types=1);

use Capell\HtmlCache\Http\Middleware\HtmlCacheMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

it('real HtmlCacheMiddleware returns private no-store when access_gate.protected attribute is set', function (): void {
    if (! class_exists(HtmlCacheMiddleware::class)) {
        $this->markTestSkipped('HtmlCacheMiddleware is not available in this environment.');
    }

    $middleware = resolve(HtmlCacheMiddleware::class);

    $request = Request::create('/gated-page', 'GET');
    $request->attributes->set('access_gate.protected', true);

    $response = $middleware->handle($request, fn (Request $passedRequest): Response => new Response('secret gated content', 200, ['Content-Type' => 'text/html']));

    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($response->headers->get('Cache-Control'))->toContain('private')
        ->and($response->getContent())->toBe('secret gated content');
});

it('real HtmlCacheMiddleware does not write gated content to disk cache', function (): void {
    if (! class_exists(HtmlCacheMiddleware::class)) {
        $this->markTestSkipped('HtmlCacheMiddleware is not available in this environment.');
    }

    $middleware = resolve(HtmlCacheMiddleware::class);

    $request = Request::create('/gated-page-disk-check', 'GET');
    $request->attributes->set('access_gate.protected', true);

    $response = $middleware->handle($request, fn (Request $passedRequest): Response => new Response('private gated content', 200, ['Content-Type' => 'text/html']));

    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($request->attributes->get(HtmlCacheMiddleware::CACHE_WRITE_SUCCEEDED_ATTRIBUTE))->toBeNull();
});

it('real HtmlCacheMiddleware recognises the access_gate.protected attribute name has not drifted', function (): void {
    if (! class_exists(HtmlCacheMiddleware::class)) {
        $this->markTestSkipped('HtmlCacheMiddleware is not available in this environment.');
    }

    $source = (string) file_get_contents(
        (new ReflectionClass(HtmlCacheMiddleware::class))->getFileName(),
    );

    expect($source)->toContain('access_gate.protected');
});
