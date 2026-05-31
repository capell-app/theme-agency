<?php

declare(strict_types=1);

use Capell\SeoSuite\Enums\AiDiscoveryStatusEnum;
use Capell\SeoSuite\Http\Controllers\PageMarkdownController;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Illuminate\Http\Request;

it('checks markdown availability against site profile flags and accept header requirements', function (): void {
    $controller = new PageMarkdownController;
    $method = new ReflectionMethod(PageMarkdownController::class, 'isAvailable');
    $profile = new AiDiscoverySiteProfile;
    $profile->forceFill([
        'markdown_pages_enabled' => true,
        'accept_markdown_enabled' => false,
        'status' => AiDiscoveryStatusEnum::Enabled,
    ]);

    expect($method->invoke($controller, $profile, false))->toBeTrue()
        ->and($method->invoke($controller, $profile, true))->toBeFalse();

    $profile->forceFill(['accept_markdown_enabled' => true]);

    expect($method->invoke($controller, $profile, true))->toBeTrue();

    $profile->forceFill(['markdown_pages_enabled' => false]);

    expect($method->invoke($controller, $profile, false))->toBeFalse();

    $profile->forceFill([
        'markdown_pages_enabled' => true,
        'status' => AiDiscoveryStatusEnum::Disabled,
    ]);

    expect($method->invoke($controller, $profile, false))->toBeFalse();
});

it('normalizes markdown request paths and builds stable etags', function (): void {
    $controller = new PageMarkdownController;
    $canonicalPath = new ReflectionMethod(PageMarkdownController::class, 'canonicalPath');
    $etag = new ReflectionMethod(PageMarkdownController::class, 'etag');

    expect($canonicalPath->invoke($controller, null))->toBe('/')
        ->and($canonicalPath->invoke($controller, 'index'))->toBe('/')
        ->and($canonicalPath->invoke($controller, '/docs/index.md'))->toBe('/docs')
        ->and($canonicalPath->invoke($controller, 'docs/page.md'))->toBe('/docs/page')
        ->and($etag->invoke($controller, 'markdown body'))->toBe('"' . hash('sha256', 'markdown body') . '"');
});

it('builds canonical markdown request urls with query strings', function (): void {
    $controller = new PageMarkdownController;
    $method = new ReflectionMethod(PageMarkdownController::class, 'canonicalRequestUrl');
    $request = Request::create('https://example.test/ignored?preview=1');

    expect($method->invoke($controller, $request, 'docs/page.md'))->toBe('https://example.test/docs/page?preview=1')
        ->and($method->invoke($controller, Request::create('https://example.test/ignored'), 'index.md'))->toBe('https://example.test/index');
});

it('returns an empty 404 markdown response instead of aborting for optional accept-header rendering', function (): void {
    $controller = new PageMarkdownController;
    $method = new ReflectionMethod(PageMarkdownController::class, 'unavailable');

    $response = $method->invoke($controller, false);

    expect($response->getStatusCode())->toBe(404)
        ->and($response->getContent())->toBe('');
});
