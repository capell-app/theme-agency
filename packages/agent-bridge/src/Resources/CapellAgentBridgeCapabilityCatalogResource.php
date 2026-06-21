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
            if (! is_array($row)) {
                continue;
            }

            $markdown[] = sprintf(
                '| %s | `%s` | %s | %s | %s | %s | %s | %s |',
                $this->cell($this->stringValue($row, 'name')),
                $this->cell($this->stringValue($row, 'scope')),
                $this->cell($this->stringValue($row, 'server')),
                $this->cell($this->stringValue($row, 'risk')),
                ($row['requires_confirmation'] ?? false) === true ? 'yes' : 'no',
                ($row['supports_preview'] ?? false) === true ? 'yes' : 'no',
                $this->cell($this->stringValue($row, 'required_package')),
                $this->cell($this->stringValue($row, 'policy_ability')),
            );
        }

        return Response::text(implode("\n", $markdown));
    }

    private function cell(string $value): string
    {
        $value = trim($value);

        return $value === '' ? '-' : str_replace('|', '\\|', $value);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function stringValue(array $row, string $key): string
    {
        $value = $row[$key] ?? null;

        return is_string($value) ? $value : '';
    }
}
