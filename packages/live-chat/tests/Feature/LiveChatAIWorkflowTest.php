<?php

declare(strict_types=1);

require_once __DIR__ . '/../Pest.php';

use Capell\AIOrchestrator\Actions\RunAIOrchestratorCapabilityAction;
use Capell\AIOrchestrator\Data\AIOrchestratorCapabilityData;
use Capell\AIOrchestrator\Data\AIOrchestratorRunData;
use Capell\AIOrchestrator\Support\AIOrchestratorModuleRegistry;
use Capell\LiveChat\Actions\BuildLiveChatAnalyticsAction;
use Capell\LiveChat\Actions\IndexLiveChatKnowledgeSourceAction;
use Capell\LiveChat\Actions\StartLiveChatConversationAction;
use Capell\LiveChat\Data\IncomingLiveChatMessageData;
use Capell\LiveChat\Data\LiveChatVisitorData;
use Capell\LiveChat\Enums\KnowledgeSourceType;
use Capell\LiveChat\Models\LiveChatAIRun;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-15 10:00:00', 'Europe/London'));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

/**
 * @return array<string, mixed>
 */
function live_chat_ai_workflow_array(mixed $value): array
{
    throw_unless(is_array($value), RuntimeException::class, 'Expected AI workflow payload to be an array.');

    $items = [];

    foreach ($value as $key => $item) {
        throw_unless(is_string($key), RuntimeException::class, 'Expected AI workflow payload to be an object.');

        $items[$key] = $item;
    }

    return $items;
}

it('registers the live chat AI Orchestrator module capabilities', function (): void {
    $module = resolve(AIOrchestratorModuleRegistry::class)->module('live-chat');
    $capabilityKeys = collect($module->capabilities())
        ->map(static fn (AIOrchestratorCapabilityData $capability): string => $capability->key)
        ->values()
        ->all();

    expect($module->label())->toBe('Live Chat')
        ->and($capabilityKeys)->toBe([
            'classify-message',
            'detect-risk-sentiment',
            'answer-approved-sources',
            'extract-lead-details',
            'summarize-conversation',
            'suggest-human-reply',
            'identify-knowledge-gap',
        ]);
});

it('runs grounded source answering and operator workflow capabilities', function (): void {
    $installation = $this->createLiveChatInstallation();
    $source = LiveChatKnowledgeSource::query()->create([
        'site_id' => $installation->site_id,
        'type' => KnowledgeSourceType::Manual,
        'source_key' => 'onboarding',
        'title' => 'Onboarding help',
        'content' => 'Implementation onboarding includes a launch checklist and a technical setup review.',
        'status' => 'active',
    ]);
    $document = (new IndexLiveChatKnowledgeSourceAction)->handle($source, $installation->id);

    expect($document)->toBeInstanceOf(LiveChatKnowledgeDocument::class);

    if (! $document instanceof LiveChatKnowledgeDocument) {
        throw new RuntimeException('Manual source did not produce a document.');
    }

    $result = (new StartLiveChatConversationAction)->handle(
        new IncomingLiveChatMessageData(
            body: 'Can you explain implementation onboarding?',
            visitorToken: 'ai-workflow-token',
            visitor: new LiveChatVisitorData(
                name: 'Ada Visitor',
                email: 'ada@example.test',
                company: 'Example Co',
                processingConsent: true,
            ),
        ),
        $installation->site_id,
        $installation,
    );
    $conversation = $result['conversation'];
    $visitorMessage = $result['visitor_message'];

    $answer = live_chat_ai_workflow_array(RunAIOrchestratorCapabilityAction::run(new AIOrchestratorRunData(
        moduleKey: 'live-chat',
        capabilityKey: 'answer-approved-sources',
        prompt: $visitorMessage->body,
        context: [
            'conversation_id' => $conversation->id,
            'message_id' => $visitorMessage->id,
        ],
    )));

    expect($answer['status'] ?? null)->toBe('grounded')
        ->and($answer['source_document_ids'] ?? null)->toBe([$document->id])
        ->and($answer['model_tier'] ?? null)->toBe('strong');

    $summary = live_chat_ai_workflow_array(RunAIOrchestratorCapabilityAction::run(new AIOrchestratorRunData(
        moduleKey: 'live-chat',
        capabilityKey: 'summarize-conversation',
        prompt: 'Summarize this conversation for an operator.',
        context: ['conversation_id' => $conversation->id],
    )));
    $suggestion = live_chat_ai_workflow_array(RunAIOrchestratorCapabilityAction::run(new AIOrchestratorRunData(
        moduleKey: 'live-chat',
        capabilityKey: 'suggest-human-reply',
        prompt: 'Suggest a human reply.',
        context: ['conversation_id' => $conversation->id],
    )));
    $metadata = live_chat_ai_workflow_array($conversation->refresh()->metadata ?? []);
    $aiMetadata = live_chat_ai_workflow_array($metadata['ai'] ?? []);

    expect($summary['summary'] ?? null)->toContain('Ada Visitor')
        ->and($summary['source_document_ids'] ?? null)->toBe([$document->id])
        ->and($suggestion['suggested_reply'] ?? null)->toBeString()
        ->and($aiMetadata['summary'] ?? null)->toContain('Ada Visitor')
        ->and($aiMetadata['suggested_reply'] ?? null)->toBeString()
        ->and(LiveChatAIRun::query()->whereIn('capability_key', [
            'live-chat-summarize-conversation',
            'live-chat-suggest-human-reply',
        ])->count())->toBe(2);
});

it('reports installation analytics for AI answers, handoffs, leads, and gaps', function (): void {
    $installation = $this->createLiveChatInstallation();

    (new StartLiveChatConversationAction)->handle(
        new IncomingLiveChatMessageData(
            body: 'Do you provide museum onboarding for curators?',
            visitorToken: 'analytics-gap-token',
        ),
        $installation->site_id,
        $installation,
    );

    $analytics = (new BuildLiveChatAnalyticsAction)->handle($installation->site_id, $installation->id);

    expect($analytics['total'])->toBe(1)
        ->and($analytics['ai_answer_rate'])->toBe(0.0)
        ->and($analytics['handoff_rate'])->toBe(0.0)
        ->and($analytics['lead_capture_rate'])->toBe(0.0)
        ->and($analytics['knowledge_gaps'])->toHaveCount(1);
});
