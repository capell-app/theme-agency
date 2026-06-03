<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Experiments\Actions\CreateExperimentAction;
use Capell\Experiments\Actions\ResolveExperimentVariantForContextAction;
use Capell\Experiments\Data\ExperimentContextData;
use Capell\Experiments\Data\ExperimentData;
use Capell\Experiments\Data\ExperimentVariantData;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Enums\ExperimentSubjectType;

it('resolves the experiment matching the context subject class when type and id collide', function (): void {
    CreateExperimentAction::run(new ExperimentData(
        name: 'Page 42 hero test',
        key: 'page-42-hero',
        status: ExperimentStatus::Active,
        subjectType: ExperimentSubjectType::Generic,
        subjectClass: 'page',
        subjectId: 42,
        variants: [
            new ExperimentVariantData(name: 'Page variant', key: 'page-variant', isControl: true),
        ],
    ));
    CreateExperimentAction::run(new ExperimentData(
        name: 'Campaign 42 hero test',
        key: 'campaign-42-hero',
        status: ExperimentStatus::Active,
        subjectType: ExperimentSubjectType::Generic,
        subjectClass: 'campaign',
        subjectId: 42,
        variants: [
            new ExperimentVariantData(name: 'Campaign variant', key: 'campaign-variant', isControl: true),
        ],
    ));

    $campaignResolution = ResolveExperimentVariantForContextAction::run(
        allocationKey: 'visitor-1',
        context: new ExperimentContextData(
            subjectType: 'generic',
            subjectClass: 'campaign',
            subjectId: 42,
        ),
    );

    expect($campaignResolution)->not->toBeNull()
        ->and($campaignResolution?->experimentKey)->toBe('campaign-42-hero')
        ->and($campaignResolution?->variantKey)->toBe('campaign-variant');
});

it('does not exclude class-agnostic experiments when a subject class is supplied', function (): void {
    CreateExperimentAction::run(new ExperimentData(
        name: 'Class agnostic test',
        key: 'class-agnostic',
        status: ExperimentStatus::Active,
        subjectType: ExperimentSubjectType::Generic,
        subjectClass: null,
        subjectId: 42,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
    ));

    $resolution = ResolveExperimentVariantForContextAction::run(
        allocationKey: 'visitor-2',
        context: new ExperimentContextData(
            subjectType: 'generic',
            subjectClass: 'page',
            subjectId: 42,
        ),
    );

    expect($resolution)->not->toBeNull()
        ->and($resolution?->experimentKey)->toBe('class-agnostic');
});
