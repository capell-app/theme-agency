<?php

declare(strict_types=1);

use Capell\AutomationStudio\Enums\ResourceEnum;
use Capell\AutomationStudio\Filament\Resources\AutomationRules\AutomationRuleResource;
use Capell\AutomationStudio\Filament\Resources\AutomationRuns\AutomationRunResource;
use Capell\AutomationStudio\Manifest\AutomationRuleResourceContribution;
use Capell\AutomationStudio\Manifest\AutomationRunResourceContribution;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Providers\AdminServiceProvider;
use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;

it('declares admin resources for automation rules and run history', function (): void {
    expect(ResourceEnum::cases())->toHaveCount(2)
        ->and(AutomationRuleResource::getModel())->toBe(AutomationRule::class)
        ->and(AutomationRunResource::getModel())->toBe(AutomationRun::class);
});

it('exposes mutable rule pages and read-only run pages', function (): void {
    expect(array_keys(AutomationRuleResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(AutomationRunResource::getPages()))->toBe(['index']);
});

it('declares admin manifest contributions and composer requirements', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );
    $composer = json_decode(
        (string) file_get_contents(__DIR__ . '/../../composer.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    $composerPackageRequirements = array_values(array_filter(
        array_keys($composer['require'] ?? []),
        static fn (int|string $packageName): bool => is_string($packageName) && str_starts_with($packageName, 'capell-app/'),
    ));

    sort($composerPackageRequirements);

    $manifestRequirements = $manifest['dependencies']['requires'] ?? [];
    sort($manifestRequirements);

    expect($manifestRequirements)->toBe($composerPackageRequirements)
        ->and($manifest['providers']['admin'])->toContain(AdminServiceProvider::class)
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => AutomationRuleResourceContribution::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => AutomationRunResourceContribution::class,
        ])
        ->and(class_implements(AutomationRuleResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(AutomationRunResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and($manifest['capabilities'])->toContain('automation-admin');
});
