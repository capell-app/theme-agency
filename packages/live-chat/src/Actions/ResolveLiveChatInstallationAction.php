<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Models\LiveChatInstallation;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static LiveChatInstallation|null run(?string $publicKey = null, ?int $siteId = null)
 */
final class ResolveLiveChatInstallationAction
{
    use AsAction;

    public function handle(?string $publicKey = null, ?int $siteId = null): ?LiveChatInstallation
    {
        $query = LiveChatInstallation::query()->where('is_active', true);

        if (is_string($publicKey) && trim($publicKey) !== '') {
            return $query
                ->where('public_key', trim($publicKey))
                ->first();
        }

        $resolvedSiteId = $siteId ?? $this->defaultSiteId();

        if ($resolvedSiteId === null) {
            return null;
        }

        return $query
            ->where('site_id', $resolvedSiteId)
            ->oldest('id')
            ->first();
    }

    private function defaultSiteId(): ?int
    {
        $siteId = config('capell-live-chat.default_site_id');

        return is_numeric($siteId) ? (int) $siteId : null;
    }
}
