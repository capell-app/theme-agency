<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Policies;

use Capell\AIOrchestrator\Settings\AIOrchestratorSettings;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class AiCreatorPolicy
{
    private const string USE_PERMISSION = 'Use:AiCreator';

    public function __construct(private readonly AIOrchestratorSettings $settings) {}

    public function isEnabledFor(object $site): bool
    {
        $siteOverride = $site->ai_creator_enabled ?? null;

        if ($siteOverride !== null) {
            return (bool) $siteOverride;
        }

        return $this->settings->ai_creator;
    }

    public function canUse(?object $user, object $site): bool
    {
        if (! $this->isEnabledFor($site)) {
            return false;
        }

        if ($user === null) {
            return false;
        }

        if ($user instanceof Authenticatable && Gate::forUser($user)->allows(self::USE_PERMISSION)) {
            return true;
        }

        if (method_exists($user, 'can')) {
            return $user->can(self::USE_PERMISSION) === true;
        }

        return true;
    }
}
