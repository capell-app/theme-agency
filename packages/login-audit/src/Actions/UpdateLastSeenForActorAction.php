<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Actions;

use Capell\LoginAudit\Models\LoginAudit;
use Capell\LoginAudit\Settings\LoginAuditSettings;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class UpdateLastSeenForActorAction
{
    use AsAction;

    public function handle(Model $actor, Request $request, ?CarbonImmutable $now = null): ?LoginAudit
    {
        $trackedAt = $now ?? CarbonImmutable::now();
        $ipAddress = ResolveLoginAuditIpAddressAction::run($request);
        $userAgent = (string) $request->userAgent();

        $audit = LoginAudit::query()
            ->where('authenticatable_type', $actor->getMorphClass())
            ->where('authenticatable_id', $actor->getKey())
            ->when(
                $ipAddress === null,
                fn (Builder $query): Builder => $query->whereNull('ip_address'),
                fn (Builder $query): Builder => $query->where('ip_address', $ipAddress),
            )
            ->where('user_agent', $userAgent)
            ->where('login_at', '<', $trackedAt)
            ->orderByDesc('login_at')
            ->orderByDesc('id')
            ->first();

        if (! $audit instanceof LoginAudit) {
            return null;
        }

        if (! $this->shouldUpdate($audit, $trackedAt)) {
            return $audit;
        }

        $audit->last_seen_at = $trackedAt;
        $audit->save();

        return $audit;
    }

    private function shouldUpdate(LoginAudit $audit, CarbonImmutable $trackedAt): bool
    {
        if ($audit->last_seen_at === null) {
            return true;
        }

        $graceSeconds = $this->activityUpdateGraceSeconds();
        if ($graceSeconds <= 0) {
            return true;
        }

        return $audit->last_seen_at->lessThanOrEqualTo($trackedAt->subSeconds($graceSeconds));
    }

    private function activityUpdateGraceSeconds(): int
    {
        try {
            /** @var LoginAuditSettings $settings */
            $settings = resolve(LoginAuditSettings::class);

            return max(0, $settings->activity_update_grace_seconds);
        } catch (Throwable) {
            return 60;
        }
    }
}
