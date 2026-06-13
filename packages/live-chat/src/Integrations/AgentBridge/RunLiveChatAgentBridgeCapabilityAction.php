<?php

declare(strict_types=1);

namespace Capell\LiveChat\Integrations\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\LiveChat\Actions\BuildLiveChatOperatorStateAction;
use Capell\LiveChat\Actions\BuildLiveChatSuggestedReplyAction;
use Capell\LiveChat\Actions\BuildLiveChatTranscriptAction;
use Capell\LiveChat\Actions\CloseLiveChatConversationAction;
use Capell\LiveChat\Actions\RequestLiveChatHandoffAction;
use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

final class RunLiveChatAgentBridgeCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        return match ($invocation->capability->key) {
            'capell.live-chat.conversations.list' => $this->listConversations($invocation),
            'capell.live-chat.conversations.inspect' => $this->inspectConversation($invocation),
            'capell.live-chat.summary.preview' => $this->previewSummary($invocation),
            'capell.live-chat.reply.preview' => $this->previewReply($invocation),
            'capell.live-chat.escalation.preview' => $this->previewEscalation($invocation),
            'capell.live-chat.escalate' => $this->previewEscalation($invocation),
            'capell.live-chat.close' => $this->previewClose($invocation),
            default => throw new RuntimeException(sprintf('Unsupported Live Chat Agent Bridge capability [%s].', $invocation->capability->key)),
        };
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        return match ($invocation->capability->key) {
            'capell.live-chat.escalate' => $this->executeEscalation($invocation),
            'capell.live-chat.close' => $this->executeClose($invocation),
            default => $this->preview($invocation),
        };
    }

    private function listConversations(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $limit = min(50, max(1, $this->intPayload($invocation, 'limit') ?? 10));
        $query = LiveChatConversation::query()
            ->latest('last_message_at')
            ->limit($limit);
        $this->applyFilters($query, $invocation);

        $conversations = $query
            ->get()
            ->map(static fn (LiveChatConversation $conversation): array => [
                'id' => $conversation->id,
                'uuid' => $conversation->uuid,
                'installation_id' => $conversation->installation_id,
                'status' => $conversation->status->value,
                'intent' => $conversation->intent?->value,
                'priority' => $conversation->priority->value,
                'last_message_at' => $conversation->last_message_at?->toISOString(),
            ])
            ->values()
            ->all();

        return new CapabilityResultData(
            ok: true,
            message: __('capell-live-chat::generic.agent_bridge.list_conversations_result'),
            data: ['conversations' => array_values($conversations)],
        );
    }

    private function inspectConversation(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $conversation = $this->conversation($invocation);

        return new CapabilityResultData(
            ok: true,
            message: __('capell-live-chat::generic.agent_bridge.inspect_conversation_result'),
            data: [
                'conversation' => $this->conversationPayload($conversation),
                'transcript' => (new BuildLiveChatTranscriptAction)->handle($conversation),
                'operator_state' => (new BuildLiveChatOperatorStateAction)->handle($conversation),
            ],
        );
    }

    private function previewSummary(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $state = (new BuildLiveChatOperatorStateAction)->handle($this->conversation($invocation));

        return new CapabilityResultData(
            ok: true,
            message: __('capell-live-chat::generic.agent_bridge.preview_summary_result'),
            data: ['operator_state' => $state],
        );
    }

    private function previewReply(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $conversation = $this->conversation($invocation);
        $state = (new BuildLiveChatOperatorStateAction)->handle($conversation);

        return new CapabilityResultData(
            ok: true,
            message: __('capell-live-chat::generic.agent_bridge.preview_reply_result'),
            data: [
                'operator_state' => $state,
                'suggested_reply' => (new BuildLiveChatSuggestedReplyAction)->handle($conversation, $state),
            ],
        );
    }

    private function previewEscalation(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $conversation = $this->conversation($invocation);
        $state = (new BuildLiveChatOperatorStateAction)->handle($conversation);

        return new CapabilityResultData(
            ok: true,
            message: __('capell-live-chat::generic.agent_bridge.preview_escalation_result'),
            data: [
                'conversation' => $this->conversationPayload($conversation),
                'operator_state' => $state,
                'will_escalate' => $conversation->status !== ConversationStatus::Closed,
                'route_to' => $conversation->assignment_queue ?? $this->configString('capell-live-chat.escalation.default_queue', 'support'),
            ],
        );
    }

    private function previewClose(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $conversation = $this->conversation($invocation);

        return new CapabilityResultData(
            ok: true,
            message: __('capell-live-chat::generic.agent_bridge.preview_close_result'),
            data: [
                'conversation' => $this->conversationPayload($conversation),
                'will_close' => $conversation->status !== ConversationStatus::Closed,
            ],
        );
    }

    private function executeEscalation(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $conversation = (new RequestLiveChatHandoffAction)->handle(
            $this->conversation($invocation),
            $this->stringPayload($invocation, 'note'),
        );

        return new CapabilityResultData(
            ok: true,
            message: __('capell-live-chat::generic.agent_bridge.confirm_escalation_result'),
            data: ['conversation' => $this->conversationPayload($conversation)],
        );
    }

    private function executeClose(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $conversation = (new CloseLiveChatConversationAction)->handle($this->conversation($invocation));

        return new CapabilityResultData(
            ok: true,
            message: __('capell-live-chat::generic.agent_bridge.confirm_close_result'),
            data: ['conversation' => $this->conversationPayload($conversation)],
        );
    }

    private function conversation(CapabilityInvocationData $invocation): LiveChatConversation
    {
        $conversationId = $this->intPayload($invocation, 'conversation_id');

        throw_unless($conversationId !== null, RuntimeException::class, 'Live Chat Agent Bridge capability requires a conversation_id payload value.');

        return LiveChatConversation::query()->findOrFail($conversationId);
    }

    /**
     * @param  Builder<LiveChatConversation>  $query
     */
    private function applyFilters(Builder $query, CapabilityInvocationData $invocation): void
    {
        $siteId = $this->intPayload($invocation, 'site_id');
        $installationId = $this->intPayload($invocation, 'installation_id');
        $status = $this->stringPayload($invocation, 'status');

        if ($siteId !== null) {
            $query->where('site_id', $siteId);
        }

        if ($installationId !== null) {
            $query->where('installation_id', $installationId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function conversationPayload(LiveChatConversation $conversation): array
    {
        $latestMessage = $conversation->messages()->latest('id')->first();

        return [
            'id' => $conversation->id,
            'uuid' => $conversation->uuid,
            'installation_id' => $conversation->installation_id,
            'site_id' => $conversation->site_id,
            'status' => $conversation->status->value,
            'intent' => $conversation->intent?->value,
            'priority' => $conversation->priority->value,
            'assignment_queue' => $conversation->assignment_queue,
            'last_message_at' => $conversation->last_message_at?->toISOString(),
            'latest_message' => $latestMessage instanceof LiveChatMessage ? [
                'role' => $latestMessage->role->value,
                'body' => $latestMessage->body,
                'created_at' => $latestMessage->created_at?->toISOString(),
            ] : null,
        ];
    }

    private function intPayload(CapabilityInvocationData $invocation, string $key): ?int
    {
        $value = $invocation->payload[$key] ?? null;

        return is_int($value) ? $value : null;
    }

    private function stringPayload(CapabilityInvocationData $invocation, string $key): ?string
    {
        $value = $invocation->payload[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function configString(string $key, string $fallback): string
    {
        $value = config($key);

        return is_string($value) && $value !== '' ? $value : $fallback;
    }
}
