<?php

declare(strict_types=1);

namespace Capell\LiveChat\Contracts;

use Capell\LiveChat\Data\LiveChatResponseData;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatMessage;

interface LiveChatResponder
{
    public function respond(LiveChatConversation $conversation, LiveChatMessage $message): LiveChatResponseData;
}
