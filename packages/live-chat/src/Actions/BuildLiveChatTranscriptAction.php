<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatMessage;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildLiveChatTranscriptAction
{
    use AsAction;

    /**
     * @return list<array{role: string, body: string, created_at: string|null}>
     */
    public function handle(LiveChatConversation $conversation): array
    {
        return array_values($conversation
            ->messages()
            ->oldest()
            ->get()
            ->map(static fn (LiveChatMessage $message): array => [
                'role' => $message->role->value,
                'body' => $message->body,
                'created_at' => $message->created_at?->toISOString(),
            ])
            ->values()
            ->all());
    }
}
