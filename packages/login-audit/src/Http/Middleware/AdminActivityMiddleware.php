<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Http\Middleware;

use Capell\LoginAudit\Actions\ShouldTrackAdminActivityAction;
use Capell\LoginAudit\Actions\UpdateLastSeenForActorAction;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminActivityMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (Filament::auth()->check()) {
            $this->updateUserActivity($request);
        }

        return $next($request);
    }

    private function updateUserActivity(Request $request): void
    {
        if (! ShouldTrackAdminActivityAction::run()) {
            return;
        }

        $user = Filament::auth()->user();

        if (! $user instanceof Model) {
            return;
        }

        UpdateLastSeenForActorAction::run($user, $request);
    }
}
