<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Controllers;

use Capell\LiveChat\Actions\RequestLiveChatHandoffAction;
use Capell\LiveChat\Http\Controllers\Concerns\BuildsLiveChatPayloads;
use Capell\LiveChat\Http\Requests\RequestLiveChatHandoffRequest;
use Capell\LiveChat\Models\LiveChatConversation;
use Illuminate\Http\JsonResponse;

final class RequestLiveChatHandoffController
{
    use BuildsLiveChatPayloads;

    public function __invoke(RequestLiveChatHandoffRequest $request, string $conversation): JsonResponse
    {
        $liveChatConversation = LiveChatConversation::query()
            ->where('uuid', $conversation)
            ->firstOrFail();
        $validated = $request->validated();
        $visitor = $this->visitorData($validated['visitor'] ?? null);

        if ($visitor !== null) {
            $liveChatConversation->fill([
                'visitor_name' => $visitor->name ?? $liveChatConversation->visitor_name,
                'visitor_email' => $visitor->email ?? $liveChatConversation->visitor_email,
                'visitor_phone' => $visitor->phone ?? $liveChatConversation->visitor_phone,
                'visitor_company' => $visitor->company ?? $liveChatConversation->visitor_company,
            ])->save();
        }

        $updatedConversation = RequestLiveChatHandoffAction::run(
            conversation: $liveChatConversation,
            note: $this->nullableString($validated['note'] ?? null),
        );

        return $this->noStore(response()->json([
            'conversation' => [
                'uuid' => $updatedConversation->uuid,
                'status' => $updatedConversation->status->value,
                'priority' => $updatedConversation->priority->value,
                'assignment_queue' => $updatedConversation->assignment_queue,
                'handoff_requested_at' => $updatedConversation->handoff_requested_at?->toISOString(),
            ],
            'message' => __('capell-live-chat::generic.messages.handoff_requested'),
        ]));
    }

    private function noStore(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
