<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Models\RedirectHit;
use Capell\UrlManager\Models\RedirectRule;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordRedirectHitAction
{
    use AsAction;

    public function handle(
        RedirectRule $redirectRule,
        string $requestUrl,
        ?string $refererUrl = null,
        ?string $userAgent = null,
        ?string $ipAddress = null,
    ): RedirectHit {
        $hitAt = now();

        /** @var RedirectHit $redirectHit */
        $redirectHit = $redirectRule->hits()->create([
            'request_url' => mb_substr($requestUrl, 0, 2048),
            'referer_url' => $refererUrl === null ? null : mb_substr($refererUrl, 0, 2048),
            'user_agent_hash' => $this->nullableHash($userAgent),
            'ip_hash' => $this->nullableHash($ipAddress),
            'hit_at' => $hitAt,
        ]);

        $redirectRule->forceFill([
            'hit_count' => $redirectRule->hit_count + 1,
            'last_hit_at' => $hitAt,
        ])->save();

        return $redirectHit;
    }

    private function nullableHash(?string $value): ?string
    {
        return $value === null || $value === '' ? null : hash('sha256', $value);
    }
}
