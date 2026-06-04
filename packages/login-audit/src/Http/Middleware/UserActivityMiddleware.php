<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Http\Middleware;

use Capell\LoginAudit\Actions\UpdateLastSeenForActorAction;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserActivityMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->user() !== null) {
            $this->updateUserActivity($request);
        }

        return $next($request);
    }

    private function updateUserActivity(Request $request): void
    {
        $user = $request->user();

        if (! $user instanceof Model) {
            return;
        }

        UpdateLastSeenForActorAction::run($user, $request);
    }
}
