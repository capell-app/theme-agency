<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Http\Middleware;

use Capell\LoginAudit\Actions\ResolveLoginAuditIpAddressAction;
use Capell\LoginAudit\Models\LoginAudit;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
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
        $ipAddress = resolve(ResolveLoginAuditIpAddressAction::class)->handle($request);

        $userAgent = $request->userAgent();

        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User || ! method_exists($user, 'authentications')) {
            return;
        }

        $now = CarbonImmutable::now();

        /** @var MorphMany<LoginAudit, User&Model> $builder */
        $builder = $user->authentications();

        $log = $builder
            ->when(
                $ipAddress === null,
                fn (Builder $query): Builder => $query->whereNull('ip_address'),
                fn (Builder $query): Builder => $query->where('ip_address', $ipAddress),
            )
            ->where('user_agent', $userAgent)
            ->where('login_at', '<', $now)
            ->latest('id')
            ->first();

        if ($log !== null) {
            $log->last_seen_at = $now;
            $log->save();
        }
    }
}
