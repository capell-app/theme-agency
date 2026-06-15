<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianBillingEntryStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianBillingEntry;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianBillingEntry run(string $label, int $amountPence, ?Model $source = null, ?int $portalAccountId = null, ?int $siteId = null, EquestrianBillingEntryStatusEnum $status = EquestrianBillingEntryStatusEnum::Draft, ?string $invoiceReference = null, ?array<string, mixed> $meta = null)
 */
final class CreateBillingEntryAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        string $label,
        int $amountPence,
        ?Model $source = null,
        ?int $portalAccountId = null,
        ?int $siteId = null,
        EquestrianBillingEntryStatusEnum $status = EquestrianBillingEntryStatusEnum::Draft,
        ?string $invoiceReference = null,
        ?array $meta = null,
    ): EquestrianBillingEntry {
        return EquestrianBillingEntry::query()->create([
            'site_id' => $siteId,
            'portal_account_id' => $portalAccountId,
            'source_type' => $source instanceof Model ? $source->getMorphClass() : null,
            'source_id' => $source instanceof Model ? $source->getKey() : null,
            'status' => $status,
            'label' => $label,
            'amount_pence' => $amountPence,
            'invoice_reference' => $invoiceReference,
            'meta' => $meta,
        ]);
    }
}
