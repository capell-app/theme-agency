<?php

declare(strict_types=1);

namespace Capell\Newsletter\Policies;

use Capell\Newsletter\Models\SyncAttempt;
use Illuminate\Database\Eloquent\Model;
use Override;

final class SyncAttemptPolicy extends AbstractNewsletterResourcePolicy
{
    protected static function subject(): string
    {
        return 'SyncAttempt';
    }

    #[Override]
    protected function recordSiteId(Model $record): ?int
    {
        if (! $record instanceof SyncAttempt) {
            return parent::recordSiteId($record);
        }

        $siteId = $record->subscriber?->site_id ?? $record->providerConnection?->site_id;

        return is_numeric($siteId) ? (int) $siteId : null;
    }
}
