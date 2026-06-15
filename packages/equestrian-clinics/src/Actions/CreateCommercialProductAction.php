<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianCommercialProductTypeEnum;
use Capell\EquestrianClinics\Models\EquestrianCommercialProduct;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianCommercialProduct run(EquestrianCommercialProductTypeEnum $type, string $name, int $pricePence, ?string $description = null, ?int $creditQuantity = null, ?string $eligibleArchetype = null, ?int $siteId = null, ?array<string, mixed> $settings = null, bool $active = true)
 */
final class CreateCommercialProductAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>|null  $settings
     */
    public function handle(
        EquestrianCommercialProductTypeEnum $type,
        string $name,
        int $pricePence,
        ?string $description = null,
        ?int $creditQuantity = null,
        ?string $eligibleArchetype = null,
        ?int $siteId = null,
        ?array $settings = null,
        bool $active = true,
    ): EquestrianCommercialProduct {
        return EquestrianCommercialProduct::query()->create([
            'site_id' => $siteId,
            'type' => $type,
            'name' => $name,
            'description' => $description,
            'price_pence' => $pricePence,
            'credit_quantity' => $creditQuantity,
            'eligible_archetype' => $eligibleArchetype,
            'settings' => $settings,
            'active' => $active,
        ]);
    }
}
