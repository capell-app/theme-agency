<?php

declare(strict_types=1);

use Capell\UrlManager\Actions\BuildCanonicalUrlAction;
use Capell\UrlManager\Actions\ExportRedirectRulesAction;
use Capell\UrlManager\Actions\ImportRedirectRulesAction;
use Capell\UrlManager\Actions\PreviewRedirectRulesImportAction;
use Capell\UrlManager\Actions\PruneRedirectHitsAction;
use Capell\UrlManager\Actions\RecordChangedUrlRedirectAction;
use Capell\UrlManager\Actions\RecordRedirectHitAction;
use Capell\UrlManager\Actions\ResolveRedirectRuleAction;
use Capell\UrlManager\Actions\UpdateRedirectRuleAction;
use Capell\UrlManager\Actions\UpsertRedirectRuleAction;
use Capell\UrlManager\Data\ChangedUrlRedirectData;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Models\RedirectHit;
use Capell\UrlManager\Models\RedirectRule;

it('upserts and resolves an exact redirect rule with hit tracking', function (): void {
    config(['capell-url-manager.hit_recording.defer' => false]);

    $redirectRule = UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/old-page/',
        targetUrl: '/new-page/',
    ));

    $resolution = ResolveRedirectRuleAction::run('/old-page');

    expect($redirectRule)->toBeInstanceOf(RedirectRule::class)
        ->and($redirectRule->source_url)->toBe('/old-page')
        ->and($redirectRule->target_url)->toBe('/new-page')
        ->and($resolution?->targetUrl)->toBe('/new-page')
        ->and($resolution?->statusCode)->toBe(301)
        ->and(RedirectHit::query()->count())->toBe(1)
        ->and($redirectRule->fresh()?->hit_count)->toBe(1);
});

it('normalizes exact redirect matches without query strings or case drift', function (): void {
    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/Old-Page/',
        targetUrl: '/New-Page/',
    ));

    $resolution = ResolveRedirectRuleAction::run('/old-page?utm_source=test', recordHit: false);

    expect($resolution?->sourceUrl)->toBe('/old-page')
        ->and($resolution?->targetUrl)->toBe('/new-page');
});

it('resolves the most specific prefix redirect rule', function (): void {
    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/docs',
        targetUrl: '/resources',
        matchType: RedirectMatchType::Prefix,
    ));

    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/docs/guides',
        targetUrl: '/learn/guides',
        matchType: RedirectMatchType::Prefix,
    ));

    $resolution = ResolveRedirectRuleAction::run('/docs/guides/install', recordHit: false);

    expect($resolution?->targetUrl)->toBe('/learn/guides/install')
        ->and($resolution?->matchType)->toBe(RedirectMatchType::Prefix);
});

it('uses redirect priority when prefix rules overlap', function (): void {
    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/docs',
        targetUrl: '/resources',
        matchType: RedirectMatchType::Prefix,
        priority: 10,
    ));

    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/docs/guides',
        targetUrl: '/learn/guides',
        matchType: RedirectMatchType::Prefix,
        priority: 0,
    ));

    $resolution = ResolveRedirectRuleAction::run('/docs/guides/install', recordHit: false);

    expect($resolution?->targetUrl)->toBe('/resources/guides/install');
});

it('rejects absolute redirect targets outside the configured host allowlist', function (): void {
    config([
        'app.url' => 'https://capell.test',
        'capell-url-manager.redirects.absolute_target_allowed_hosts' => ['trusted.example'],
    ]);

    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/legacy',
        targetUrl: 'https://evil.example/phishing',
    ));
})->throws(InvalidArgumentException::class, 'Absolute redirect targets to evil.example are not allowed.');

it('allows app and configured host absolute redirect targets', function (): void {
    config([
        'app.url' => 'https://capell.test',
        'capell-url-manager.redirects.absolute_target_allowed_hosts' => ['trusted.example'],
    ]);

    $appRedirect = UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/app-target',
        targetUrl: 'https://capell.test/current',
    ));

    $trustedRedirect = UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/trusted-target',
        targetUrl: 'https://trusted.example/current',
    ));

    expect($appRedirect->target_url)->toBe('https://capell.test/current')
        ->and($trustedRedirect->target_url)->toBe('https://trusted.example/current');
});

it('applies open-redirect protection when updating existing redirect rules', function (): void {
    config([
        'app.url' => 'https://capell.test',
        'capell-url-manager.redirects.absolute_target_allowed_hosts' => [],
    ]);

    $redirectRule = UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/legacy',
        targetUrl: '/current',
    ));

    UpdateRedirectRuleAction::run($redirectRule, new RedirectRuleData(
        sourceUrl: '/legacy',
        targetUrl: 'https://evil.example/current',
    ));
})->throws(InvalidArgumentException::class, 'Absolute redirect targets to evil.example are not allowed.');

it('rejects invalid or overlong regex redirect sources before saving', function (): void {
    config(['capell-url-manager.redirects.regex.max_pattern_length' => 8]);

    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/^([a-z]+)$/',
        targetUrl: '/target',
        matchType: RedirectMatchType::Regex,
    ));
})->throws(InvalidArgumentException::class, 'Regex redirect sources may not be longer than 8 characters.');

it('rejects exact redirect loops before saving', function (): void {
    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/a',
        targetUrl: '/b',
    ));

    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/b',
        targetUrl: '/a',
    ));
})->throws(InvalidArgumentException::class, 'This redirect would create a redirect loop or chain cycle.');

it('collapses exact redirect chains before saving', function (): void {
    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/b',
        targetUrl: '/c',
    ));

    $redirectRule = UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/a',
        targetUrl: '/b',
    ));

    $resolution = ResolveRedirectRuleAction::run('/a', recordHit: false);

    expect($redirectRule->target_url)->toBe('/c')
        ->and($resolution?->targetUrl)->toBe('/c');
});

it('defers public hit writes until application termination by default', function (): void {
    config(['capell-url-manager.hit_recording.defer' => true]);

    $redirectRule = UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/deferred',
        targetUrl: '/target',
    ));

    ResolveRedirectRuleAction::run('/deferred');

    expect(RedirectHit::query()->count())->toBe(0)
        ->and($redirectRule->fresh()?->hit_count)->toBe(0);

    app()->terminate();

    expect(RedirectHit::query()->count())->toBe(1)
        ->and($redirectRule->fresh()?->hit_count)->toBe(1);
});

it('prunes redirect hit rows older than the configured retention window', function (): void {
    config(['capell-url-manager.hit_recording.defer' => false]);

    $redirectRule = UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/tracked',
        targetUrl: '/target',
    ));

    RecordRedirectHitAction::run($redirectRule, '/tracked');

    RedirectHit::query()->first()?->forceFill([
        'hit_at' => now()->subDays(400),
    ])->save();

    $deleted = PruneRedirectHitsAction::run(365);

    expect($deleted)->toBe(1)
        ->and(RedirectHit::query()->count())->toBe(0);
});

it('builds canonical urls from configurable URL Manager policy', function (): void {
    config([
        'capell-url-manager.canonical.scheme' => 'https',
        'capell-url-manager.canonical.host' => 'www.example.test',
        'capell-url-manager.canonical.trailing_slash' => 'remove',
        'capell-url-manager.canonical.strip_query_keys' => ['utm_source', 'fbclid'],
    ]);

    $canonicalUrl = BuildCanonicalUrlAction::run('http://EXAMPLE.test/Docs/Install/?utm_source=newsletter&page=2&fbclid=abc');

    expect($canonicalUrl)->toBe('https://www.example.test/docs/install?page=2');
});

it('imports valid redirect rows and reports invalid rows', function (): void {
    $result = ImportRedirectRulesAction::run([
        [
            'source_url' => '/legacy',
            'target_url' => '/current',
            'status_code' => 302,
            'priority' => 25,
        ],
        [
            'source_url' => '',
            'target_url' => '/missing-source',
        ],
    ]);

    $export = ExportRedirectRulesAction::run();

    expect($result->imported)->toBe(1)
        ->and($result->skipped)->toBe(1)
        ->and($result->errors)->toHaveCount(1)
        ->and($export)->toHaveCount(1)
        ->and($export[0]['source_url'])->toBe('/legacy')
        ->and($export[0]['status_code'])->toBe(302)
        ->and($export[0]['priority'])->toBe(25);
});

it('previews redirect imports with the same safety policy as import', function (): void {
    config([
        'app.url' => 'https://capell.test',
        'capell-url-manager.redirects.absolute_target_allowed_hosts' => [],
    ]);

    $result = PreviewRedirectRulesImportAction::run([
        [
            'source_url' => '/legacy',
            'target_url' => 'https://evil.example/current',
        ],
    ]);

    expect($result->imported)->toBe(0)
        ->and($result->skipped)->toBe(1)
        ->and($result->errors[0])->toContain('Absolute redirect targets to evil.example are not allowed.');
});

it('records redirects for changed page urls', function (): void {
    $redirectRule = RecordChangedUrlRedirectAction::run(new ChangedUrlRedirectData(
        previousUrl: '/old-page/',
        currentUrl: '/new-page/',
        siteId: 10,
        languageId: 20,
        notes: 'Recorded from a page URL change.',
        createdByUserId: 30,
    ));

    $resolution = ResolveRedirectRuleAction::run('/old-page', siteId: 10, languageId: 20, recordHit: false);

    expect($redirectRule)->toBeInstanceOf(RedirectRule::class)
        ->and($redirectRule?->source_url)->toBe('/old-page')
        ->and($redirectRule?->target_url)->toBe('/new-page')
        ->and($redirectRule?->notes)->toBe('Recorded from a page URL change.')
        ->and($redirectRule?->created_by_user_id)->toBe(30)
        ->and($resolution?->targetUrl)->toBe('/new-page');
});

it('records prefix redirects when a parent page url changes', function (): void {
    RecordChangedUrlRedirectAction::run(new ChangedUrlRedirectData(
        previousUrl: '/services',
        currentUrl: '/what-we-do',
        siteId: 10,
        languageId: 20,
    ));

    $resolution = ResolveRedirectRuleAction::run('/services/design', siteId: 10, languageId: 20, recordHit: false);

    expect($resolution?->targetUrl)->toBe('/what-we-do/design')
        ->and($resolution?->matchType)->toBe(RedirectMatchType::Prefix);
});

it('does not record a redirect when changed page urls normalize to the same path', function (): void {
    $redirectRule = RecordChangedUrlRedirectAction::run(new ChangedUrlRedirectData(
        previousUrl: '/same-page/',
        currentUrl: '/same-page',
    ));

    expect($redirectRule)->toBeNull()
        ->and(RedirectRule::query()->count())->toBe(0);
});
