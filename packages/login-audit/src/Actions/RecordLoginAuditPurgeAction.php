<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Actions;

use Capell\LoginAudit\Settings\LoginAuditSettings;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class RecordLoginAuditPurgeAction
{
    use AsAction;

    public function handle(?CarbonImmutable $purgedAt = null): void
    {
        try {
            /** @var LoginAuditSettings $settings */
            $settings = resolve(LoginAuditSettings::class);
        } catch (Throwable) {
            return;
        }

        $settings->last_purged_at = ($purgedAt ?? CarbonImmutable::now())->toIso8601String();
        $settings->save();
    }
}
