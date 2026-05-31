<?php

declare(strict_types=1);

namespace Capell\Newsletter\Policies;

use Capell\Newsletter\Models\ProviderInterestMapping;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ProviderInterestMappingPolicy extends AbstractNewsletterResourcePolicy
{
    protected static function subject(): string
    {
        return 'ProviderInterestMapping';
    }

    #[Override]
    protected function recordSiteId(Model $record): ?int
    {
        if (! $record instanceof ProviderInterestMapping) {
            return parent::recordSiteId($record);
        }

        $siteId = $record->providerAudience?->providerConnection?->site_id;

        return is_numeric($siteId) ? (int) $siteId : null;
    }
}
