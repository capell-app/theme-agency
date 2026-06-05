<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

arch('shopify commerce does not depend on frontend or authoring packages')
    ->expect('Capell\ShopifyCommerce')
    ->not->toUse([
        'Capell\Frontend',
        'Capell\FrontendAuthoring',
        'Capell\PublicActions',
    ]);

it('keeps oauth routes authenticated and declares no frontend provider', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = shopifyCommerceManifest();
    $routeFile = file_get_contents($packagePath . '/routes/oauth.php');

    expect($routeFile)->not->toBeFalse()
        ->and((string) $routeFile)->toContain("Route::middleware(['web', 'auth'])")
        ->and(data_get($manifest, 'surfaces'))->not->toContain('frontend')
        ->and(data_get($manifest, 'providers.frontend'))->toBe([])
        ->and(data_get($manifest, 'performance.frontendRenderBudgetMs'))->toBe(0)
        ->and(data_get($manifest, 'performance.cacheSafety.cacheable'))->toBeFalse()
        ->and(data_get($manifest, 'performance.cacheSafety.sensitiveOutput'))->toBeTrue();
});

it('does not ship public output files with shopify secrets or admin internals', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $publicOutputPaths = array_filter([
        $packagePath . '/resources/views/public',
        $packagePath . '/resources/views/components/public',
        $packagePath . '/resources/js',
        $packagePath . '/resources/css',
        $packagePath . '/public',
    ], is_dir(...));

    if ($publicOutputPaths === []) {
        expect($publicOutputPaths)->toBeEmpty();

        return;
    }

    $forbiddenFragments = [
        'access_token',
        'shopify_oauth_states',
        'raw_snapshot',
        'GraphQL',
        'graphql',
        '/admin/api/',
        'myshopify.com/admin',
        'capell/oauth/shopify',
        'frontend-authoring',
        'capell-authoring',
        'data-capell-edit',
        'data-field-path',
        'data-model-id',
        'signed-editor',
    ];
    $violations = [];
    $files = (new Finder)
        ->files()
        ->in($publicOutputPaths)
        ->name(['*.blade.php', '*.php', '*.js', '*.css', '*.html']);

    foreach ($files as $file) {
        $contents = $file->getContents();

        foreach ($forbiddenFragments as $fragment) {
            if (str_contains($contents, $fragment)) {
                $violations[] = sprintf('%s contains %s', $file->getRelativePathname(), $fragment);
            }
        }
    }

    expect($violations)->toBeEmpty();
});

arch()
    ->expect('Capell\ShopifyCommerce')
    ->classes()
    ->toUseStrictEquality();

/**
 * @return array<string, mixed>
 */
function shopifyCommerceManifest(): array
{
    return capell_json_file_array(dirname(__DIR__, 2) . '/capell.json');
}
