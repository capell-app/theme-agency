<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\Experiments\Actions\AllocateVariantAction;
use Capell\Experiments\Actions\BuildWinnerReportAction;
use Capell\Experiments\Actions\CreateExperimentAction;
use Capell\Experiments\Actions\RecordGoalEventAction;
use Capell\Experiments\Data\ExperimentAudienceRuleData;
use Capell\Experiments\Data\ExperimentContextData;
use Capell\Experiments\Data\ExperimentData;
use Capell\Experiments\Data\ExperimentGoalData;
use Capell\Experiments\Data\ExperimentGoalEventData;
use Capell\Experiments\Data\ExperimentVariantData;
use Capell\Experiments\Enums\AudienceOperator;
use Capell\Experiments\Enums\AudienceRuleType;
use Capell\Experiments\Enums\ExperimentGoalType;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Enums\ExperimentSubjectType;
use Capell\Experiments\Models\ExperimentAllocation;

it('creates an experiment aggregate with variants goals and audience rules', function (): void {
    $experiment = CreateExperimentAction::run(new ExperimentData(
        name: 'Pricing hero test',
        status: ExperimentStatus::Active,
        subjectType: ExperimentSubjectType::Page,
        subjectClass: 'page',
        subjectId: 42,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
            new ExperimentVariantData(name: 'Benefit lead', key: 'benefit-lead', weight: 150),
        ],
        goals: [
            new ExperimentGoalData(name: 'Signup', key: 'signup', type: ExperimentGoalType::FormSubmission, isPrimary: true),
        ],
        audienceRules: [
            new ExperimentAudienceRuleData(
                type: AudienceRuleType::Path,
                key: 'path',
                operator: AudienceOperator::StartsWith,
                value: '/pricing',
            ),
        ],
    ));

    expect($experiment->status)->toBe(ExperimentStatus::Active)
        ->and($experiment->subject_type)->toBe(ExperimentSubjectType::Page)
        ->and($experiment->variants()->count())->toBe(2)
        ->and($experiment->goals()->count())->toBe(1)
        ->and($experiment->audienceRules()->count())->toBe(1);
});

it('allocates sticky variants records goals and builds a winner report', function (): void {
    $experiment = CreateExperimentAction::run(new ExperimentData(
        name: 'Landing page CTA test',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
            new ExperimentVariantData(name: 'Short CTA', key: 'short-cta'),
        ],
        goals: [
            new ExperimentGoalData(name: 'Lead', key: 'lead', type: ExperimentGoalType::CustomEvent, isPrimary: true),
        ],
    ));

    $firstAllocation = AllocateVariantAction::run(
        experiment: $experiment,
        allocationKey: 'visitor-123',
        context: new ExperimentContextData(source: 'insights', externalId: 'visit-1', path: '/landing'),
    );
    $secondAllocation = AllocateVariantAction::run($experiment, 'visitor-123');

    expect($firstAllocation)->not->toBeNull()
        ->and($firstAllocation?->isNewAllocation)->toBeTrue()
        ->and($secondAllocation?->isNewAllocation)->toBeFalse()
        ->and($secondAllocation?->variantId)->toBe($firstAllocation?->variantId);

    $allocation = ExperimentAllocation::query()->firstOrFail();
    $goal = $experiment->goals()->firstOrFail();

    RecordGoalEventAction::run(
        allocation: $allocation,
        goal: $goal,
        data: new ExperimentGoalEventData(eventKey: 'lead', valueAmount: '25.00'),
    );

    $report = BuildWinnerReportAction::run($experiment, $goal);

    expect($report->totalAllocations)->toBe(1)
        ->and($report->totalConversions)->toBe(1)
        ->and($report->winningVariantId)->toBe($firstAllocation?->variantId)
        ->and($report->variants)->toHaveCount(2);
});

it('does not allocate visitors outside required audience rules', function (): void {
    $experiment = CreateExperimentAction::run(new ExperimentData(
        name: 'Pricing page test',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
        audienceRules: [
            new ExperimentAudienceRuleData(
                type: AudienceRuleType::Path,
                key: 'path',
                operator: AudienceOperator::StartsWith,
                value: '/pricing',
            ),
        ],
    ));

    $miss = AllocateVariantAction::run($experiment, 'visitor-456', new ExperimentContextData(path: '/blog'));
    $match = AllocateVariantAction::run($experiment, 'visitor-789', new ExperimentContextData(path: '/pricing'));

    expect($miss)->toBeNull()
        ->and($match)->not->toBeNull();
});
