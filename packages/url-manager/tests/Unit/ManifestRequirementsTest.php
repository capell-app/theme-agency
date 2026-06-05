<?php

declare(strict_types=1);

use Capell\UrlManager\Filament\Pages\NotFoundOpportunitiesPage;
use Capell\UrlManager\Filament\Pages\RedirectRulesPage;
use Capell\UrlManager\Manifest\NotFoundOpportunitiesPageContribution;
use Capell\UrlManager\Manifest\RedirectRulesPageContribution;
use Capell\UrlManager\Manifest\UrlManagerModelsContribution;
use Capell\UrlManager\Models\NotFoundOpportunity;
use Capell\UrlManager\Models\RedirectHit;
use Capell\UrlManager\Models\RedirectRule;
use Capell\UrlManager\Providers\UrlManagerServiceProvider;

it('declares URL Manager owned models and protected tables', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect((new RedirectRule)->getTable())->toBe('url_manager_redirect_rules')
        ->and($manifest['description'])->toContain("site's link equity")
        ->and($manifest['marketplace']['summary'])->toBe('Stop losing traffic to broken links — manage redirects, auto-preserve moved page URLs, and turn repeated 404s into recovered SEO.')
        ->and($manifest['marketplace']['description'])->toContain('CSV import/export for bulk migrations')
        ->and($manifest['marketplace']['screenshots'])->toHaveCount(8)
        ->and((new RedirectHit)->getTable())->toBe('url_manager_redirect_hits')
        ->and((new NotFoundOpportunity)->getTable())->toBe('url_manager_not_found_opportunities')
        ->and($manifest['database']['requiredTables'])->toBe([
            'url_manager_redirect_rules',
            'url_manager_redirect_hits',
            'url_manager_not_found_opportunities',
        ])
        ->and($manifest['providers']['runtime'])->toContain(UrlManagerServiceProvider::class)
        ->and($manifest['providers']['admin'])->toContain(UrlManagerServiceProvider::class)
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-page',
            'class' => RedirectRulesPageContribution::class,
            'pageClass' => RedirectRulesPage::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-page',
            'class' => NotFoundOpportunitiesPageContribution::class,
            'pageClass' => NotFoundOpportunitiesPage::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'model',
            'class' => UrlManagerModelsContribution::class,
        ])
        ->and($manifest['actions'])->toHaveKeys([
            'buildNotFoundRedirectSuggestions',
            'convertNotFoundOpportunityToRedirect',
            'exportRedirectRules',
            'importRedirectRules',
            'importSeoSuiteBrokenLinks',
            'recordChangedUrlRedirect',
            'recordNotFoundOpportunity',
            'recordRedirectHit',
            'resolveRedirectRule',
            'upsertRedirectRule',
        ])
        ->and($manifest['capabilities'])->toContain(
            'managed-redirects',
            'changed-url-redirect-detection',
            'redirect-hit-counts',
            'redirect-import-export',
            'not-found-opportunities',
            'not-found-redirect-suggestions',
            'seo-suite-broken-url-import',
            'frontend-redirect-resolver',
            'url-manager-admin',
        )
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([]);
});

it('references existing URL Manager marketplace screenshots', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    foreach ($manifest['marketplace']['screenshots'] as $screenshot) {
        expect($screenshot['path'])->toStartWith('docs/screenshots/')
            ->and(file_exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
