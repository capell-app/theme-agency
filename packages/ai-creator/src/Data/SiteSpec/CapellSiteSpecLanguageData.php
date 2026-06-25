<?php

declare(strict_types=1);

namespace Capell\AiCreator\Data\SiteSpec;

use Spatie\LaravelData\Data;

/**
 * The site's primary language. Defaults to British English so a minimal
 * spec is buildable without the agent having to ask.
 */
final class CapellSiteSpecLanguageData extends Data
{
    public function __construct(
        public readonly string $code = 'en',
        public readonly string $name = 'English',
        public readonly string $locale = 'en_GB',
        public readonly string $flag = 'gb',
        public readonly bool $default = true,
    ) {}
}
