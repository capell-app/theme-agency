<?php

declare(strict_types=1);

use Capell\UrlManager\Actions\ExportRedirectRulesAction;
use Capell\UrlManager\Actions\ImportRedirectRulesAction;
use Capell\UrlManager\Actions\RecordChangedUrlRedirectAction;
use Capell\UrlManager\Actions\ResolveRedirectRuleAction;
use Capell\UrlManager\Actions\UpsertRedirectRuleAction;
use Capell\UrlManager\Data\ChangedUrlRedirectData;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Models\RedirectHit;
use Capell\UrlManager\Models\RedirectRule;

it('upserts and resolves an exact redirect rule with hit tracking', function (): void {
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

it('imports valid redirect rows and reports invalid rows', function (): void {
    $result = ImportRedirectRulesAction::run([
        [
            'source_url' => '/legacy',
            'target_url' => '/current',
            'status_code' => 302,
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
        ->and($export[0]['status_code'])->toBe(302);
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

it('does not record a redirect when changed page urls normalize to the same path', function (): void {
    $redirectRule = RecordChangedUrlRedirectAction::run(new ChangedUrlRedirectData(
        previousUrl: '/same-page/',
        currentUrl: '/same-page',
    ));

    expect($redirectRule)->toBeNull()
        ->and(RedirectRule::query()->count())->toBe(0);
});
