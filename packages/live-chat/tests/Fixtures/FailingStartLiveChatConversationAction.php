<?php

declare(strict_types=1);

namespace Capell\LiveChat\Tests\Fixtures;

use Capell\LiveChat\Data\IncomingLiveChatMessageData;
use Capell\LiveChat\Models\LiveChatInstallation;
use RuntimeException;

final class FailingStartLiveChatConversationAction
{
    public function handle(IncomingLiveChatMessageData $data, int $siteId, ?LiveChatInstallation $installation = null): never
    {
        throw new RuntimeException('Conversation write failed after attachment storage');
    }
}
