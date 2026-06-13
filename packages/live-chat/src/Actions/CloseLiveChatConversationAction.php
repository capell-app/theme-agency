<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Models\LiveChatConversation;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class CloseLiveChatConversationAction
{
    use AsAction;

    public function handle(LiveChatConversation $conversation): LiveChatConversation
    {
        $conversation->forceFill([
            'status' => ConversationStatus::Closed,
            'closed_at' => CarbonImmutable::now(),
        ])->save();

        SyncLiveChatConversationContactAction::run($conversation);

        return $conversation->fresh() ?? $conversation;
    }
}
