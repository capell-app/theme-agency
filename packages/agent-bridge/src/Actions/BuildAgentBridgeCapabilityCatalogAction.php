<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Actions;

use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<array<string, mixed>> run()
 */
final class BuildAgentBridgeCapabilityCatalogAction
{
    use AsAction;

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(?CapellAgentBridgeCapabilityRegistry $registry = null): array
    {
        $registry ??= resolve(CapellAgentBridgeCapabilityRegistry::class);

        return $registry
            ->all()
            ->sortBy(fn (CapabilityData $capability): string => $capability->server->value . ':' . $capability->scope . ':' . $capability->key)
            ->map(fn (CapabilityData $capability): array => [
                'key' => $capability->key,
                'name' => $capability->name,
                'scope' => $capability->scope,
                'server' => $capability->server->value,
                'risk' => $capability->risk->value,
                'requires_confirmation' => $capability->needsConfirmation(),
                'supports_preview' => $capability->supportsPreview,
                'required_package' => $capability->requiredPackage,
                'policy_ability' => $capability->policyAbility,
                'audit_event' => $capability->auditEvent,
                'input_data_class' => $capability->inputDataClass,
                'output_data_class' => $capability->outputDataClass,
                'input_schema' => $capability->inputSchema,
                'output_schema' => $capability->outputSchema,
            ])
            ->values()
            ->all();
    }
}
