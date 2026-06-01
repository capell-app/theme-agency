<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Experiments\Enums\ResourceEnum;
use Capell\Experiments\Filament\Resources\ExperimentAudienceRules\ExperimentAudienceRuleResource;
use Capell\Experiments\Filament\Resources\ExperimentGoals\ExperimentGoalResource;
use Capell\Experiments\Filament\Resources\Experiments\ExperimentResource;
use Capell\Experiments\Filament\Resources\ExperimentVariants\ExperimentVariantResource;
use Capell\Experiments\Manifest\ExperimentAudienceRuleResourceContribution;
use Capell\Experiments\Manifest\ExperimentGoalResourceContribution;
use Capell\Experiments\Manifest\ExperimentResourceContribution;
use Capell\Experiments\Manifest\ExperimentVariantResourceContribution;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentAudienceRule;
use Capell\Experiments\Models\ExperimentGoal;
use Capell\Experiments\Models\ExperimentVariant;

it('declares admin resources for experiment setup records', function (): void {
    expect(ResourceEnum::cases())->toHaveCount(4)
        ->and(ExperimentResource::getModel())->toBe(Experiment::class)
        ->and(ExperimentVariantResource::getModel())->toBe(ExperimentVariant::class)
        ->and(ExperimentGoalResource::getModel())->toBe(ExperimentGoal::class)
        ->and(ExperimentAudienceRuleResource::getModel())->toBe(ExperimentAudienceRule::class)
        ->and(ExperimentResource::getNavigationLabel())->toBe(__('capell-experiments::generic.resources.experiments'))
        ->and(ExperimentVariantResource::getNavigationLabel())->toBe(__('capell-experiments::generic.resources.variants'))
        ->and(ExperimentGoalResource::getNavigationLabel())->toBe(__('capell-experiments::generic.resources.goals'))
        ->and(ExperimentAudienceRuleResource::getNavigationLabel())->toBe(__('capell-experiments::generic.resources.audience_rules'));
});

it('exposes list create edit pages for experiment setup resources', function (): void {
    expect(array_keys(ExperimentResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(ExperimentVariantResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(ExperimentGoalResource::getPages()))->toBe(['index', 'create', 'edit'])
        ->and(array_keys(ExperimentAudienceRuleResource::getPages()))->toBe(['index', 'create', 'edit']);
});

it('declares admin manifest contributions and package requirements', function (): void {
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

    expect($manifest['dependencies']['requires'])->toContain('capell-app/admin', 'capell-app/core')
        ->and(array_keys($composer['require'] ?? []))->toContain('capell-app/admin', 'capell-app/core', 'filament/filament')
        ->and($manifest['capabilities'])->toContain('experiments-admin')
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => ExperimentResourceContribution::class,
            'resourceClass' => ExperimentResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => ExperimentVariantResourceContribution::class,
            'resourceClass' => ExperimentVariantResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => ExperimentGoalResourceContribution::class,
            'resourceClass' => ExperimentGoalResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => ExperimentAudienceRuleResourceContribution::class,
            'resourceClass' => ExperimentAudienceRuleResource::class,
        ])
        ->and(class_implements(ExperimentResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(ExperimentVariantResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(ExperimentGoalResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(ExperimentAudienceRuleResourceContribution::class))->toContain(RegistersExtensionAdminResource::class);
});
