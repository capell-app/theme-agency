<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property int|null $portal_account_id
 * @property string $label
 * @property int $initial_quantity
 * @property int $remaining_quantity
 * @property string|null $eligible_archetype
 * @property CarbonImmutable|null $expires_at
 * @property array<string, mixed>|null $meta
 */
final class EquestrianClinicCredit extends Model
{
    protected $table = 'equestrian_clinic_credits';

    protected $guarded = [];

    #[Override]
    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'initial_quantity' => 'integer',
            'meta' => 'json',
            'remaining_quantity' => 'integer',
        ];
    }
}
