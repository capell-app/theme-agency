<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Tools\Public;

use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('capell-public-list-capabilities')]
#[Title('List Public Capabilities')]
#[Description('List the tokenless, publicly readable Capell capabilities (theme/page/section/layout catalogs, interview, site-spec schema, and spec validation). No authentication required.')]
#[IsReadOnly]
final class ListPublicCapabilitiesTool extends Tool
{
    public function handle(CapellAgentBridgeCapabilityRegistry $registry): ResponseFactory
    {
        return Response::structured([
            'capabilities' => $registry->publiclyReadable()
                ->map(fn (CapabilityData $capability): array => $capability->publicPayload())
                ->all(),
        ]);
    }
}
