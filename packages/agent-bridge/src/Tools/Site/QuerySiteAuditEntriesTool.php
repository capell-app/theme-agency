<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Tools\Site;

use Capell\AgentBridge\Actions\QueryAgentBridgeAuditEntriesAction;
use Capell\AgentBridge\Models\CapellAgentBridgeToken;
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

#[Name('capell-site-query-audit')]
#[Title('Query Agent Bridge Audit')]
#[Description('Query recent Agent Bridge audit entries for the authenticated token.')]
#[IsReadOnly]
final class QuerySiteAuditEntriesTool extends Tool
{
    #[Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'capability' => $schema->string()->description('Optional capability key to filter by.'),
            'event' => $schema->string()->description('Optional audit event to filter by.'),
            'limit' => $schema->integer()->description('Maximum entries to return, between 1 and 100.'),
        ];
    }

    public function handle(Request $request, CapellAgentBridgeToken $token): ResponseFactory
    {
        $data = $request->validate([
            'capability' => ['sometimes', 'nullable', 'string'],
            'event' => ['sometimes', 'nullable', 'string'],
            'limit' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return Response::structured([
            'entries' => QueryAgentBridgeAuditEntriesAction::run(
                $token,
                is_string($data['capability'] ?? null) ? $data['capability'] : null,
                is_string($data['event'] ?? null) ? $data['event'] : null,
                (int) ($data['limit'] ?? 25),
            )->all(),
        ]);
    }
}
