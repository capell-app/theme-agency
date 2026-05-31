<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Data;

use Capell\CampaignStudio\Enums\LandingPageVariantMatchType;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class LandingPageVariantSelectionData extends Data
{
    public function __construct(
        public LandingPageVariantData $variant,
        public LandingPageVariantMatchType $matchType,
        public ?string $matchedValue = null,
    ) {}

    public static function fromLandingPage(
        CampaignLandingPage $landingPage,
        LandingPageVariantMatchType $matchType,
        ?string $matchedValue = null,
    ): self {
        return new self(
            variant: LandingPageVariantData::fromLandingPage($landingPage),
            matchType: $matchType,
            matchedValue: $matchedValue,
        );
    }
}
