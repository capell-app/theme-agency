<?php

declare(strict_types=1);

namespace Capell\Newsletter\Policies;

use Capell\Newsletter\Models\ProviderAudience;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ProviderAudiencePolicy extends AbstractNewsletterResourcePolicy
{
    protected static function subject(): string
    {
        return 'ProviderAudience';
    }

    #[Override]
    protected function recordSiteId(Model $record): ?int
    {
        if (! $record instanceof ProviderAudience) {
            return parent::recordSiteId($record);
        }

        $siteId = $record->providerConnection?->site_id;

        return is_numeric($siteId) ? (int) $siteId : null;
    }
}
