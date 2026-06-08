<?php

declare(strict_types=1);

it('documents the approved extension suite catalog', function (string $suiteName): void {
    expect(extension_suites_documentation())->toContain($suiteName);
})->with([
    'layout pro' => 'Layout Pro',
    'media pro dam' => 'Media Pro / DAM',
    'account security' => 'Account Security',
    'ai content ops' => 'AI Content Ops',
    'storefront' => 'Storefront',
    'migration seo' => 'Migration & SEO',
    'findability' => 'Findability',
    'member portal' => 'Member Portal',
    'courses lms lite' => 'Courses / LMS Lite',
    'local business' => 'Local Business',
]);

it('declares optional supports for extension suite anchors', function (string $manifestPath, array $expectedSupports): void {
    $manifest = extension_suites_manifest($manifestPath);
    $supports = data_get($manifest, 'dependencies.supports', []);

    expect($supports)
        ->toBeArray()
        ->toContain(...$expectedSupports);
})->with([
    'layout pro' => [
        'packages/layout-builder/capell.json',
        [
            'capell-app/content-sections',
            'capell-app/frontend-authoring',
            'capell-app/publishing-studio',
            'capell-app/structured-content-library',
        ],
    ],
    'media pro dam' => [
        'packages/media-library/capell.json',
        [
            'capell-app/media-ai',
            'capell-app/seo-suite',
        ],
    ],
    'account security' => [
        'packages/password-policy/capell.json',
        [
            'capell-app/access-gate',
            'capell-app/diagnostics',
            'capell-app/login-audit',
            'capell-app/privacy-center',
        ],
    ],
    'ai content ops' => [
        'packages/ai-orchestrator/capell.json',
        [
            'capell-app/content-sections',
            'capell-app/media-ai',
            'capell-app/seo-suite',
            'capell-app/translation-manager',
        ],
    ],
    'storefront' => [
        'packages/shopify-commerce/capell.json',
        [
            'capell-app/contacts',
            'capell-app/media-library',
            'capell-app/payments',
            'capell-app/search',
            'capell-app/theme-commerce',
        ],
    ],
    'migration seo' => [
        'packages/migration-assistant/capell.json',
        [
            'capell-app/media-library',
            'capell-app/seo-suite',
            'capell-app/site-discovery',
            'capell-app/url-manager',
            'capell-app/wordpress-importer',
        ],
    ],
    'findability' => [
        'packages/search/capell.json',
        [
            'capell-app/seo-suite',
            'capell-app/site-discovery',
            'capell-app/url-manager',
        ],
    ],
    'member portal' => [
        'packages/customer-portal/capell.json',
        [
            'capell-app/access-gate',
            'capell-app/contacts',
            'capell-app/document-lifecycle',
            'capell-app/events',
            'capell-app/newsletter',
            'capell-app/payments',
            'capell-app/privacy-center',
        ],
    ],
    'courses lms lite' => [
        'packages/theme-education/capell.json',
        [
            'capell-app/access-gate',
            'capell-app/bookings',
            'capell-app/customer-portal',
            'capell-app/events',
            'capell-app/form-builder',
            'capell-app/payments',
            'capell-app/seo-suite',
        ],
    ],
    'local business' => [
        'packages/theme-local-services/capell.json',
        [
            'capell-app/address',
            'capell-app/bookings',
            'capell-app/events',
            'capell-app/form-builder',
            'capell-app/seo-suite',
        ],
    ],
]);

function extension_suites_documentation(): string
{
    $contents = file_get_contents(extension_suites_repository_path('docs/extension-suites.md'));

    throw_unless(is_string($contents), RuntimeException::class, 'Unable to read extension suites documentation.');

    return $contents;
}

/**
 * @return array<string, mixed>
 */
function extension_suites_manifest(string $relativePath): array
{
    return capell_json_file_array(extension_suites_repository_path($relativePath));
}

function extension_suites_repository_path(string $relativePath): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
}
