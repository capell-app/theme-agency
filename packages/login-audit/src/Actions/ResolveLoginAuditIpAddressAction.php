<?php

declare(strict_types=1);

namespace Capell\LoginAudit\Actions;

use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveLoginAuditIpAddressAction
{
    use AsAction;

    public function handle(Request $request): ?string
    {
        if (! resolve(ShouldTrackUserIpAddressesAction::class)->handle()) {
            return null;
        }

        if (config('login-audit.behind_cdn') !== false) {
            $header = config('login-audit.behind_cdn.http_header_field');
            $value = is_string($header) ? $request->server($header) : null;
            $ipAddress = is_scalar($value) ? trim((string) $value) : '';

            return filter_var($ipAddress, FILTER_VALIDATE_IP) !== false ? $ipAddress : $request->ip();
        }

        return $request->ip();
    }
}
