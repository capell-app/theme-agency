<?php

declare(strict_types=1);

use Capell\UrlManager\Actions\BuildNotFoundRedirectSuggestionsAction;
use Capell\UrlManager\Actions\ConvertNotFoundOpportunityToRedirectAction;
use Capell\UrlManager\Actions\ImportSeoSuiteBrokenLinksAction;
use Capell\UrlManager\Actions\RecordNotFoundOpportunityAction;
use Capell\UrlManager\Actions\SetNotFoundOpportunityStatusAction;
use Capell\UrlManager\Actions\UpsertRedirectRuleAction;
use Capell\UrlManager\Data\ConvertNotFoundOpportunityData;
use Capell\UrlManager\Data\NotFoundOpportunityData;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\NotFoundOpportunityStatus;
use Capell\UrlManager\Models\NotFoundOpportunity;
use Capell\UrlManager\Models\RedirectRule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('records repeat 404 opportunities against the same normalized source', function (): void {
    RecordNotFoundOpportunityAction::run(new NotFoundOpportunityData(
        sourceUrl: '/gone/',
        context: ['source' => 'frontend_404'],
    ));

    $opportunity = RecordNotFoundOpportunityAction::run(new NotFoundOpportunityData(
        sourceUrl: '/gone',
        context: ['source' => 'frontend_404'],
    ));

    expect(NotFoundOpportunity::query()->count())->toBe(1)
        ->and($opportunity->source_url)->toBe('/gone')
        ->and($opportunity->hit_count)->toBe(2)
        ->and($opportunity->status)->toBe(NotFoundOpportunityStatus::Open);
});

it('suggests redirect targets for matching 404 slugs from existing rules', function (): void {
    UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/old-product',
        targetUrl: '/products/blue-widget',
    ));

    RecordNotFoundOpportunityAction::run(new NotFoundOpportunityData(
        sourceUrl: '/shop/blue-widget',
    ));

    $updated = BuildNotFoundRedirectSuggestionsAction::run();

    expect($updated)->toBe(1)
        ->and(NotFoundOpportunity::query()->first()?->suggested_target_url)->toBe('/products/blue-widget');
});

it('imports seo suite broken link rows as 404 opportunities when seo suite is available', function (): void {
    Schema::create('pages', function (Blueprint $table): void {
        $table->id();
    });

    Schema::create('broken_links', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
        $table->string('target_url');
        $table->integer('http_status')->nullable();
        $table->timestamp('last_checked_at')->nullable();
        $table->timestamps();
    });

    DB::table('pages')->insert(['id' => 10]);
    DB::table('broken_links')->insert([
        [
            'page_id' => 10,
            'target_url' => 'https://example.test/missing-page?from=seo',
            'http_status' => 404,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'page_id' => 10,
            'target_url' => '/healthy-page',
            'http_status' => 200,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $imported = ImportSeoSuiteBrokenLinksAction::run(siteId: 1, languageId: 1);
    $opportunity = NotFoundOpportunity::query()->first();

    expect($imported)->toBe(1)
        ->and($opportunity?->source_url)->toBe('/missing-page')
        ->and($opportunity?->site_id)->toBe(1)
        ->and($opportunity?->language_id)->toBe(1)
        ->and($opportunity?->context['source'] ?? null)->toBe('seo_suite_broken_link')
        ->and($opportunity?->context['page_id'] ?? null)->toBe(10);
});

it('converts a 404 opportunity into a managed redirect rule', function (): void {
    $opportunity = RecordNotFoundOpportunityAction::run(new NotFoundOpportunityData(
        sourceUrl: '/missing-resource',
        siteId: 1,
        languageId: 1,
        suggestedTargetUrl: '/current-resource',
    ));

    $redirectRule = ConvertNotFoundOpportunityToRedirectAction::run(new ConvertNotFoundOpportunityData(
        opportunityId: (int) $opportunity->getKey(),
        statusCode: 302,
        notes: 'Reviewed by editor.',
    ));

    $opportunity->refresh();

    expect($redirectRule)->toBeInstanceOf(RedirectRule::class)
        ->and($redirectRule->source_url)->toBe('/missing-resource')
        ->and($redirectRule->target_url)->toBe('/current-resource')
        ->and($redirectRule->status_code)->toBe(302)
        ->and($redirectRule->notes)->toBe('Reviewed by editor.')
        ->and($opportunity->status)->toBe(NotFoundOpportunityStatus::Converted)
        ->and($opportunity->redirect_rule_id)->toBe((int) $redirectRule->getKey());
});

it('requires a target before converting a 404 opportunity', function (): void {
    $opportunity = RecordNotFoundOpportunityAction::run(new NotFoundOpportunityData(
        sourceUrl: '/missing-target',
    ));

    ConvertNotFoundOpportunityToRedirectAction::run(new ConvertNotFoundOpportunityData(
        opportunityId: (int) $opportunity->getKey(),
    ));
})->throws(InvalidArgumentException::class, 'A target URL is required to convert a 404 opportunity.');

it('ignores and reopens 404 opportunities without changing converted rows', function (): void {
    $openOpportunity = RecordNotFoundOpportunityAction::run(new NotFoundOpportunityData(
        sourceUrl: '/open-missing',
    ));
    $convertedOpportunity = RecordNotFoundOpportunityAction::run(new NotFoundOpportunityData(
        sourceUrl: '/converted-missing',
        suggestedTargetUrl: '/target',
    ));

    ConvertNotFoundOpportunityToRedirectAction::run(new ConvertNotFoundOpportunityData(
        opportunityId: (int) $convertedOpportunity->getKey(),
    ));

    $ignored = SetNotFoundOpportunityStatusAction::run(
        collect([$openOpportunity, $convertedOpportunity->refresh()]),
        NotFoundOpportunityStatus::Ignored,
    );

    expect($ignored)->toBe(1)
        ->and($openOpportunity->refresh()->status)->toBe(NotFoundOpportunityStatus::Ignored)
        ->and($convertedOpportunity->refresh()->status)->toBe(NotFoundOpportunityStatus::Converted);

    $reopened = SetNotFoundOpportunityStatusAction::run($openOpportunity, NotFoundOpportunityStatus::Open);

    expect($reopened)->toBe(1)
        ->and($openOpportunity->refresh()->status)->toBe(NotFoundOpportunityStatus::Open);
});
