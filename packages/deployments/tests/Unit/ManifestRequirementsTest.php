<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;
use Capell\Deployments\Manifest\DeploymentsAdminPageContribution;
use Capell\Deployments\Manifest\DeploymentsDashboardWidgetContribution;
use Capell\Deployments\Manifest\DeploymentsRoutesContribution;

/**
 * @return array<string, mixed>
 */
function deploymentsPackageManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Expected deployments manifest array.');

    return $manifest;
}

it('declares only shipped deployment surfaces and capabilities', function (): void {
    $manifest = deploymentsPackageManifest();
    $contributionTraceability = $manifest['contributionTraceability'] ?? null;

    throw_unless(is_array($contributionTraceability), RuntimeException::class, 'Expected deployments contribution traceability array.');

    expect($manifest['surfaces'] ?? null)->toBe(['admin'])
        ->and($manifest['capabilities'] ?? [])->toBe([
            'deployments',
            'deployments-admin',
            'deployments-install-policy',
            'deployments-publish-history',
            'deployments-publish-idempotency',
            'deployments-token-refresh',
        ])
        ->and($manifest['commands'] ?? [])->toBe([
            'install' => null,
            'setup' => null,
            'demo' => null,
            'doctor' => null,
        ]);
});

it('declares shipped deployments contributions and no longer defers them', function (): void {
    $manifest = deploymentsPackageManifest();
    $contributionTraceability = $manifest['contributionTraceability'] ?? null;

    throw_unless(is_array($contributionTraceability), RuntimeException::class, 'Expected deployments contribution traceability array.');

    expect($manifest['contributes'] ?? [])->toBe([
        [
            'type' => 'admin-page',
            'class' => DeploymentsAdminPageContribution::class,
            'pageClass' => 'Capell\\Deployments\\Filament\\Pages\\DeploymentConnectionPage',
            'labelKey' => 'capell-deployments::plugins.deployment_connection.nav_label',
            'surface' => 'admin',
        ],
        [
            'type' => 'route',
            'class' => DeploymentsRoutesContribution::class,
            'routes' => [
                'capell-deployments.oauth.bitbucket',
                'capell-deployments.oauth.github',
                'capell-deployments.oauth.gitlab',
            ],
            'prefix' => 'capell/oauth',
            'middleware' => ['web', 'auth'],
            'surface' => 'admin',
        ],
        [
            'type' => 'dashboard-widget',
            'class' => DeploymentsDashboardWidgetContribution::class,
            'widgetClass' => 'Capell\\Deployments\\Filament\\Widgets\\DeploymentConnectionWidget',
            'dashboard' => 'system-health',
            'surface' => 'admin',
        ],
    ])
        ->and($contributionTraceability['deferredContributions'] ?? null)->toBe([])
        ->and(class_implements(DeploymentsAdminPageContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(DeploymentsRoutesContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(DeploymentsRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(DeploymentsDashboardWidgetContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(DeploymentsDashboardWidgetContribution::class))->toContain(RegistersExtensionWidget::class)
        ->and(DeploymentsAdminPageContribution::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(DeploymentsRoutesContribution::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(DeploymentsDashboardWidgetContribution::compatibleCapellApiVersion())->toBe('^4.0');
});

it('describes authenticated oauth routes and all deployments http clients', function (): void {
    $manifest = deploymentsPackageManifest();
    $security = $manifest['security'] ?? null;

    throw_unless(is_array($security), RuntimeException::class, 'Expected deployments security metadata array.');
    throw_unless(is_array($security['publicSurface'] ?? null), RuntimeException::class, 'Expected deployments public surface metadata array.');
    throw_unless(is_array($security['externalHttpClients'] ?? null), RuntimeException::class, 'Expected deployments external HTTP client metadata array.');

    expect($security['publicSurface']['auth'] ?? null)->toBe('authenticated')
        ->and($security['publicSurface']['routeNames'] ?? [])->toBe([
            'capell-deployments.oauth.bitbucket',
            'capell-deployments.oauth.github',
            'capell-deployments.oauth.gitlab',
        ])
        ->and($security['externalHttpClients']['clients'] ?? [])->toContain(
            'Capell\\Deployments\\Actions\\RefreshProviderTokenAction',
            'Capell\\Deployments\\Http\\Controllers\\OAuth\\BitbucketCallbackController',
            'Capell\\Deployments\\Http\\Controllers\\OAuth\\GitHubCallbackController',
            'Capell\\Deployments\\Http\\Controllers\\OAuth\\GitLabCallbackController',
            'Capell\\Deployments\\Services\\GitProvider\\BitbucketProvider',
            'Capell\\Deployments\\Services\\GitProvider\\GitHubProvider',
            'Capell\\Deployments\\Services\\GitProvider\\GitLabProvider',
        );
});

it('keeps marketplace screenshots aligned with committed deployment media', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = deploymentsPackageManifest();
    $screenshotContract = json_decode(
        (string) file_get_contents($packagePath . '/docs/screenshots.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($screenshotContract), RuntimeException::class, 'Expected deployments screenshot contract array.');

    $contractEntries = $screenshotContract['entries'] ?? [];
    $marketplace = $manifest['marketplace'] ?? [];
    $marketplaceScreenshotEntries = is_array($marketplace) ? ($marketplace['screenshots'] ?? []) : [];

    throw_unless(is_array($contractEntries), RuntimeException::class, 'Expected deployments screenshot contract entries array.');
    throw_unless(is_array($marketplaceScreenshotEntries), RuntimeException::class, 'Expected deployments marketplace screenshot entries array.');

    $requiredScreenshotPaths = [];

    foreach ($contractEntries as $contractEntry) {
        throw_unless(is_array($contractEntry), RuntimeException::class, 'Deployments screenshot contract entries must be arrays.');

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required deployments screenshot entries must declare a screenshot path.');

        $requiredScreenshotPaths[] = str_replace('packages/deployments/', '', $screenshotPath);

        $darkScreenshotPath = $contractEntry['darkScreenshotPath'] ?? null;

        if (is_string($darkScreenshotPath) && $darkScreenshotPath !== '') {
            $requiredScreenshotPaths[] = str_replace('packages/deployments/', '', $darkScreenshotPath);
        }
    }

    $marketplaceScreenshotPaths = [];

    foreach ($marketplaceScreenshotEntries as $marketplaceScreenshotEntry) {
        throw_unless(is_array($marketplaceScreenshotEntry), RuntimeException::class, 'Deployments marketplace screenshot entries must be arrays.');

        $path = $marketplaceScreenshotEntry['path'] ?? null;
        $alt = $marketplaceScreenshotEntry['alt'] ?? null;
        $caption = $marketplaceScreenshotEntry['caption'] ?? null;

        throw_unless(is_string($path), RuntimeException::class, 'Deployments marketplace screenshot paths must be strings.');
        throw_unless(is_string($alt), RuntimeException::class, 'Deployments marketplace screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'Deployments marketplace screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $path;

        expect(file_exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    expect($requiredScreenshotPaths)->toBe([
        'docs/screenshots/deployment-connection-page.png',
        'docs/screenshots/deployment-connection-page-dark.png',
    ])->and($marketplaceScreenshotPaths)->toBe([
        'docs/assets/marketplace/extension-card.jpg',
        ...$requiredScreenshotPaths,
    ]);
});
