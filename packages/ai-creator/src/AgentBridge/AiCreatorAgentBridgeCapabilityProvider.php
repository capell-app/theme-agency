<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityProvider;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Support\CapabilitySchemas;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\AiCreator\Support\AiCreatorCapabilitySchemas;

final class AiCreatorAgentBridgeCapabilityProvider implements CapellAgentBridgeCapabilityProvider
{
    public function registerCapabilities(CapellAgentBridgeCapabilityRegistry $registry): void
    {
        foreach ($this->capabilities() as $capability) {
            if ($registry->has($capability->key)) {
                continue;
            }

            $registry->register($capability);
        }
    }

    /**
     * @return list<CapabilityData>
     */
    private function capabilities(): array
    {
        return [
            new CapabilityData(
                key: 'capell.ai-creator.sessions.start',
                name: 'Start AI Creator session',
                description: 'Start a reviewed AI Creator session for an existing Capell site or content workflow.',
                scope: 'capell.ai-creator.write',
                server: CapabilityServerEnum::Site,
                risk: CapabilityRiskEnum::Low,
                actionClass: StartAiCreatorSessionCapabilityAction::class,
                requiredPackage: 'capell-app/ai-creator',
                outputDataClass: CapabilityResultData::class,
                inputSchema: AiCreatorCapabilitySchemas::startSessionInput(),
                outputSchema: CapabilitySchemas::capabilityResultOutput(),
                supportsPreview: true,
                requiresConfirmation: false,
                auditEvent: 'ai-creator.sessions.start',
            ),
            new CapabilityData(
                key: 'capell.ai-creator.sessions.preview_apply',
                name: 'Preview AI Creator apply',
                description: 'Read the deterministic preview for an AI Creator session before applying changes.',
                scope: 'capell.ai-creator.read',
                server: CapabilityServerEnum::Site,
                risk: CapabilityRiskEnum::Read,
                actionClass: PreviewAiCreatorSessionCapabilityAction::class,
                requiredPackage: 'capell-app/ai-creator',
                outputDataClass: CapabilityResultData::class,
                inputSchema: AiCreatorCapabilitySchemas::sessionIdInput(),
                outputSchema: CapabilitySchemas::capabilityResultOutput(),
                supportsPreview: true,
                requiresConfirmation: false,
                auditEvent: 'ai-creator.sessions.preview',
            ),
            new CapabilityData(
                key: 'capell.ai-creator.sessions.apply',
                name: 'Apply AI Creator session',
                description: 'Apply a confirmed AI Creator session after review.',
                scope: 'capell.ai-creator.apply',
                server: CapabilityServerEnum::Site,
                risk: CapabilityRiskEnum::High,
                actionClass: ApplyAiCreatorSessionCapabilityAction::class,
                requiredPackage: 'capell-app/ai-creator',
                outputDataClass: CapabilityResultData::class,
                inputSchema: AiCreatorCapabilitySchemas::sessionIdInput(),
                outputSchema: CapabilitySchemas::capabilityResultOutput(),
                supportsPreview: true,
                requiresConfirmation: true,
                auditEvent: 'ai-creator.sessions.apply',
            ),
            $this->readCapability(
                key: 'capell.ai-creator.discovery.list_themes',
                name: 'List themes',
                description: 'List installed themes the spec may select via theme.key.',
                actionClass: ListThemesCapabilityAction::class,
                auditEvent: 'ai-creator.discovery.list_themes',
                public: true,
            ),
            $this->readCapability(
                key: 'capell.ai-creator.discovery.list_page_types',
                name: 'List page types',
                description: 'List page blueprints the spec may reference via pages[].pageType.',
                actionClass: ListPageTypesCapabilityAction::class,
                auditEvent: 'ai-creator.discovery.list_page_types',
                public: true,
            ),
            $this->readCapability(
                key: 'capell.ai-creator.discovery.list_section_types',
                name: 'List section types',
                description: 'List section blueprints the spec may reference via sections[].type.',
                actionClass: ListSectionTypesCapabilityAction::class,
                auditEvent: 'ai-creator.discovery.list_section_types',
                public: true,
            ),
            $this->readCapability(
                key: 'capell.ai-creator.discovery.list_layouts',
                name: 'List layouts',
                description: 'List installed layouts the builder assigns to pages.',
                actionClass: ListLayoutsCapabilityAction::class,
                auditEvent: 'ai-creator.discovery.list_layouts',
                public: true,
            ),
            new CapabilityData(
                key: 'capell.ai-creator.build_preview',
                name: 'Build AI Creator preview',
                description: 'Build a non-destructive preview site for a session from a candidate spec, before any cloud deploy.',
                scope: 'capell.ai-creator.write',
                server: CapabilityServerEnum::Site,
                risk: CapabilityRiskEnum::Low,
                actionClass: BuildAiCreatorSitePreviewCapabilityAction::class,
                requiredPackage: 'capell-app/ai-creator',
                outputDataClass: CapabilityResultData::class,
                inputSchema: AiCreatorCapabilitySchemas::buildPreviewInput(),
                outputSchema: CapabilitySchemas::capabilityResultOutput(),
                supportsPreview: true,
                requiresConfirmation: false,
                auditEvent: 'ai-creator.build_preview',
            ),
            new CapabilityData(
                key: 'capell.ai-creator.export_site',
                name: 'Export Capell site',
                description: 'Write a portable spec artifact + capell:install command for a local Capell project. Cost-free, no cloud resources.',
                scope: 'capell.ai-creator.write',
                server: CapabilityServerEnum::Site,
                risk: CapabilityRiskEnum::Low,
                actionClass: ExportSiteCapabilityAction::class,
                requiredPackage: 'capell-app/ai-creator',
                outputDataClass: CapabilityResultData::class,
                inputSchema: AiCreatorCapabilitySchemas::exportSiteInput(),
                outputSchema: CapabilitySchemas::capabilityResultOutput(),
                supportsPreview: true,
                requiresConfirmation: false,
                auditEvent: 'ai-creator.export_site',
            ),
            $this->readCapability(
                key: 'capell.ai-creator.interview.get',
                name: 'Get interview',
                description: 'Return the deterministic interview script (required core + agent expansion + rules) the agent uses to drive the conversation.',
                actionClass: GetInterviewCapabilityAction::class,
                auditEvent: 'ai-creator.interview.get',
                public: true,
            ),
            $this->readCapability(
                key: 'capell.ai-creator.discovery.get_site_spec_schema',
                name: 'Get site spec schema',
                description: 'Return the JSON schema for the Capell site spec the agent assembles.',
                actionClass: GetSiteSpecSchemaCapabilityAction::class,
                auditEvent: 'ai-creator.discovery.get_site_spec_schema',
                public: true,
            ),
            $this->readCapability(
                key: 'capell.ai-creator.discovery.validate_spec',
                name: 'Validate site spec',
                description: 'Normalise and validate a candidate site spec without building anything.',
                actionClass: ValidateSiteSpecCapabilityAction::class,
                auditEvent: 'ai-creator.discovery.validate_spec',
                inputSchema: AiCreatorCapabilitySchemas::validateSiteSpecInput(),
                public: true,
            ),
        ];
    }

    /**
     * @param  class-string<CapellAgentBridgeCapabilityAction>  $actionClass
     * @param  array<string, mixed>|null  $inputSchema
     */
    private function readCapability(
        string $key,
        string $name,
        string $description,
        string $actionClass,
        string $auditEvent,
        ?array $inputSchema = null,
        bool $public = false,
    ): CapabilityData {
        return new CapabilityData(
            key: $key,
            name: $name,
            description: $description,
            scope: 'capell.ai-creator.read',
            server: CapabilityServerEnum::Site,
            risk: CapabilityRiskEnum::Read,
            actionClass: $actionClass,
            requiredPackage: 'capell-app/ai-creator',
            outputDataClass: CapabilityResultData::class,
            inputSchema: $inputSchema ?? AiCreatorCapabilitySchemas::noInput(),
            outputSchema: CapabilitySchemas::capabilityResultOutput(),
            supportsPreview: true,
            requiresConfirmation: false,
            auditEvent: $auditEvent,
            public: $public,
        );
    }
}
