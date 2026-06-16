<?php

declare(strict_types=1);

namespace Capell\LiveChat\Tests\Fixtures;

use Capell\LiveChat\Contracts\LiveChatResponder;
use Capell\LiveChat\Data\LiveChatResponseData;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatMessage;
use RuntimeException;

final class FailingLiveChatResponder implements LiveChatResponder
{
    public function respond(LiveChatConversation $conversation, LiveChatMessage $message): LiveChatResponseData
    {
        throw new RuntimeException('Responder unavailable');
    }
}
