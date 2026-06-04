<?php

declare(strict_types=1);

namespace Capell\UrlManager\Http\Middleware;

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\UrlManager\Actions\RecordNotFoundOpportunityAction;
use Capell\UrlManager\Data\NotFoundOpportunityData;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RecordNotFoundOpportunityMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== 404 || ! (bool) config('capell-url-manager.not_found.capture_middleware_enabled', true)) {
            return $response;
        }

        $path = '/' . ltrim($request->path(), '/');

        if ($this->isIgnoredPath($path)) {
            return $response;
        }

        RecordNotFoundOpportunityAction::run(new NotFoundOpportunityData(
            sourceUrl: $request->getRequestUri(),
            siteId: $this->modelKey($request->attributes->get('site')),
            languageId: $this->modelKey($request->attributes->get('language')),
            context: [
                'source' => 'frontend_404_middleware',
                'referer' => $request->headers->get('referer'),
            ],
        ));

        return $response;
    }

    private function isIgnoredPath(string $path): bool
    {
        $ignoredPrefixes = config('capell-url-manager.not_found.ignored_path_prefixes', []);

        if (! is_array($ignoredPrefixes)) {
            return false;
        }

        foreach ($ignoredPrefixes as $prefix) {
            if (is_string($prefix) && $prefix !== '' && str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function modelKey(mixed $value): ?int
    {
        if ($value instanceof Site || $value instanceof Language) {
            return (int) $value->getKey();
        }

        return null;
    }
}
