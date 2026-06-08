<?php

declare(strict_types=1);

namespace Capell\UrlManager\Http\Middleware;

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\UrlManager\Actions\ResolveRedirectRuleAction;
use Capell\UrlManager\Data\RedirectResolutionData;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ServeGoneRedirectRuleMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $resolution = ResolveRedirectRuleAction::run(
            requestUrl: $request->getRequestUri(),
            siteId: $this->modelKey($request->attributes->get('site')),
            languageId: $this->modelKey($request->attributes->get('language')),
            refererUrl: $request->headers->get('referer'),
            userAgent: $request->userAgent(),
            ipAddress: $request->ip(),
            statusCode: Response::HTTP_GONE,
        );

        if ($resolution instanceof RedirectResolutionData && $resolution->statusCode === Response::HTTP_GONE) {
            return new Response('', Response::HTTP_GONE);
        }

        return $next($request);
    }

    private function modelKey(mixed $value): ?int
    {
        if ($value instanceof Site || $value instanceof Language) {
            return (int) $value->getKey();
        }

        return null;
    }
}
