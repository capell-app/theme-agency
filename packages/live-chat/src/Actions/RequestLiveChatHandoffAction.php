<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\EscalationReason;
use Capell\LiveChat\Enums\LiveChatPriority;
use Capell\LiveChat\Enums\MessageRole;
use Capell\LiveChat\Models\LiveChatConversation;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class RequestLiveChatHandoffAction
{
    use AsAction;

    public function handle(LiveChatConversation $conversation, ?string $note = null): LiveChatConversation
    {
        $conversation->forceFill([
            'status' => ConversationStatus::WaitingForHuman,
            'priority' => $conversation->priority === LiveChatPriority::Urgent ? LiveChatPriority::Urgent : LiveChatPriority::High,
            'assignment_queue' => $conversation->assignment_queue ?? config('capell-live-chat.escalation.default_queue', 'support'),
            'escalation_reason' => $conversation->escalation_reason ?? EscalationReason::Manual,
            'handoff_requested_at' => $conversation->handoff_requested_at ?? CarbonImmutable::now(),
            'escalated_at' => $conversation->escalated_at ?? CarbonImmutable::now(),
        ])->save();

        $conversation->messages()->create([
            'role' => MessageRole::System,
            'body' => $note !== null && trim($note) !== '' ? $note : 'Human handoff requested.',
            'metadata' => [
                'event' => 'handoff_requested',
            ],
        ]);

        SyncLiveChatConversationContactAction::run($conversation, 'Live chat handoff: ' . ($conversation->visitor_name ?: $conversation->uuid));

        return $conversation->fresh() ?? $conversation;
    }
}
