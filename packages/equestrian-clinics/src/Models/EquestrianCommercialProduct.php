<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianCommercialProductTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property EquestrianCommercialProductTypeEnum $type
 * @property string $name
 * @property string|null $description
 * @property int $price_pence
 * @property int|null $credit_quantity
 * @property string|null $eligible_archetype
 * @property bool $active
 * @property array<string, mixed>|null $settings
 */
final class EquestrianCommercialProduct extends Model
{
    protected $table = 'equestrian_commercial_products';

    protected $guarded = [];

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'credit_quantity' => 'integer',
            'price_pence' => 'integer',
            'settings' => 'json',
            'type' => EquestrianCommercialProductTypeEnum::class,
        ];
    }
}
