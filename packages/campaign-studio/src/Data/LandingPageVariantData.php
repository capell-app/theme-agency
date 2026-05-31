<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Data;

use Capell\CampaignStudio\Models\CampaignLandingPage;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class LandingPageVariantData extends Data
{
    public function __construct(
        public int $landingPageId,
        public int $campaignGroupId,
        public int $pageId,
        public string $variantKey,
        public ?string $headline,
        public ?string $utmContent,
        public ?string $utmTerm,
        public bool $isPrimary,
    ) {}

    public static function fromLandingPage(CampaignLandingPage $landingPage): self
    {
        $landingPageId = (int) $landingPage->getKey();

        return new self(
            landingPageId: $landingPageId,
            campaignGroupId: (int) $landingPage->campaign_group_id,
            pageId: (int) $landingPage->page_id,
            variantKey: self::variantKey($landingPage, $landingPageId),
            headline: $landingPage->headline,
            utmContent: $landingPage->utm_content,
            utmTerm: $landingPage->utm_term,
            isPrimary: (bool) $landingPage->is_primary,
        );
    }

    private static function variantKey(CampaignLandingPage $landingPage, int $landingPageId): string
    {
        if (is_string($landingPage->utm_content) && trim($landingPage->utm_content) !== '') {
            return trim($landingPage->utm_content);
        }

        if (is_string($landingPage->utm_term) && trim($landingPage->utm_term) !== '') {
            return trim($landingPage->utm_term);
        }

        return 'landing-page-' . $landingPageId;
    }
}
