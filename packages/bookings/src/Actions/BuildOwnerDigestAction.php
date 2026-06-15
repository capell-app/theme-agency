<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<string, mixed> run(?int $siteId = null, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null)
 */
class BuildOwnerDigestAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(?int $siteId = null, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): array
    {
        $from ??= CarbonImmutable::now()->startOfDay();
        $to ??= $from->addDays(7);

        $query = AppointmentRequest::query()
            ->whereBetween('requested_starts_at', [$from, $to]);

        if ($siteId !== null) {
            $query->where('site_id', $siteId);
        }

        $counts = $query
            ->get()
            ->groupBy(static fn (AppointmentRequest $appointmentRequest): string => $appointmentRequest->status->value)
            ->map(static fn (Collection $group): int => $group->count())
            ->all();

        return [
            'site_id' => $siteId,
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'counts' => $counts,
            'needs_attention' => ($counts[AppointmentRequestStatusEnum::Requested->value] ?? 0) > 0,
        ];
    }
}
