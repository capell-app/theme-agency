<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Http\Controllers;

use Capell\AgentDelivery\Actions\BuildAgentDeliveryPageIndexAction;
use Capell\AgentDelivery\Actions\ResolveAgentDeliveryPageAction;
use Capell\AgentDelivery\Data\AgentDeliveryPageIndexEntryData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PageIndexController extends AbstractAgentDeliveryController
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($this->isNotInstalled()) {
            return $this->notFound();
        }

        $resolved = ResolveAgentDeliveryPageAction::run($request);

        if ($resolved->site === null || $resolved->language === null) {
            return $this->notFound();
        }

        $entries = BuildAgentDeliveryPageIndexAction::run($resolved->site, $resolved->language);
        $latestUpdatedAt = collect($entries)
            ->map(fn (AgentDeliveryPageIndexEntryData $entry): ?string => $entry->lastUpdatedAt)
            ->filter()
            ->max();

        return $this->cacheableJson($request, [
            'data' => array_map(
                static fn (AgentDeliveryPageIndexEntryData $entry): array => $entry->toArray(),
                $entries,
            ),
            'meta' => [
                'count' => count($entries),
                'generatedAt' => $latestUpdatedAt ?? now()->toIso8601String(),
            ],
        ], cacheTags: $this->cacheTags($resolved), lastModifiedAt: $latestUpdatedAt);
    }
}
