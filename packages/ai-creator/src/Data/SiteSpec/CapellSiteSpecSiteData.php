<?php

declare(strict_types=1);

namespace Capell\AiCreator\Data\SiteSpec;

use Spatie\LaravelData\Data;

/**
 * Top-level site identity. `name` is the Site record name; the remaining
 * fields populate Site.meta (business_name / organization_type) and seed
 * the site's translation summary.
 */
final class CapellSiteSpecSiteData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $businessName = null,
        public readonly ?string $organisationType = null,
        public readonly ?string $description = null,
    ) {}
}
