<?php

declare(strict_types=1);

namespace Capell\AccessGate\Http\Controllers;

use Capell\AccessGate\Actions\ResolveAccessGateAccessAction;
use Capell\AccessGate\Support\AccessGateResponseHeaders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AccessGateStatusController
{
    public function __construct(
        private readonly ResolveAccessGateAccessAction $resolveAccess,
    ) {}

    public function __invoke(Request $request, string $area): JsonResponse
    {
        $result = $this->resolveAccess->handle($request, [$area]);

        $response = response()->json([
            'allowed' => $result->allowed,
        ]);

        return AccessGateResponseHeaders::noStore($response);
    }
}
