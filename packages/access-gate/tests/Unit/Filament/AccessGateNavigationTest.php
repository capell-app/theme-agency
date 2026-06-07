<?php

declare(strict_types=1);

use Capell\AccessGate\Filament\Resources\AccessAreas\AccessAreaResource;
use Capell\AccessGate\Filament\Resources\BrowserTokens\BrowserTokenResource;
use Capell\AccessGate\Filament\Resources\ClaimTokens\ClaimTokenResource;
use Capell\AccessGate\Filament\Resources\Events\AccessGateEventResource;
use Capell\AccessGate\Filament\Resources\Grants\GrantResource;
use Capell\AccessGate\Filament\Resources\Registrations\RegistrationResource;
use Capell\AccessGate\Manifest\AccessAreaResourceContribution;
use Capell\AccessGate\Manifest\AccessGateEventResourceContribution;
use Capell\AccessGate\Manifest\BrowserTokenResourceContribution;
use Capell\AccessGate\Manifest\ClaimTokenResourceContribution;
use Capell\AccessGate\Manifest\GrantResourceContribution;
use Capell\AccessGate\Manifest\RegistrationResourceContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;

it('keeps access gate contained under workspace navigation', function (): void {
    $navigationGroup = (string) __('capell-admin::navigation.group_workflow');
    $parentItem = (string) __('capell-access-gate::filament.navigation_group');

    expect(AccessAreaResource::getNavigationGroup())->toBe($navigationGroup)
        ->and(AccessAreaResource::getNavigationLabel())->toBe($parentItem)
        ->and(AccessAreaResource::getNavigationSort())->toBe(30)
        ->and(AccessAreaResource::getNavigationParentItem())->toBeNull()
        ->and(RegistrationResource::getNavigationGroup())->toBe($navigationGroup)
        ->and(RegistrationResource::getNavigationParentItem())->toBe($parentItem)
        ->and(RegistrationResource::getNavigationSort())->toBe(31)
        ->and(GrantResource::getNavigationParentItem())->toBe($parentItem)
        ->and(GrantResource::getNavigationSort())->toBe(32)
        ->and(ClaimTokenResource::getNavigationParentItem())->toBe($parentItem)
        ->and(ClaimTokenResource::getNavigationSort())->toBe(33)
        ->and(BrowserTokenResource::getNavigationParentItem())->toBe($parentItem)
        ->and(BrowserTokenResource::getNavigationSort())->toBe(34)
        ->and(AccessGateEventResource::getNavigationParentItem())->toBe($parentItem)
        ->and(AccessGateEventResource::getNavigationSort())->toBe(35);
});

it('declares access gate admin resources and payments support in the package manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
    $composer = json_decode(
        (string) file_get_contents(__DIR__ . '/../../../composer.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['dependencies']['requires'])->toContain('capell-app/admin', 'capell-app/core')
        ->and($manifest['dependencies']['supports'])->toContain('capell-app/customer-portal', 'capell-app/payments', 'capell-app/public-actions')
        ->and(array_keys($composer['require'] ?? []))->toContain('capell-app/admin', 'capell-app/core', 'filament/filament')
        ->and($manifest['capabilities'])->toContain('paid-gated-access-fulfillment')
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => AccessAreaResourceContribution::class,
            'resourceClass' => AccessAreaResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => RegistrationResourceContribution::class,
            'resourceClass' => RegistrationResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => GrantResourceContribution::class,
            'resourceClass' => GrantResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => BrowserTokenResourceContribution::class,
            'resourceClass' => BrowserTokenResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => ClaimTokenResourceContribution::class,
            'resourceClass' => ClaimTokenResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => AccessGateEventResourceContribution::class,
            'resourceClass' => AccessGateEventResource::class,
        ])
        ->and(class_implements(AccessAreaResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(RegistrationResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(GrantResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(BrowserTokenResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(ClaimTokenResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(AccessGateEventResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and($manifest['contributionTraceability']['deferredContributions'])->not->toContain('admin-resource', 'migration', 'model', 'route');
});
