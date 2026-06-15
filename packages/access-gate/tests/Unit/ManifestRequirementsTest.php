<?php

declare(strict_types=1);

use Capell\AccessGate\Console\Commands\AccessGateDoctorCommand;
use Capell\AccessGate\Console\Commands\AccessGateInstallCommand;
use Capell\AccessGate\Console\Commands\AccessGatePruneCommand;
use Capell\AccessGate\Console\Commands\AccessGateSetupCommand;
use Capell\AccessGate\Filament\Resources\AccessAreas\AccessAreaResource;
use Capell\AccessGate\Filament\Resources\BrowserTokens\BrowserTokenResource;
use Capell\AccessGate\Filament\Resources\ClaimTokens\ClaimTokenResource;
use Capell\AccessGate\Filament\Resources\Events\AccessGateEventResource;
use Capell\AccessGate\Filament\Resources\Grants\GrantResource;
use Capell\AccessGate\Filament\Resources\Registrations\RegistrationResource;
use Capell\AccessGate\Filament\Widgets\PendingAccessRequestsWidget;
use Capell\AccessGate\Health\AccessGateHealthCheck;
use Capell\AccessGate\Manifest\AccessAreaResourceContribution;
use Capell\AccessGate\Manifest\AccessGateConsoleCommandsContribution;
use Capell\AccessGate\Manifest\AccessGateEventResourceContribution;
use Capell\AccessGate\Manifest\AccessGateHealthContribution;
use Capell\AccessGate\Manifest\AccessGateModelsContribution;
use Capell\AccessGate\Manifest\AccessGateRoutesContribution;
use Capell\AccessGate\Manifest\BrowserTokenResourceContribution;
use Capell\AccessGate\Manifest\ClaimTokenResourceContribution;
use Capell\AccessGate\Manifest\GrantResourceContribution;
use Capell\AccessGate\Manifest\PaidAccessCheckoutCreationContribution;
use Capell\AccessGate\Manifest\PendingAccessRequestsWidgetContribution;
use Capell\AccessGate\Manifest\RegistrationResourceContribution;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Models\ClaimToken;
use Capell\AccessGate\Models\Event;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Contracts\Extensions\RegistersExtensionWidget;
use Capell\Core\Support\Manifest\ManifestValidator;

it('declares the shipped access gate package manifest surfaces', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = capell_json_file_array($packagePath . '/capell.json');
    $composer = capell_json_file_array($packagePath . '/composer.json');
    $contributions = data_get($manifest, 'contributes');

    throw_unless(is_array($contributions), RuntimeException::class, 'Expected Access Gate manifest contributions.');

    (new ManifestValidator)->validate($manifest, $composer, 'capell-app/access-gate', $packagePath . '/capell.json');

    expect($manifest)
        ->toHaveKey('manifest-version', 3)
        ->toHaveKey('name', 'capell-app/access-gate')
        ->toHaveKey('namespace', 'Capell\\AccessGate')
        ->and(data_get($manifest, 'surfaces', []))->toContain('admin', 'frontend', 'console')
        ->and(data_get($manifest, 'providers.runtime', []))->toContain('Capell\\AccessGate\\Providers\\AccessGateServiceProvider')
        ->and(data_get($manifest, 'capabilities', []))->toContain('paid-gated-access-checkout-creation', 'paid-gated-access-fulfillment')
        ->and(data_get($manifest, 'database.migrations'))->toBeTrue()
        ->and(data_get($manifest, 'commands.install'))->toBe('capell:access-gate-install')
        ->and(data_get($manifest, 'commands.setup'))->toBe('capell:access-gate-setup')
        ->and(data_get($manifest, 'commands.doctor'))->toBe('capell:access-gate-doctor')
        ->and(data_get($manifest, 'commands.prune'))->toBe('capell:access-gate-prune')
        ->and(data_get($manifest, 'healthChecks.0.class'))->toBe(AccessGateHealthCheck::class)
        ->and(data_get($manifest, 'security.publicSurface.routeNames', []))->toBe([
            'capell-access-gate.claim',
            'capell-access-gate.logout',
            'capell-access-gate.request',
            'capell-access-gate.request.store',
            'capell-access-gate.status',
        ])
        ->and(data_get($manifest, 'security.publicSurface.tokenizedRoutes', []))->toBe([
            'capell-access-gate.claim',
        ])
        ->and(data_get($manifest, 'actions.paidAccessCheckout'))->toBe('Capell\\AccessGate\\Actions\\CreatePaidAccessCheckoutForRegistrationAction')
        ->and(data_get($manifest, 'actions.paidAccessCheckoutContribution'))->toBe(PaidAccessCheckoutCreationContribution::class)
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([]);

    expect($contributions)
        ->toContain([
            'type' => 'admin-resource',
            'class' => AccessAreaResourceContribution::class,
            'resourceClass' => AccessAreaResource::class,
        ])
        ->toContain([
            'type' => 'admin-resource',
            'class' => RegistrationResourceContribution::class,
            'resourceClass' => RegistrationResource::class,
        ])
        ->toContain([
            'type' => 'admin-resource',
            'class' => GrantResourceContribution::class,
            'resourceClass' => GrantResource::class,
        ])
        ->toContain([
            'type' => 'admin-resource',
            'class' => BrowserTokenResourceContribution::class,
            'resourceClass' => BrowserTokenResource::class,
        ])
        ->toContain([
            'type' => 'admin-resource',
            'class' => ClaimTokenResourceContribution::class,
            'resourceClass' => ClaimTokenResource::class,
        ])
        ->toContain([
            'type' => 'admin-resource',
            'class' => AccessGateEventResourceContribution::class,
            'resourceClass' => AccessGateEventResource::class,
        ])
        ->toContain([
            'type' => 'dashboard-widget',
            'class' => PendingAccessRequestsWidgetContribution::class,
            'widgetClass' => PendingAccessRequestsWidget::class,
            'labelKey' => 'capell-access-gate::filament.widgets.pending_access_requests',
        ])
        ->toContain([
            'type' => 'model',
            'class' => AccessGateModelsContribution::class,
            'modelClasses' => [
                Area::class,
                Registration::class,
                Grant::class,
                ClaimToken::class,
                BrowserToken::class,
                Event::class,
            ],
        ])
        ->toContain([
            'type' => 'route',
            'class' => AccessGateRoutesContribution::class,
            'routes' => [
                'capell-access-gate.request',
                'capell-access-gate.request.store',
                'capell-access-gate.claim',
                'capell-access-gate.logout',
                'capell-access-gate.status',
            ],
        ])
        ->toContain([
            'type' => 'console-command',
            'class' => AccessGateConsoleCommandsContribution::class,
            'commands' => [
                'capell:access-gate-install',
                'capell:access-gate-setup',
                'capell:access-gate-doctor',
                'capell:access-gate-prune',
            ],
            'commandClasses' => [
                AccessGateInstallCommand::class,
                AccessGateSetupCommand::class,
                AccessGateDoctorCommand::class,
                AccessGatePruneCommand::class,
            ],
        ])
        ->toContain([
            'type' => 'health-check',
            'class' => AccessGateHealthContribution::class,
            'checkClass' => AccessGateHealthCheck::class,
        ]);

    foreach ($contributions as $contribution) {
        throw_unless(is_array($contribution), RuntimeException::class, 'Expected Access Gate manifest contribution to be an array.');

        $class = $contribution['class'] ?? null;

        expect(is_string($class) ? class_implements($class) : [])->not->toBeEmpty();
    }

    expect(class_implements(AccessAreaResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(RegistrationResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(GrantResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(BrowserTokenResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(ClaimTokenResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(AccessGateEventResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(PendingAccessRequestsWidgetContribution::class))->toContain(RegistersExtensionWidget::class)
        ->and(class_implements(AccessGateRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(AccessGateConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(AccessGateModelsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(PaidAccessCheckoutCreationContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(AccessGateHealthContribution::class))->toContain(ChecksExtensionHealth::class);

    expect(__('capell-access-gate::filament.widgets.pending_access_requests'))->toBe('Pending access requests');
});
