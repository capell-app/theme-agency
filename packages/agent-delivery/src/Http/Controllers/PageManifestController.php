<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Http\Controllers;

use Capell\AgentDelivery\Actions\ResolveAgentDeliveryPageAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PageManifestController extends AbstractAgentDeliveryController
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($this->isNotInstalled()) {
            return $this->notFound();
        }

        $resolved = ResolveAgentDeliveryPageAction::run($request);

        if (! $resolved->found() || $resolved->delivery === null) {
            return $this->notFound();
        }

        return $this->cacheableJson($request, [
            'data' => $resolved->delivery->toArray(),
        ], cacheTags: $this->cacheTags($resolved), lastModifiedAt: $resolved->delivery->lastUpdatedAt);
    }
}
