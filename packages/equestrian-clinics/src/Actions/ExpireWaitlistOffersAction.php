<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianWaitlistStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianSlotWaitlistEntry;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(?CarbonImmutable $now = null)
 */
final class ExpireWaitlistOffersAction
{
    use AsAction;

    public function handle(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        return EquestrianSlotWaitlistEntry::query()
            ->where('status', EquestrianWaitlistStatusEnum::Offered)
            ->whereNotNull('offer_expires_at')
            ->where('offer_expires_at', '<=', $now)
            ->update([
                'status' => EquestrianWaitlistStatusEnum::Expired,
                'updated_at' => $now,
            ]);
    }
}
