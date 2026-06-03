<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Http\Controllers;

use Capell\AgentDelivery\Data\ResolvedAgentDeliveryPageData;
use Capell\AgentDelivery\Providers\AgentDeliveryServiceProvider;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class AbstractAgentDeliveryController
{
    protected const string API_VERSION = 'v1';

    protected function notFound(): JsonResponse
    {
        return response()
            ->json(['message' => __('capell-agent-delivery::messages.page_not_found')], 404)
            ->header('X-Capell-Agent-Delivery-Version', static::API_VERSION);
    }

    protected function isNotInstalled(): bool
    {
        return ! CapellCore::isPackageInstalled(AgentDeliveryServiceProvider::$packageName);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $cacheTags
     */
    protected function json(array $payload, int $status = 200, array $cacheTags = ['agent-delivery']): JsonResponse
    {
        return response()
            ->json($payload, $status)
            ->header('X-Capell-Agent-Delivery-Version', static::API_VERSION)
            ->header('X-Capell-Cache-Tags', implode(',', $cacheTags));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $cacheTags
     */
    protected function cacheableJson(Request $request, array $payload, array $cacheTags = ['agent-delivery'], ?string $lastModifiedAt = null): JsonResponse
    {
        $encodedPayload = json_encode($payload, JSON_THROW_ON_ERROR);
        $etag = '"' . hash('sha256', $encodedPayload) . '"';
        $maxAge = $this->cacheMaxAge();
        $lastModifiedHeader = $lastModifiedAt !== null ? ($this->httpDate($lastModifiedAt) ?? $lastModifiedAt) : null;

        if ($this->requestMatchesEtag($request, $etag)) {
            $response = response()
                ->json(null, Response::HTTP_NOT_MODIFIED)
                ->header('X-Capell-Agent-Delivery-Version', static::API_VERSION)
                ->header('X-Capell-Cache-Tags', implode(',', $cacheTags))
                ->header('Cache-Control', sprintf('public, max-age=%d', $maxAge))
                ->header('ETag', $etag);

            if ($lastModifiedHeader !== null) {
                $response->header('Last-Modified', $lastModifiedHeader);
            }

            return $response;
        }

        $response = $this->json($payload, cacheTags: $cacheTags)
            ->header('Cache-Control', sprintf('public, max-age=%d', $maxAge))
            ->header('ETag', $etag);

        if ($lastModifiedHeader !== null) {
            $response->header('Last-Modified', $lastModifiedHeader);
        }

        return $response;
    }

    /**
     * @return list<string>
     */
    protected function cacheTags(ResolvedAgentDeliveryPageData $resolved): array
    {
        $tags = ['agent-delivery'];

        if ($resolved->site instanceof Site) {
            $tags[] = 'site:' . $resolved->site->getKey();
        }

        if ($resolved->language instanceof Language) {
            $tags[] = 'language:' . $resolved->language->getKey();
        }

        if ($resolved->page instanceof Pageable) {
            $tags[] = 'page:' . $resolved->page->getKey();
        }

        return $tags;
    }

    private function cacheMaxAge(): int
    {
        $maxAge = config('capell-agent-delivery.public_pages.cache_max_age_seconds', 300);

        if (is_int($maxAge)) {
            return max(0, $maxAge);
        }

        return is_numeric($maxAge) ? max(0, (int) $maxAge) : 300;
    }

    private function requestMatchesEtag(Request $request, string $etag): bool
    {
        $header = $request->headers->get('If-None-Match');

        if (! is_string($header) || trim($header) === '') {
            return false;
        }

        $candidates = array_map(trim(...), explode(',', $header));

        return in_array($etag, $candidates, true) || in_array('W/' . $etag, $candidates, true);
    }

    private function httpDate(string $date): ?string
    {
        try {
            return CarbonImmutable::parse($date)->toRfc7231String();
        } catch (Throwable) {
            return null;
        }
    }
}
