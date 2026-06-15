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

#[Name('capell-agent-bridge-capability-catalog')]
#[Title('Capell Agent Bridge Capability Catalog')]
#[Description('Governance inventory of registered Agent Bridge capabilities, scopes, risk, server visibility, and confirmation requirements.')]
#[Uri('capell://agent-bridge/capabilities')]
#[MimeType('text/markdown')]
final class CapellAgentBridgeCapabilityCatalogResource extends Resource
{
    public function handle(): Response
    {
        $rows = BuildAgentBridgeCapabilityCatalogAction::run();

        $markdown = [
            '# Capell Agent Bridge Capability Catalog',
            '',
            '| Capability | Scope | Server | Risk | Confirmation | Preview | Required package | Policy ability |',
            '| --- | --- | --- | --- | --- | --- | --- | --- |',
        ];

        foreach ($rows as $row) {
            $markdown[] = sprintf(
                '| %s | `%s` | %s | %s | %s | %s | %s | %s |',
                $this->cell((string) $row['name']),
                $this->cell((string) $row['scope']),
                $this->cell((string) $row['server']),
                $this->cell((string) $row['risk']),
                $row['requires_confirmation'] === true ? 'yes' : 'no',
                $row['supports_preview'] === true ? 'yes' : 'no',
                $this->cell((string) ($row['required_package'] ?? '')),
                $this->cell((string) ($row['policy_ability'] ?? '')),
            );
        }

        return Response::text(implode("\n", $markdown));
    }

    private function cell(string $value): string
    {
        $value = trim($value);

        return $value === '' ? '-' : str_replace('|', '\\|', $value);
    }
}
