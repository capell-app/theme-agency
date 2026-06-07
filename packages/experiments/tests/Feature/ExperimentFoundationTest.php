<?php

declare(strict_types=1);

use Capell\Frontend\Actions\Performance\RecordExtensionRenderContributionAction;

require_once __DIR__ . '/../Pest.php';

use Capell\Experiments\Actions\AllocateVariantAction;
use Capell\Experiments\Actions\BuildWinnerReportAction;
use Capell\Experiments\Actions\CreateExperimentAction;
use Capell\Experiments\Actions\DeclareExperimentWinnerAction;
use Capell\Experiments\Actions\RecordGoalEventAction;
use Capell\Experiments\Actions\ResolveExperimentVariantForContextAction;
use Capell\Experiments\Actions\SyncExperimentStatusesAction;
use Capell\Experiments\Data\ExperimentAudienceRuleData;
use Capell\Experiments\Data\ExperimentContextData;
use Capell\Experiments\Data\ExperimentData;
use Capell\Experiments\Data\ExperimentGoalData;
use Capell\Experiments\Data\ExperimentGoalEventData;
use Capell\Experiments\Data\ExperimentVariantData;
use Capell\Experiments\Enums\AllocationStrategy;
use Capell\Experiments\Enums\AudienceOperator;
use Capell\Experiments\Enums\AudienceRuleType;
use Capell\Experiments\Enums\ExperimentGoalType;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Enums\ExperimentSubjectType;
use Capell\Experiments\Models\ExperimentAllocation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        ->and($report->winningVariantId)->toBeNull()
        ->and($report->isStatisticallySignificant)->toBeFalse()
        ->and($report->variants)->toHaveCount(2);
});

it('records keyed goal events once per allocation and goal', function (): void {
    $experiment = CreateExperimentAction::run(new ExperimentData(
        name: 'Idempotent goal event test',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
        goals: [
            new ExperimentGoalData(name: 'Signup', key: 'signup', type: ExperimentGoalType::CustomEvent, isPrimary: true),
        ],
    ));
    $allocation = ExperimentAllocation::query()->create([
        'experiment_id' => $experiment->getKey(),
        'experiment_variant_id' => $experiment->variants()->firstOrFail()->getKey(),
        'allocation_key' => 'visitor-idempotent',
        'allocation_hash' => hash('sha256', 'visitor-idempotent'),
        'allocated_at' => now(),
    ]);
    $goal = $experiment->goals()->firstOrFail();

    $firstEvent = RecordGoalEventAction::run(
        allocation: $allocation,
        goal: $goal,
        data: new ExperimentGoalEventData(eventKey: 'signup', valueAmount: '25.00', metadata: ['source' => 'first']),
    );
    $secondEvent = RecordGoalEventAction::run(
        allocation: $allocation,
        goal: $goal,
        data: new ExperimentGoalEventData(eventKey: 'signup', valueAmount: '99.00', metadata: ['source' => 'duplicate']),
    );
    $keylessEvent = RecordGoalEventAction::run(
        allocation: $allocation,
        goal: $goal,
        data: new ExperimentGoalEventData(valueAmount: '50.00'),
    );

    expect($secondEvent->is($firstEvent))->toBeTrue()
        ->and($keylessEvent->is($firstEvent))->toBeFalse()
        ->and($goal->events()->count())->toBe(2)
        ->and($secondEvent->value_amount)->toBe('25.00')
        ->and($secondEvent->metadata)->toBe(['source' => 'first']);
});

it('syncs scheduled and expired experiment statuses', function (): void {
    $now = CarbonImmutable::parse('2026-06-07 12:00:00', 'UTC');
    $scheduled = CreateExperimentAction::run(new ExperimentData(
        name: 'Scheduled experiment',
        status: ExperimentStatus::Scheduled,
        startsAt: $now->subMinute(),
        endsAt: $now->addDay(),
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
    ));
    $expiredActive = CreateExperimentAction::run(new ExperimentData(
        name: 'Expired active experiment',
        status: ExperimentStatus::Active,
        startsAt: $now->subDays(2),
        endsAt: $now->subMinute(),
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
    ));
    $expiredScheduled = CreateExperimentAction::run(new ExperimentData(
        name: 'Expired scheduled experiment',
        status: ExperimentStatus::Scheduled,
        startsAt: $now->subDays(2),
        endsAt: $now->subMinute(),
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
    ));
    $futureScheduled = CreateExperimentAction::run(new ExperimentData(
        name: 'Future scheduled experiment',
        status: ExperimentStatus::Scheduled,
        startsAt: $now->addHour(),
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
    ));

    $result = SyncExperimentStatusesAction::run($now);

    expect($result->scheduledToActive)->toBe(1)
        ->and($result->expiredToEnded)->toBe(2)
        ->and($scheduled->refresh()->status)->toBe(ExperimentStatus::Active)
        ->and($expiredActive->refresh()->status)->toBe(ExperimentStatus::Ended)
        ->and($expiredScheduled->refresh()->status)->toBe(ExperimentStatus::Ended)
        ->and($futureScheduled->refresh()->status)->toBe(ExperimentStatus::Scheduled);
});

it('honours weighted allocation strategy without reusing a sticky visitor row', function (): void {
    $experiment = CreateExperimentAction::run(new ExperimentData(
        name: 'Weighted allocation test',
        status: ExperimentStatus::Active,
        allocationStrategy: AllocationStrategy::Weighted,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', weight: 0, isControl: true),
            new ExperimentVariantData(name: 'Variant', key: 'variant', weight: 100),
        ],
    ));

    $firstAllocation = AllocateVariantAction::run($experiment, 'visitor-123');
    $secondAllocation = AllocateVariantAction::run($experiment, 'visitor-123');

    expect($firstAllocation)->not->toBeNull()
        ->and($secondAllocation)->not->toBeNull()
        ->and($firstAllocation?->isNewAllocation)->toBeTrue()
        ->and($secondAllocation?->isNewAllocation)->toBeTrue()
        ->and($firstAllocation?->variantKey)->toBe('variant')
        ->and($secondAllocation?->variantKey)->toBe('variant')
        ->and($experiment->allocations()->count())->toBe(2);
});

it('declares a statistically significant winning variant and ends the experiment', function (): void {
    $experiment = CreateExperimentAction::run(new ExperimentData(
        name: 'Signup form CTA test',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
            new ExperimentVariantData(name: 'Benefit CTA', key: 'benefit-cta'),
        ],
        goals: [
            new ExperimentGoalData(name: 'Signup', key: 'signup', type: ExperimentGoalType::CustomEvent, isPrimary: true),
        ],
    ));
    $goal = $experiment->goals()->firstOrFail();
    $controlVariant = $experiment->variants()->where('key', 'control')->firstOrFail();
    $benefitVariant = $experiment->variants()->where('key', 'benefit-cta')->firstOrFail();

    foreach (range(1, 100) as $visitorIndex) {
        $controlAllocation = ExperimentAllocation::query()->create([
            'experiment_id' => $experiment->getKey(),
            'experiment_variant_id' => $controlVariant->getKey(),
            'allocation_key' => 'visitor-control-' . $visitorIndex,
            'allocation_hash' => hash('sha256', 'visitor-control-' . $visitorIndex),
            'allocated_at' => now(),
        ]);
        $benefitAllocation = ExperimentAllocation::query()->create([
            'experiment_id' => $experiment->getKey(),
            'experiment_variant_id' => $benefitVariant->getKey(),
            'allocation_key' => 'visitor-benefit-' . $visitorIndex,
            'allocation_hash' => hash('sha256', 'visitor-benefit-' . $visitorIndex),
            'allocated_at' => now(),
        ]);

        if ($visitorIndex <= 10) {
            RecordGoalEventAction::run($controlAllocation, $goal, new ExperimentGoalEventData(eventKey: 'signup'));
        }

        if ($visitorIndex <= 30) {
            RecordGoalEventAction::run($benefitAllocation, $goal, new ExperimentGoalEventData(eventKey: 'signup'));
        }
    }

    $report = BuildWinnerReportAction::run($experiment, $goal);

    expect($report->isStatisticallySignificant)->toBeTrue()
        ->and($report->winningVariantId)->toBe($benefitVariant->getKey())
        ->and($report->minimumSampleSize)->toBe(100)
        ->and($report->confidenceLevel)->toBe(0.95);

    $declaredAt = CarbonImmutable::parse('2026-06-01 10:00:00', 'UTC');
    $experiment = DeclareExperimentWinnerAction::run($experiment, $goal, $declaredAt);

    expect($experiment->status)->toBe(ExperimentStatus::Ended)
        ->and($experiment->winning_variant_id)->toBe($benefitVariant->getKey())
        ->and($experiment->winner_declared_at?->toIso8601String())->toBe('2026-06-01T10:00:00+00:00')
        ->and($experiment->ends_at?->toIso8601String())->toBe('2026-06-01T10:00:00+00:00')
        ->and($experiment->metadata['winner_report']['winning_variant_id'])->toBe($benefitVariant->getKey())
        ->and($experiment->metadata['winner_report']['is_statistically_significant'])->toBeTrue();
});

it('does not declare a winner without allocation data', function (): void {
    $experiment = CreateExperimentAction::run(new ExperimentData(
        name: 'Empty experiment',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
    ));

    DeclareExperimentWinnerAction::run($experiment);
})->throws(ValidationException::class);

it('does not declare a raw conversion winner before the sample floor is met', function (): void {
    $experiment = CreateExperimentAction::run(new ExperimentData(
        name: 'Early winner experiment',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
            new ExperimentVariantData(name: 'Benefit CTA', key: 'benefit-cta'),
        ],
        goals: [
            new ExperimentGoalData(name: 'Signup', key: 'signup', type: ExperimentGoalType::CustomEvent, isPrimary: true),
        ],
    ));
    $goal = $experiment->goals()->firstOrFail();
    $controlVariant = $experiment->variants()->where('key', 'control')->firstOrFail();
    $benefitVariant = $experiment->variants()->where('key', 'benefit-cta')->firstOrFail();
    ExperimentAllocation::query()->create([
        'experiment_id' => $experiment->getKey(),
        'experiment_variant_id' => $controlVariant->getKey(),
        'allocation_key' => 'visitor-control',
        'allocation_hash' => hash('sha256', 'visitor-control'),
        'allocated_at' => now(),
    ]);
    $benefitAllocation = ExperimentAllocation::query()->create([
        'experiment_id' => $experiment->getKey(),
        'experiment_variant_id' => $benefitVariant->getKey(),
        'allocation_key' => 'visitor-benefit',
        'allocation_hash' => hash('sha256', 'visitor-benefit'),
        'allocated_at' => now(),
    ]);

    RecordGoalEventAction::run($benefitAllocation, $goal, new ExperimentGoalEventData(eventKey: 'signup'));

    $report = BuildWinnerReportAction::run($experiment, $goal);

    expect($report->totalAllocations)->toBe(2)
        ->and($report->totalConversions)->toBe(1)
        ->and($report->winningVariantId)->toBeNull()
        ->and($report->isStatisticallySignificant)->toBeFalse();

    DeclareExperimentWinnerAction::run($experiment, $goal);
})->throws(ValidationException::class);

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

it('resolves an active request context variant with cache variation metadata', function (): void {
    $recordContributionAction = RecordExtensionRenderContributionAction::class;

    if (! class_exists($recordContributionAction)) {
        test()->markTestSkipped('Capell Frontend render contribution recording is not available.');
    }

    resolve($recordContributionAction)->clear();

    CreateExperimentAction::run(new ExperimentData(
        name: 'Pricing hero test',
        key: 'pricing-hero-test',
        siteId: 12,
        status: ExperimentStatus::Active,
        subjectType: ExperimentSubjectType::Page,
        subjectId: 42,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', weight: 0, isControl: true),
            new ExperimentVariantData(name: 'Benefit lead', key: 'benefit-lead', payload: ['headline' => 'Ship faster']),
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

    $context = new ExperimentContextData(
        siteId: 12,
        subjectType: 'page',
        subjectId: 42,
        source: 'insights',
        externalId: 'visit-123',
        path: '/pricing/pro',
    );

    $firstResolution = ResolveExperimentVariantForContextAction::run('visitor-123', $context);
    $secondResolution = ResolveExperimentVariantForContextAction::run('visitor-123', $context);
    $contribution = collect(resolve($recordContributionAction)->recorded())
        ->first(fn (mixed $record): bool => $record->contributionType === 'experiment-variant-resolution');

    expect($firstResolution)->not->toBeNull()
        ->and($firstResolution?->experimentKey)->toBe('pricing-hero-test')
        ->and($firstResolution?->variantKey)->toBe('benefit-lead')
        ->and($firstResolution?->variantPayload)->toBe(['headline' => 'Ship faster'])
        ->and($firstResolution?->cacheVariationKey)->toBe('experiment:pricing-hero-test:benefit-lead')
        ->and($firstResolution?->cacheVaryBy)->toBe([
            'experiment' => 'pricing-hero-test',
            'variant' => 'benefit-lead',
        ])
        ->and($firstResolution?->isNewAllocation)->toBeTrue()
        ->and($secondResolution?->isNewAllocation)->toBeFalse()
        ->and($secondResolution?->variantId)->toBe($firstResolution?->variantId)
        ->and($contribution?->packageName)->toBe('capell-app/experiments')
        ->and($contribution?->cacheable)->toBeFalse()
        ->and($contribution?->sensitiveOutput)->toBeFalse()
        ->and($contribution?->variesBy)->toBe(['visitor', 'experiment', 'variant'])
        ->and($contribution?->cacheTags)->toContain('experiment-pricing-hero-test', 'experiment-variant-benefit-lead');
});

it('returns no resolved variant when request context misses active experiments', function (): void {
    CreateExperimentAction::run(new ExperimentData(
        name: 'Pricing page test',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control'),
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

    $resolution = ResolveExperimentVariantForContextAction::run(
        allocationKey: 'visitor-456',
        context: new ExperimentContextData(path: '/blog'),
    );

    expect($resolution)->toBeNull();
});

it('bounds request-context candidate resolution and ignores inactive variant-only experiments', function (): void {
    Config::set('capell-experiments.resolution_candidate_limit', 1);

    CreateExperimentAction::run(new ExperimentData(
        name: 'Inactive variant experiment',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', weight: 0, isControl: true),
        ],
    ));
    CreateExperimentAction::run(new ExperimentData(
        name: 'First candidate experiment',
        key: 'first-candidate',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
    ));
    CreateExperimentAction::run(new ExperimentData(
        name: 'Beyond limit experiment',
        key: 'beyond-limit',
        status: ExperimentStatus::Active,
        variants: [
            new ExperimentVariantData(name: 'Control', key: 'control', isControl: true),
        ],
    ));

    DB::flushQueryLog();
    DB::enableQueryLog();

    $resolution = ResolveExperimentVariantForContextAction::run('visitor-bounded');
    $queryCount = count(DB::getQueryLog());

    DB::disableQueryLog();

    expect($resolution)->not->toBeNull()
        ->and($resolution?->experimentKey)->toBe('first-candidate')
        ->and($queryCount)->toBeLessThanOrEqual(5);
});
