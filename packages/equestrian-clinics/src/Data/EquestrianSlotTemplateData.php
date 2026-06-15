<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Data;

use Capell\EquestrianClinics\Enums\EquestrianSlotArchetypeEnum;
use Spatie\LaravelData\Data;

final class EquestrianSlotTemplateData extends Data
{
    public function __construct(
        public string $title,
        public EquestrianSlotArchetypeEnum $archetype,
        public int $durationMinutes,
        public int $gapMinutes,
        public int $capacityMin,
        public int $capacityMax,
        public int $pricePence,
        public ?int $depositPence = null,
        public ?string $skillTier = null,
    ) {}
}
