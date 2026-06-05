<?php

declare(strict_types=1);

namespace Capell\Experiments\Actions;

use Capell\Experiments\Data\ExperimentContextData;
use Capell\Experiments\Data\ResolvedExperimentVariantData;
use Capell\Experiments\Data\VariantAllocationData;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentVariant;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveExperimentVariantForContextAction
{
    use AsAction;

    private const string RECORD_EXTENSION_RENDER_CONTRIBUTION_ACTION = 'Capell\\Frontend\\Actions\\Performance\\RecordExtensionRenderContributionAction';

    public function handle(string $allocationKey, ?ExperimentContextData $context = null): ?ResolvedExperimentVariantData
    {
        $startedAt = microtime(true);
        $context ??= new ExperimentContextData;

        /** @var iterable<int, Experiment> $experiments */
        $experiments = $this->candidateQuery($context)->cursor();

        foreach ($experiments as $experiment) {
            $allocation = AllocateVariantAction::run($experiment, $allocationKey, $context);

            if (! $allocation instanceof VariantAllocationData) {
                continue;
            }

            $resolved = $this->resolvedData($experiment, $allocation);
            $this->recordFrontendCacheSafetyContribution($resolved, $startedAt);

            return $resolved;
        }

        return null;
    }

    /**
     * @return Builder<Experiment>
     */
    private function candidateQuery(ExperimentContextData $context): Builder
    {
        return Experiment::query()
            ->where('status', ExperimentStatus::Active)
            ->when($context->siteId !== null, function (Builder $query) use ($context): void {
                $query->where(function (Builder $query) use ($context): void {
                    $query->whereNull('site_id')
                        ->orWhere('site_id', $context->siteId);
                });
            })
            ->when($context->subjectType !== null, function (Builder $query) use ($context): void {
                $query->where('subject_type', $context->subjectType);
            })
            ->when($context->subjectClass !== null, function (Builder $query) use ($context): void {
                $query->where(function (Builder $query) use ($context): void {
                    $query->whereNull('subject_class')
                        ->orWhere('subject_class', $context->subjectClass);
                });
            })
            ->when($context->subjectId !== null, function (Builder $query) use ($context): void {
                $query->where(function (Builder $query) use ($context): void {
                    $query->whereNull('subject_id')
                        ->orWhere('subject_id', $context->subjectId);
                });
            })
            ->orderByRaw('site_id is not null desc')
            ->orderByRaw('subject_id is not null desc')
            ->orderBy('id');
    }

    private function resolvedData(Experiment $experiment, VariantAllocationData $allocation): ResolvedExperimentVariantData
    {
        /** @var ExperimentVariant $variant */
        $variant = $experiment
            ->variants()
            ->whereKey($allocation->variantId)
            ->firstOrFail();

        return new ResolvedExperimentVariantData(
            experimentId: $experiment->id,
            experimentKey: $experiment->key,
            variantId: $variant->id,
            variantKey: $variant->key,
            allocationKey: $allocation->allocationKey,
            isNewAllocation: $allocation->isNewAllocation,
            cacheVariationKey: sprintf('experiment:%s:%s', $experiment->key, $variant->key),
            cacheVaryBy: [
                'experiment' => $experiment->key,
                'variant' => $variant->key,
            ],
            variantPayload: $variant->payload ?? [],
        );
    }

    private function recordFrontendCacheSafetyContribution(ResolvedExperimentVariantData $resolved, float $startedAt): void
    {
        if (! class_exists(self::RECORD_EXTENSION_RENDER_CONTRIBUTION_ACTION)) {
            return;
        }

        $actionClass = self::RECORD_EXTENSION_RENDER_CONTRIBUTION_ACTION;

        $actionClass::run(
            packageName: 'capell-app/experiments',
            surface: 'frontend',
            contributionType: 'experiment-variant-resolution',
            contributionClass: self::class,
            elapsedMilliseconds: (microtime(true) - $startedAt) * 1000,
            frontendRenderBudgetMs: 20,
            cacheTags: [
                'experiment-' . $resolved->experimentKey,
                'experiment-variant-' . $resolved->variantKey,
            ],
            cacheable: false,
            sensitiveOutput: false,
            variesBy: [
                'visitor',
                'experiment',
                'variant',
            ],
        );
    }
}
