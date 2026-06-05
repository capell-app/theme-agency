<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\SeoSuite\Health\SeoSuiteHealthCheck;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

it('reports a compatible capell api version', function (): void {
    expect(SeoSuiteHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = SeoSuiteHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(5)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->label !== ''))->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->message !== ''))->toBeTrue();
});

it('maps each declared manifest health key to its targeted diagnostics', function (): void {
    $expectedLabelsByKey = [
        'seo-suite.page-report' => [
            __('capell-seo-suite::generic.health_storage_tables_label'),
            __('capell-seo-suite::generic.health_seo_services_label'),
        ],
        'seo-suite.schema-graph' => [
            __('capell-seo-suite::generic.health_seo_services_label'),
            __('capell-seo-suite::generic.health_schema_templates_label'),
        ],
        'seo-suite.broken-links' => [
            __('capell-seo-suite::generic.health_storage_tables_label'),
            __('capell-seo-suite::generic.health_seo_services_label'),
        ],
        'seo-suite.ai-discovery' => [
            __('capell-seo-suite::generic.health_storage_tables_label'),
            __('capell-seo-suite::generic.health_ai_discovery_routes_label'),
            __('capell-seo-suite::generic.health_seo_services_label'),
            __('capell-seo-suite::generic.health_ai_crawler_policy_label'),
        ],
    ];

    foreach ($expectedLabelsByKey as $key => $expectedLabels) {
        $labels = SeoSuiteHealthCheck::runDiagnostics($key)
            ->map(static fn (DoctorCheckResultData $result): string => $result->label)
            ->all();

        expect($labels)->toBe($expectedLabels);
    }

    expect(SeoSuiteHealthCheck::runDiagnostics('seo-suite.unknown'))->toBeEmpty();
});

it('passes when storage, routes, services, schema templates, and crawler policy are configured', function (): void {
    $check = new SeoSuiteHealthCheck;

    expect(SeoSuiteHealthCheck::passed())->toBeTrue()
        ->and($check->missingTables())->toBe([])
        ->and($check->missingAiDiscoveryRoutes())->toBe([])
        ->and($check->unresolvableSeoServices())->toBe([])
        ->and($check->missingSchemaTemplates())->toBe([])
        ->and($check->missingAiCrawlerAgents())->toBe([]);
});

it('fails the storage table check when a seo suite table is missing', function (): void {
    Schema::drop('broken_links');

    $check = new SeoSuiteHealthCheck;

    expect($check->missingTables())->toContain('broken_links')
        ->and($check->storageTablesCheck()->passed)->toBeFalse()
        ->and(SeoSuiteHealthCheck::passed())->toBeFalse();
});

it('fails the ai crawler policy check when required crawler defaults are missing', function (): void {
    Config::set('capell-seo-suite.ai_discovery.default_crawler_rules', [
        [
            'user_agent' => 'GPTBot',
        ],
    ]);

    $check = new SeoSuiteHealthCheck;

    expect($check->missingAiCrawlerAgents())->toContain('ClaudeBot', 'PerplexityBot', 'Google-Extended', 'CCBot')
        ->and($check->aiCrawlerPolicyCheck()->passed)->toBeFalse()
        ->and(SeoSuiteHealthCheck::passed())->toBeFalse();
});

it('confirms ai discovery routes and seo services are available', function (): void {
    $check = new SeoSuiteHealthCheck;

    expect($check->missingAiDiscoveryRoutes())->toBe([])
        ->and($check->aiDiscoveryRoutesCheck()->passed)->toBeTrue()
        ->and($check->unresolvableSeoServices())->toBe([])
        ->and($check->seoServiceBindingsCheck()->passed)->toBeTrue();
});

it('confirms built-in schema templates are registered', function (): void {
    $check = new SeoSuiteHealthCheck;

    expect($check->missingSchemaTemplates())->toBe([])
        ->and($check->schemaTemplateRegistryCheck()->passed)->toBeTrue();
});
