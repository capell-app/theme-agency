<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DiscoveryController extends AbstractAgentDeliveryController
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($this->isNotInstalled()) {
            return $this->notFound();
        }

        $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/api/capell/agent/v1';

        return $this->cacheableJson($request, [
            'data' => [
                'type' => 'capell-agent-delivery-discovery',
                'version' => 'v1',
                'capabilities' => [
                    'public-page-index',
                    'public-page-manifest',
                    'public-page-chunks',
                ],
                'endpoints' => [
                    [
                        'rel' => 'index',
                        'method' => 'GET',
                        'url' => $baseUrl . '/pages',
                        'accept' => 'application/json',
                    ],
                    [
                        'rel' => 'manifest',
                        'method' => 'GET',
                        'urlTemplate' => $baseUrl . '/pages/manifest{?url,locale}',
                        'accept' => 'application/json',
                    ],
                    [
                        'rel' => 'chunks',
                        'method' => 'GET',
                        'urlTemplate' => $baseUrl . '/pages/chunks{?url,locale}',
                        'accept' => 'application/json',
                    ],
                ],
                'cacheVariation' => self::CACHE_VARIATION,
            ],
            'meta' => [
                'generatedAt' => now()->toIso8601String(),
            ],
        ]);
    }
}
