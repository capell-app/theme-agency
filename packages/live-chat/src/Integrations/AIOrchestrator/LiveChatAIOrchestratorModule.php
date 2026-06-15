<?php

declare(strict_types=1);

namespace Capell\LiveChat\Integrations\AIOrchestrator;

use Capell\AIOrchestrator\Contracts\AIOrchestratorModule;
use Capell\AIOrchestrator\Data\AIOrchestratorCapabilityData;
use Capell\AIOrchestrator\Enums\AIOrchestratorApprovalLevel;

final class LiveChatAIOrchestratorModule implements AIOrchestratorModule
{
    public function key(): string
    {
        return 'live-chat';
    }

    public function label(): string
    {
        return 'Live Chat';
    }

    /**
     * @return array<int, AIOrchestratorCapabilityData>
     */
    public function capabilities(): array
    {
        return [
            $this->capability('classify-message', 'Classify message', 'Classify a live-chat visitor message by intent.', AIOrchestratorApprovalLevel::None),
            $this->capability('detect-risk-sentiment', 'Detect risk and sentiment', 'Detect conversation risk and visitor sentiment.', AIOrchestratorApprovalLevel::None),
            $this->capability('answer-approved-sources', 'Answer from approved sources', 'Draft a grounded answer from Live Chat and Knowledge Base sources.', AIOrchestratorApprovalLevel::Draft),
            $this->capability('extract-lead-details', 'Extract lead details', 'Extract lead details from the live-chat transcript.', AIOrchestratorApprovalLevel::None),
            $this->capability('summarize-conversation', 'Summarize conversation', 'Summarize a live-chat conversation for operators.', AIOrchestratorApprovalLevel::Draft),
            $this->capability('suggest-human-reply', 'Suggest human reply', 'Draft a human-reviewed operator reply.', AIOrchestratorApprovalLevel::Draft),
            $this->capability('identify-knowledge-gap', 'Identify knowledge gap', 'Identify unanswered or low-confidence live-chat questions.', AIOrchestratorApprovalLevel::None),
        ];
    }

    private function capability(string $key, string $label, string $description, AIOrchestratorApprovalLevel $approvalLevel): AIOrchestratorCapabilityData
    {
        return new AIOrchestratorCapabilityData(
            key: $key,
            label: $label,
            description: $description,
            actionClass: RunLiveChatAIOrchestratorCapabilityAction::class,
            approvalLevel: $approvalLevel,
        );
    }
}
