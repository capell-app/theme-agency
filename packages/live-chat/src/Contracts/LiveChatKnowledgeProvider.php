<?php

declare(strict_types=1);

namespace Capell\LiveChat\Contracts;

use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatMessage;

interface LiveChatKnowledgeProvider
{
    public const string TAG = 'capell-live-chat.knowledge-provider';

    /**
     * @return list<LiveChatKnowledgeDocument>
     */
    public function documents(LiveChatConversation $conversation, LiveChatMessage $message): array;
}
