<?php

declare(strict_types=1);

use Capell\Core\Contracts\RedirectResolver;
use Capell\Core\Data\RedirectDecisionData;
use Capell\Core\Events\PageUrlChanged;
use Capell\Core\Models\Language;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\UrlManager\Actions\UpsertRedirectRuleAction;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Listeners\RecordRedirectForChangedPageUrl;
use Capell\UrlManager\Models\RedirectRule;
use Capell\UrlManager\Support\Redirects\UrlManagerRedirectResolver;
use Illuminate\Http\Request;

it('preserves an existing core redirect decision before checking managed redirects', function (): void {
    $resolver = new UrlManagerRedirectResolver(new class implements RedirectResolver
    {
        public function resolve(Site $site, Language $language, string $url, ?int $pageId = null, ?PageUrl $pageUrl = null): RedirectDecisionData
        {
            return new RedirectDecisionData('/core-target', 308);
        }
    });

    $decision = $resolver->resolve(urlManagerTestSite(), urlManagerTestLanguage(), '/legacy');

    expect($decision?->targetUrl)->toBe('/core-target')
        ->and($decision?->statusCode)->toBe(308);
});

it('resolves managed redirects through the core redirect resolver contract', function (): void {
    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/legacy',
        targetUrl: '/current',
        siteId: 1,
        languageId: 1,
    ));

    app()->instance('request', Request::create('/legacy?utm_source=test', Symfony\Component\HttpFoundation\Request::METHOD_GET));

    $decision = (new UrlManagerRedirectResolver)->resolve(urlManagerTestSite(), urlManagerTestLanguage(), '/legacy');

    expect($decision?->targetUrl)->toBe('/current?utm_source=test')
        ->and($decision?->statusCode)->toBe(301)
        ->and(RedirectRule::query()->first()?->hit_count)->toBe(0);

    app()->terminate();

    expect(RedirectRule::query()->first()?->hit_count)->toBe(1);
});

it('does not hide an existing non redirect page URL with a managed redirect', function (): void {
    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/real-page',
        targetUrl: '/other-page',
        siteId: 1,
        languageId: 1,
    ));

    $pageUrl = new PageUrl([
        'url' => '/real-page',
        'type' => null,
        'status' => true,
    ]);

    $decision = (new UrlManagerRedirectResolver)->resolve(
        site: urlManagerTestSite(),
        language: urlManagerTestLanguage(),
        url: '/real-page',
        pageUrl: $pageUrl,
    );

    expect($decision)->toBeNull();
});

it('records changed page URL events as managed redirect rules', function (): void {
    (new RecordRedirectForChangedPageUrl)->handle(new PageUrlChanged(
        page_url_id: 10,
        page_id: 20,
        site_id: 1,
        language_id: 1,
        old_url: '/old-page/',
        new_url: '/new-page/',
    ));

    $redirectRule = RedirectRule::query()->first();

    expect($redirectRule)->toBeInstanceOf(RedirectRule::class)
        ->and($redirectRule?->source_url)->toBe('/old-page')
        ->and($redirectRule?->target_url)->toBe('/new-page')
        ->and($redirectRule?->site_id)->toBe(1)
        ->and($redirectRule?->language_id)->toBe(1);
});

function urlManagerTestSite(): Site
{
    $site = new Site;
    $site->forceFill(['id' => 1]);

    return $site;
}

function urlManagerTestLanguage(): Language
{
    $language = new Language;
    $language->forceFill(['id' => 1]);

    return $language;
}
