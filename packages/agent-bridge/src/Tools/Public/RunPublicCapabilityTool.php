<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Tools\Public;

use Capell\AgentBridge\Actions\InvokeAgentBridgeCapabilityPreviewAction;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Override;

#[Name('capell-public-run-capability')]
#[Title('Run Public Capability')]
#[Description('Run a tokenless, publicly readable Capell capability (theme/type/layout catalogs, interview, site-spec schema, spec validation). Any capability outside the public allowlist is rejected.')]
#[IsReadOnly]
final class RunPublicCapabilityTool extends Tool
{
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'capability' => $schema->string()->description('Publicly readable capability key.')->required(),
            'payload' => $schema->object()->description('Capability payload.'),
        ];
    }

    public function handle(Request $request, CapellAgentBridgeCapabilityRegistry $registry): ResponseFactory
    {
        $data = $request->validate([
            'capability' => ['required', 'string'],
            'payload' => ['nullable', 'array'],
        ]);

        $key = (string) $data['capability'];

        // SECURITY GATE — the entire trust boundary for the tokenless endpoint.
        // Reject anything not in the public allowlist BEFORE delegating; the
        // invoke action does not re-check for a null client, so this gate is
        // the only thing standing between an anonymous caller and execution.
        $isPublic = $registry->publiclyReadable()
            ->contains(fn (CapabilityData $capability): bool => $capability->key === $key);

        abort_unless($isPublic, 403, sprintf('Capability [%s] is not publicly available.', $key));

        $result = InvokeAgentBridgeCapabilityPreviewAction::run(
            capabilityKey: $key,
            payload: $data['payload'] ?? [],
            client: null,
            token: null,
            user: null,
        );

        return Response::structured($result);
    }
}
