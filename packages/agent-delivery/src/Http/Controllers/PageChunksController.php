<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Http\Controllers;

use Capell\AgentDelivery\Actions\BuildAgentDeliveryChunksAction;
use Capell\AgentDelivery\Actions\ResolveAgentDeliveryPageAction;
use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PageChunksController extends AbstractAgentDeliveryController
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($this->isNotInstalled()) {
            return $this->notFound();
        }

        $resolved = ResolveAgentDeliveryPageAction::run($request);

        if (! $resolved->found() || $resolved->page === null || $resolved->site === null || $resolved->language === null || $resolved->delivery === null) {
            return $this->notFound();
        }

        $chunks = BuildAgentDeliveryChunksAction::run($resolved->page, $resolved->site, $resolved->language, $resolved->delivery);

        return $this->cacheableJson($request, [
            'data' => array_map(
                static fn (AgentDeliveryChunkData $chunk): array => $chunk->toArray(),
                $chunks,
            ),
            'meta' => [
                'count' => count($chunks),
                'canonicalUrl' => $resolved->delivery->canonicalUrl,
                'generatedAt' => $resolved->delivery->lastUpdatedAt ?? now()->toIso8601String(),
            ],
        ], cacheTags: $this->cacheTags($resolved), lastModifiedAt: $resolved->delivery->lastUpdatedAt);
    }
}
