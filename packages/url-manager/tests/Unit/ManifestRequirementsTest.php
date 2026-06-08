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
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect((new RedirectRule)->getTable())->toBe('url_manager_redirect_rules')
        ->and(data_get($manifest, 'description'))->toContain("site's link equity")
        ->and(data_get($manifest, 'marketplace.summary'))->toBe('Stop losing traffic to broken links — manage redirects, auto-preserve moved page URLs, and turn repeated 404s into recovered SEO.')
        ->and(data_get($manifest, 'marketplace.description'))->toContain('CSV import/export for bulk migrations')
        ->and(data_get($manifest, 'marketplace.screenshots'))->toHaveCount(0)
        ->and((new RedirectHit)->getTable())->toBe('url_manager_redirect_hits')
        ->and((new NotFoundOpportunity)->getTable())->toBe('url_manager_not_found_opportunities')
        ->and(data_get($manifest, 'database.requiredTables'))->toBe([
            'url_manager_redirect_rules',
            'url_manager_redirect_hits',
            'url_manager_not_found_opportunities',
        ])
        ->and(data_get($manifest, 'providers.runtime'))->toContain(UrlManagerServiceProvider::class)
        ->and(data_get($manifest, 'providers.admin'))->toContain(UrlManagerServiceProvider::class)
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'admin-page',
            'class' => RedirectRulesPageContribution::class,
            'pageClass' => RedirectRulesPage::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'admin-page',
            'class' => NotFoundOpportunitiesPageContribution::class,
            'pageClass' => NotFoundOpportunitiesPage::class,
        ])
        ->and(data_get($manifest, 'contributes'))->toContain([
            'type' => 'model',
            'class' => UrlManagerModelsContribution::class,
        ])
        ->and(data_get($manifest, 'actions'))->toHaveKeys([
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
        ->and(data_get($manifest, 'capabilities'))->toContain(
            'managed-redirects',
            'changed-url-redirect-detection',
            'redirect-hit-counts',
            'managed-gone-rules',
            'redirect-import-export',
            'not-found-opportunities',
            'not-found-redirect-suggestions',
            'seo-suite-broken-url-import',
            'frontend-redirect-resolver',
            'url-manager-admin',
        )
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([]);
});

it('keeps URL Manager marketplace screenshots empty until styled recapture', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    throw_unless(is_array($screenshots), RuntimeException::class, 'URL Manager screenshots must be an array.');

    expect($screenshots)->toBe([]);

    foreach ($screenshots as $screenshot) {
        throw_unless(is_array($screenshot), RuntimeException::class, 'URL Manager screenshot entries must be arrays.');

        $path = $screenshot['path'] ?? null;

        throw_unless(is_string($path), RuntimeException::class, 'URL Manager screenshot paths must be strings.');

        expect($path)->toStartWith('docs/screenshots/')
            ->and(file_exists(__DIR__ . '/../../' . $path))->toBeTrue();
    }
});
