<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Resources;

use Capell\AgentBridge\Actions\BuildAgentBridgeCapabilityCatalogAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('capell-agent-bridge-capability-schema')]
#[Title('Capell Agent Bridge Capability Schema')]
#[Description('Machine-readable capability catalog with MCP-compatible input and output schemas for registered Agent Bridge capabilities.')]
#[Uri('capell://agent-bridge/capabilities/schema')]
#[MimeType('application/json')]
final class CapellAgentBridgeCapabilitySchemaResource extends Resource
{
    public function handle(): Response
    {
        return Response::json([
            'schemaVersion' => '1.0',
            'capabilities' => BuildAgentBridgeCapabilityCatalogAction::run(),
        ]);
    }
}
