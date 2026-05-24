<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Http\Controllers;

use Capell\AgentDelivery\Actions\ResolveAgentDeliveryPageAction;
use Capell\AgentDelivery\Data\ResolvedAgentDeliveryPageData;
use Capell\AgentDelivery\Providers\AgentDeliveryServiceProvider;
use Capell\Core\Facades\CapellCore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PageManifestController
{
    private const string API_VERSION = 'v1';

    public function __invoke(Request $request): JsonResponse
    {
        if (! CapellCore::isPackageInstalled(AgentDeliveryServiceProvider::$packageName)) {
            return $this->notFound();
        }

        $resolved = ResolveAgentDeliveryPageAction::run($request);

        if (! $resolved->found() || $resolved->delivery === null) {
            return $this->notFound();
        }

        return $this->json([
            'data' => $resolved->delivery->toArray(),
        ], cacheTags: $this->cacheTags($resolved));
    }

    private function notFound(): JsonResponse
    {
        return $this->json(['message' => 'Page not found'], 404);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $cacheTags
     */
    private function json(array $payload, int $status = 200, array $cacheTags = ['agent-delivery']): JsonResponse
    {
        return response()
            ->json($payload, $status)
            ->header('X-Capell-Agent-Delivery-Version', self::API_VERSION)
            ->header('X-Capell-Cache-Tags', implode(',', $cacheTags));
    }

    /**
     * @return list<string>
     */
    private function cacheTags(ResolvedAgentDeliveryPageData $resolved): array
    {
        $tags = ['agent-delivery'];

        if ($resolved->site !== null) {
            $tags[] = 'site:' . $resolved->site->getKey();
        }

        if ($resolved->language !== null) {
            $tags[] = 'language:' . $resolved->language->getKey();
        }

        if ($resolved->page !== null) {
            $tags[] = 'page:' . $resolved->page->getKey();
        }

        return $tags;
    }
}
