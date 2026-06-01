<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('declares implemented diagnostics commands tables and capabilities', function (): void {
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['commands']['doctor'] ?? null)->toBe('capell:seo-suite-doctor')
        ->and($manifest['database']['requiredTables'] ?? [])->toContain(
            'ai_discovery_page_profiles',
            'ai_discovery_snapshots',
            'broken_links',
            'page_seo_snapshots',
            'page_speed_audit_runs',
            'search_console_url_metrics',
        )
        ->and($manifest['capabilities'] ?? [])->toContain(
            'seo-suite-doctor',
            'seo-suite-public-output-leak-scanning',
            'seo-suite-structured-data-audit',
            'seo-suite-ai-discovery-coverage',
            'seo-suite-stale-output-regeneration',
            'seo-suite-site-discovery-registry',
        );
});
