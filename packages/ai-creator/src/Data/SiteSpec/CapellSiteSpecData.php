<?php

declare(strict_types=1);

namespace Capell\AiCreator\Data\SiteSpec;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/**
 * The single contract for a generated Capell site.
 *
 * An external AI agent assembles this from the discovery tools + interview,
 * Capell validates and builds it. The same spec drives all three delivery
 * targets — preview, cloud deploy, and local export — via
 * BuildCapellSiteFromSpecAction. Capell performs no inference; the agent
 * supplies every value here.
 */
final class CapellSiteSpecData extends Data
{
    /**
     * @param  array<int, CapellSiteSpecPageData>  $pages
     */
    public function __construct(
        public readonly CapellSiteSpecSiteData $site,
        public readonly CapellSiteSpecThemeData $theme,
        #[DataCollectionOf(CapellSiteSpecPageData::class)]
        public readonly array $pages,
        public readonly CapellSiteSpecLanguageData $language = new CapellSiteSpecLanguageData,
    ) {}
}
