<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Controllers;

use Capell\LiveChat\Actions\ApplyLiveChatCorsHeadersAction;
use Capell\LiveChat\Actions\GuardLiveChatInstallationOriginAction;
use Capell\LiveChat\Actions\RequestLiveChatHandoffAction;
use Capell\LiveChat\Actions\ResolveLiveChatConversationForInstallationAction;
use Capell\LiveChat\Actions\ResolveLiveChatInstallationAction;
use Capell\LiveChat\Http\Controllers\Concerns\BuildsLiveChatPayloads;
use Capell\LiveChat\Http\Requests\RequestLiveChatHandoffRequest;
use Capell\LiveChat\Models\LiveChatConversation;
use Illuminate\Http\JsonResponse;

final class RequestLiveChatHandoffController
{
    use BuildsLiveChatPayloads;

    public function __invoke(RequestLiveChatHandoffRequest $request): JsonResponse
    {
        $publicKey = $this->nullableString($request->route('public_key'));
        $conversationUuid = $this->nullableString($request->route('conversation'));
        $origin = null;
        $validated = $request->validated();

        abort_if($conversationUuid === null, 404);

        if ($publicKey === null) {
            $liveChatConversation = LiveChatConversation::query()
                ->where('uuid', $conversationUuid)
                ->firstOrFail();
        } else {
            $installation = ResolveLiveChatInstallationAction::run($publicKey);

            abort_if($installation === null, 404);

            $origin = GuardLiveChatInstallationOriginAction::run($installation, $request);
            $liveChatConversation = ResolveLiveChatConversationForInstallationAction::run(
                installation: $installation,
                uuid: $conversationUuid,
                visitorToken: $this->nullableString($validated['visitor_token'] ?? null),
            );
        }

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

        return $this->publicResponse(response()->json([
            'conversation' => [
                'uuid' => $updatedConversation->uuid,
                'status' => $updatedConversation->status->value,
                'priority' => $updatedConversation->priority->value,
                'assignment_queue' => $updatedConversation->assignment_queue,
                'handoff_requested_at' => $updatedConversation->handoff_requested_at?->toISOString(),
            ],
            'message' => __('capell-live-chat::generic.messages.handoff_requested'),
        ]), $origin);
    }

    private function publicResponse(JsonResponse $response, ?string $origin): JsonResponse
    {
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        ApplyLiveChatCorsHeadersAction::run($response, $origin);

        return $response;
    }
}
