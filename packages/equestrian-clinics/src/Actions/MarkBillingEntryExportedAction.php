<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianBillingEntryStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianBillingEntry;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianBillingEntry run(EquestrianBillingEntry $billingEntry, string $invoiceReference, ?CarbonImmutable $exportedAt = null, EquestrianBillingEntryStatusEnum $status = EquestrianBillingEntryStatusEnum::Invoiced)
 */
final class MarkBillingEntryExportedAction
{
    use AsAction;

    public function handle(
        EquestrianBillingEntry $billingEntry,
        string $invoiceReference,
        ?CarbonImmutable $exportedAt = null,
        EquestrianBillingEntryStatusEnum $status = EquestrianBillingEntryStatusEnum::Invoiced,
    ): EquestrianBillingEntry {
        $exportedAt ??= CarbonImmutable::now();

        $billingEntry->forceFill([
            'status' => $status,
            'invoice_reference' => $invoiceReference,
            'exported_at' => $exportedAt,
        ])->save();

        return $billingEntry->refresh();
    }
}
