<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatInstallation;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ResolveLiveChatConversationForInstallationAction
{
    use AsAction;

    public function handle(LiveChatInstallation $installation, string $uuid, ?string $visitorToken): LiveChatConversation
    {
        if (! is_string($visitorToken) || trim($visitorToken) === '') {
            throw new HttpException(403, 'Live chat visitor token is required.');
        }

        $conversation = LiveChatConversation::query()
            ->where('uuid', $uuid)
            ->where('installation_id', $installation->getKey())
            ->firstOrFail();

        if ($conversation->visitor_token_hash !== LiveChatConversation::hashVisitorToken($visitorToken)) {
            throw new HttpException(403, 'Live chat visitor token does not match this conversation.');
        }

        return $conversation;
    }
}
