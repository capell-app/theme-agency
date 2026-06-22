<?php

declare(strict_types=1);

namespace Capell\AiCreator\Support;

use Capell\AgentBridge\Support\AgentBridgePageAccess;
use Capell\AiCreator\Models\AiCreatorSession;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;

final class AiCreatorSessionAccess
{
    public static function authorizeStart(?Authenticatable $user, ?int $siteId): void
    {
        if ($siteId === null) {
            return;
        }

        AgentBridgePageAccess::authorizeSite($user, $siteId);
    }

    public static function authorizeSession(?Authenticatable $user, AiCreatorSession $session): void
    {
        if (! $user instanceof Authenticatable) {
            throw ValidationException::withMessages([
                'session_id' => __('capell-ai-creator::package.session_requires_user'),
            ]);
        }

        $siteId = $session->site_id;

        if (is_numeric($siteId)) {
            AgentBridgePageAccess::authorizeSite($user, (int) $siteId, 'session_id');

            return;
        }

        if (self::isGlobalAdmin($user) || (int) $session->user_id === (int) $user->getAuthIdentifier()) {
            return;
        }

        throw ValidationException::withMessages([
            'session_id' => __('capell-ai-creator::package.session_forbidden'),
        ]);
    }

    private static function isGlobalAdmin(Authenticatable $user): bool
    {
        if (is_callable([$user, 'isGlobalAdmin']) && $user->isGlobalAdmin() === true) {
            return true;
        }

        return is_callable([$user, 'hasRole'])
            && $user->hasRole(config('capell.roles.super_admin', 'super_admin')) === true;
    }
}
