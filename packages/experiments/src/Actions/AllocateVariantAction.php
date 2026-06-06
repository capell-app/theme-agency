<?php

declare(strict_types=1);

namespace Capell\Experiments\Actions;

use Capell\Experiments\Data\ExperimentContextData;
use Capell\Experiments\Data\VariantAllocationData;
use Capell\Experiments\Enums\AllocationStrategy;
use Capell\Experiments\Enums\ExperimentStatus;
use Capell\Experiments\Models\Experiment;
use Capell\Experiments\Models\ExperimentAllocation;
use Capell\Experiments\Models\ExperimentVariant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

final class AllocateVariantAction
{
    use AsAction;

    public function handle(Experiment $experiment, string $allocationKey, ?ExperimentContextData $context = null): ?VariantAllocationData
    {
        $context ??= new ExperimentContextData;

        if ($experiment->allocation_strategy === AllocationStrategy::StickyWeighted) {
            $existingAllocation = $this->existingStickyAllocation($experiment, $allocationKey);

            if ($existingAllocation instanceof ExperimentAllocation) {
                return new VariantAllocationData(
                    experimentId: $experiment->id,
                    variantId: $existingAllocation->experiment_variant_id,
                    variantKey: $existingAllocation->variant->key,
                    allocationKey: $allocationKey,
                    isNewAllocation: false,
                    allocationId: $existingAllocation->id,
                );
            }
        }

        if (! $this->canAllocate($experiment, $allocationKey, $context)) {
            return null;
        }

        $variants = $this->activeWeightedVariants($experiment);

        if ($variants->isEmpty()) {
            return null;
        }

        $variant = $this->chooseVariant($experiment, $variants, $allocationKey);

        return $this->persistAllocation($experiment, $variant, $allocationKey, $context);
    }

    /**
     * @return Collection<int, ExperimentVariant>
     */
    private function activeWeightedVariants(Experiment $experiment): Collection
    {
        if ($experiment->relationLoaded('variants')) {
            /** @var Collection<int, ExperimentVariant> $variants */
            $variants = $experiment->variants
                ->filter(fn (ExperimentVariant $variant): bool => $variant->is_active && $variant->weight > 0)
                ->sortBy('sort_order')
                ->values();

            return $variants;
        }

        /** @var Collection<int, ExperimentVariant> $variants */
        $variants = $experiment
            ->variants()
            ->where('is_active', true)
            ->where('weight', '>', 0)
            ->orderBy('sort_order')
            ->get();

        return $variants;
    }

    private function existingStickyAllocation(Experiment $experiment, string $allocationKey): ?ExperimentAllocation
    {
        /** @var ExperimentAllocation|null $existingAllocation */
        $existingAllocation = $experiment
            ->allocations()
            ->with('variant')
            ->where('allocation_hash', $this->stickyAllocationHash($allocationKey))
            ->first();

        return $existingAllocation;
    }

    private function persistAllocation(
        Experiment $experiment,
        ExperimentVariant $variant,
        string $allocationKey,
        ExperimentContextData $context,
    ): VariantAllocationData {
        return DB::transaction(function () use ($experiment, $variant, $allocationKey, $context): VariantAllocationData {
            $allocation = ExperimentAllocation::query()->create([
                'experiment_id' => $experiment->id,
                'experiment_variant_id' => $variant->id,
                'allocation_key' => $allocationKey,
                'allocation_hash' => $this->allocationHash($experiment, $allocationKey),
                'source' => $context->source,
                'external_id' => $context->externalId,
                'allocated_at' => CarbonImmutable::now(),
                'context' => $context->toArray(),
            ]);

            return new VariantAllocationData(
                experimentId: $experiment->id,
                variantId: $allocation->experiment_variant_id,
                variantKey: $variant->key,
                allocationKey: $allocationKey,
                isNewAllocation: true,
                allocationId: $allocation->id,
            );
        });
    }

    private function canAllocate(Experiment $experiment, string $allocationKey, ExperimentContextData $context): bool
    {
        $now = CarbonImmutable::now();

        if ($experiment->status !== ExperimentStatus::Active) {
            return false;
        }

        if ($experiment->starts_at !== null && $experiment->starts_at->isAfter($now)) {
            return false;
        }

        if ($experiment->ends_at !== null && $experiment->ends_at->isBefore($now)) {
            return false;
        }

        if (! EvaluateAudienceRulesAction::run($experiment, $context)) {
            return false;
        }

        return $this->trafficBucket($experiment, $allocationKey) < $experiment->traffic_percentage;
    }

    /**
     * @param  Collection<int, ExperimentVariant>  $variants
     */
    private function chooseVariant(Experiment $experiment, Collection $variants, string $allocationKey): ExperimentVariant
    {
        $totalWeight = (int) $variants->sum('weight');
        $bucket = $this->weightedBucket($experiment, $allocationKey, $totalWeight);
        $runningWeight = 0;

        foreach ($variants as $variant) {
            $runningWeight += $variant->weight;

            if ($bucket < $runningWeight) {
                return $variant;
            }
        }

        $fallbackVariant = $variants->last();

        throw_unless($fallbackVariant instanceof ExperimentVariant, RuntimeException::class, 'Active experiment variant selection requires at least one weighted variant.');

        return $fallbackVariant;
    }

    private function trafficBucket(Experiment $experiment, string $allocationKey): int
    {
        return $this->stableNumber(sprintf('traffic:%d:%s', $experiment->id, $allocationKey)) % 100;
    }

    private function weightedBucket(Experiment $experiment, string $allocationKey, int $totalWeight): int
    {
        return $this->stableNumber(sprintf('variant:%d:%s', $experiment->id, $allocationKey)) % max(1, $totalWeight);
    }

    private function stableNumber(string $key): int
    {
        return (int) hexdec(substr(hash('sha256', $key), 0, 8));
    }

    private function allocationHash(Experiment $experiment, string $allocationKey): string
    {
        if ($experiment->allocation_strategy === AllocationStrategy::StickyWeighted) {
            return $this->stickyAllocationHash($allocationKey);
        }

        return hash('sha256', sprintf(
            '%s:%d:%s',
            $allocationKey,
            $experiment->id,
            Str::uuid()->toString(),
        ));
    }

    private function stickyAllocationHash(string $allocationKey): string
    {
        return hash('sha256', $allocationKey);
    }
}
