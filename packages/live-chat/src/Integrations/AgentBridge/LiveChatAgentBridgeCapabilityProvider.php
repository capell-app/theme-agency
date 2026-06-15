<?php

declare(strict_types=1);

namespace Capell\LiveChat\Integrations\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityProvider;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Support\CapabilitySchemas;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\LiveChat\Providers\LiveChatServiceProvider;

final class LiveChatAgentBridgeCapabilityProvider implements CapellAgentBridgeCapabilityProvider
{
    public function registerCapabilities(CapellAgentBridgeCapabilityRegistry $registry): void
    {
        foreach ($this->capabilities() as $capability) {
            $registry->register($capability);
        }
    }

    /**
     * @return list<CapabilityData>
     */
    private function capabilities(): array
    {
        return [
            $this->readCapability('capell.live-chat.conversations.list', 'list_conversations'),
            $this->readCapability('capell.live-chat.conversations.inspect', 'inspect_conversation'),
            $this->readCapability('capell.live-chat.summary.preview', 'preview_summary'),
            $this->readCapability('capell.live-chat.reply.preview', 'preview_reply'),
            $this->readCapability('capell.live-chat.escalation.preview', 'preview_escalation'),
            $this->writeCapability('capell.live-chat.escalate', 'confirm_escalation', 'capell_agent-bridge.live-chat.escalated'),
            $this->writeCapability('capell.live-chat.close', 'confirm_close', 'capell_agent-bridge.live-chat.closed'),
        ];
    }

    private function readCapability(string $key, string $translationKey): CapabilityData
    {
        return new CapabilityData(
            key: $key,
            name: $this->translation("capell-live-chat::generic.agent_bridge.{$translationKey}_name"),
            description: $this->translation("capell-live-chat::generic.agent_bridge.{$translationKey}_description"),
            scope: 'capell.live-chat.read',
            server: CapabilityServerEnum::Site,
            risk: CapabilityRiskEnum::Read,
            actionClass: RunLiveChatAgentBridgeCapabilityAction::class,
            requiredPackage: LiveChatServiceProvider::$packageName,
            outputDataClass: CapabilityResultData::class,
            inputSchema: $this->conversationInputSchema(required: in_array($key, [
                'capell.live-chat.conversations.inspect',
                'capell.live-chat.summary.preview',
                'capell.live-chat.reply.preview',
                'capell.live-chat.escalation.preview',
            ], true)),
            outputSchema: CapabilitySchemas::capabilityResultOutput(),
            requiresConfirmation: false,
            auditEvent: null,
        );
    }

    private function writeCapability(string $key, string $translationKey, string $auditEvent): CapabilityData
    {
        return new CapabilityData(
            key: $key,
            name: $this->translation("capell-live-chat::generic.agent_bridge.{$translationKey}_name"),
            description: $this->translation("capell-live-chat::generic.agent_bridge.{$translationKey}_description"),
            scope: 'capell.live-chat.write',
            server: CapabilityServerEnum::Site,
            risk: CapabilityRiskEnum::High,
            actionClass: RunLiveChatAgentBridgeCapabilityAction::class,
            requiredPackage: LiveChatServiceProvider::$packageName,
            outputDataClass: CapabilityResultData::class,
            inputSchema: $this->conversationInputSchema(required: true),
            outputSchema: CapabilitySchemas::capabilityResultOutput(),
            requiresConfirmation: true,
            auditEvent: $auditEvent,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function conversationInputSchema(bool $required): array
    {
        return [
            'type' => 'object',
            'required' => $required ? ['conversation_id'] : [],
            'properties' => [
                'conversation_id' => ['type' => 'integer'],
                'installation_id' => ['type' => 'integer'],
                'site_id' => ['type' => 'integer'],
                'status' => ['type' => 'string'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50],
                'note' => ['type' => 'string'],
            ],
        ];
    }

    private function translation(string $key): string
    {
        $value = __($key);

        return is_string($value) ? $value : $key;
    }
}
