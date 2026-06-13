<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Models\LiveChatConversation;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildLiveChatAnalyticsAction
{
    use AsAction;

    /**
     * @return array{total: int, active: int, waiting_for_human: int, captured_contacts: int, handoffs: int}
     */
    public function handle(int $siteId): array
    {
        $baseQuery = LiveChatConversation::query()->where('site_id', $siteId);

        return [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', ConversationStatus::Active)->count(),
            'waiting_for_human' => (clone $baseQuery)->where('status', ConversationStatus::WaitingForHuman)->count(),
            'captured_contacts' => (clone $baseQuery)->whereNotNull('contact_captured_at')->count(),
            'handoffs' => (clone $baseQuery)->whereNotNull('handoff_requested_at')->count(),
        ];
    }
}
