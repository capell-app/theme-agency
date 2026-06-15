<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Data\EquestrianSlotTemplateData;
use Capell\EquestrianClinics\Models\EquestrianTourDay;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, EquestrianTourDaySlot> run(EquestrianTourDay $tourDay, EquestrianSlotTemplateData $template)
 */
final class GenerateTourDaySlotsAction
{
    use AsAction;

    /**
     * @return Collection<int, EquestrianTourDaySlot>
     */
    public function handle(EquestrianTourDay $tourDay, EquestrianSlotTemplateData $template): Collection
    {
        return DB::transaction(function () use ($tourDay, $template): Collection {
            $createdSlots = collect();
            $cursor = CarbonImmutable::instance($tourDay->starts_at);
            $tourDayEnd = CarbonImmutable::instance($tourDay->ends_at);

            while ($cursor->addMinutes($template->durationMinutes)->lessThanOrEqualTo($tourDayEnd)) {
                $slotEnd = $cursor->addMinutes($template->durationMinutes);

                /** @var EquestrianTourDaySlot $slot */
                $slot = $tourDay->slots()->create([
                    'title' => $template->title,
                    'archetype' => $template->archetype,
                    'starts_at' => $cursor,
                    'ends_at' => $slotEnd,
                    'capacity_min' => $template->capacityMin,
                    'capacity_max' => $template->capacityMax,
                    'skill_tier' => $template->skillTier,
                    'price_pence' => $template->pricePence,
                    'deposit_pence' => $template->depositPence,
                ]);

                $createdSlots->push($slot);
                $cursor = $slotEnd->addMinutes($template->gapMinutes);
            }

            return $createdSlots;
        });
    }
}
