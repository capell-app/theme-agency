<?php

declare(strict_types=1);

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
        ->and((new RedirectHit)->getTable())->toBe('url_manager_redirect_hits')
        ->and((new NotFoundOpportunity)->getTable())->toBe('url_manager_not_found_opportunities')
        ->and($manifest['database']['requiredTables'])->toBe([
            'url_manager_redirect_rules',
            'url_manager_redirect_hits',
            'url_manager_not_found_opportunities',
        ])
        ->and($manifest['providers']['runtime'])->toContain(UrlManagerServiceProvider::class);
});
