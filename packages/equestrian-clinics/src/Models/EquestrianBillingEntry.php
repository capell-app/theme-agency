<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianBillingEntryStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property int|null $portal_account_id
 * @property string|null $source_type
 * @property int|null $source_id
 * @property EquestrianBillingEntryStatusEnum $status
 * @property string $label
 * @property int $amount_pence
 * @property string|null $invoice_reference
 * @property CarbonImmutable|null $exported_at
 * @property array<string, mixed>|null $meta
 */
final class EquestrianBillingEntry extends Model
{
    protected $table = 'equestrian_billing_entries';

    protected $guarded = [];

    #[Override]
    protected function casts(): array
    {
        return [
            'amount_pence' => 'integer',
            'exported_at' => 'immutable_datetime',
            'meta' => 'json',
            'status' => EquestrianBillingEntryStatusEnum::class,
        ];
    }
}
