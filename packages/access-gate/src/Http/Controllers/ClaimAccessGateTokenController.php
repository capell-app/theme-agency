<?php

declare(strict_types=1);

namespace Capell\AccessGate\Http\Controllers;

use Capell\AccessGate\Actions\ConsumeAccessGateClaimTokenAction;
use Capell\AccessGate\Data\IssuedAccessGateTokenData;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Models\BrowserToken;
use Capell\AccessGate\Support\AccessGateResponseHeaders;
use Capell\AccessGate\Support\RequestedUrlGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

final class ClaimAccessGateTokenController
{
    public function __construct(
        private readonly ConsumeAccessGateClaimTokenAction $consumeClaimToken,
        private readonly RequestedUrlGuard $requestedUrls,
    ) {}

    public function __invoke(Request $request, string $token): RedirectResponse|Response
    {
        $issuedBrowserToken = $this->consumeClaimToken->handle($token, [
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => $request->userAgent(),
        ]);

        if (! $issuedBrowserToken instanceof IssuedAccessGateTokenData || ! $issuedBrowserToken->token instanceof BrowserToken) {
            return AccessGateResponseHeaders::noStore(response()->view('capell-access-gate::message', [
                'title' => __('capell-access-gate::public.claim_failed.title'),
                'message' => __('capell-access-gate::public.claim_failed.message'),
            ], 422));
        }

        $browserToken = $issuedBrowserToken->token->loadMissing('grant.registration', 'area');
        $area = $browserToken->area;
        $redirectUrl = $area instanceof Area
            ? $this->requestedUrls->redirectUrl($request, $area, $browserToken->grant?->registration?->requested_url)
            : url('/');

        return AccessGateResponseHeaders::noStore(
            redirect($redirectUrl)
                ->withCookie($this->browserCookie($request, $issuedBrowserToken->plainTextToken)),
        );
    }

    private function browserCookie(Request $request, string $plainTextToken): \Symfony\Component\HttpFoundation\Cookie
    {
        $secure = config('access-gate.cookies.browser_token.secure');

        return Cookie::make(
            config('access-gate.cookies.browser_token.name', 'capell_access_gate_browser_token'),
            $plainTextToken,
            config('access-gate.cookies.browser_token.ttl_minutes', 259200),
            config('access-gate.cookies.browser_token.path', '/'),
            config('access-gate.cookies.browser_token.domain'),
            is_bool($secure) ? $secure : $request->isSecure(),
            config('access-gate.cookies.browser_token.http_only', true),
            false,
            config('access-gate.cookies.browser_token.same_site', 'lax'),
        );
    }
}
