<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Controllers;

use Capell\LiveChat\Actions\StartLiveChatConversationAction;
use Capell\LiveChat\Actions\StoreLiveChatAttachmentsAction;
use Capell\LiveChat\Http\Controllers\Concerns\BuildsLiveChatPayloads;
use Capell\LiveChat\Http\Requests\StoreLiveChatConversationRequest;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class StoreLiveChatConversationController
{
    use BuildsLiveChatPayloads;

    public function __invoke(StoreLiveChatConversationRequest $request): JsonResponse
    {
        $siteId = (int) config('capell-live-chat.default_site_id', 1);
        $conversationUuid = (string) Str::uuid();
        $attachments = StoreLiveChatAttachmentsAction::run(
            files: $this->uploadedFiles($request->file('attachments', [])),
            conversationUuid: $conversationUuid,
        );

        $data = $this->incomingMessageData(
            array_replace($request->validated(), ['conversation_uuid' => $conversationUuid]),
            $attachments,
        );

        $result = StartLiveChatConversationAction::run($data, $siteId);

        return $this->noStore(response()->json($this->responsePayload(
            $result['conversation'],
            $result['visitor_message'],
            $result['assistant_message'],
        )));
    }

    /**
     * @return list<UploadedFile>
     */
    private function uploadedFiles(mixed $files): array
    {
        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter($files, static fn (mixed $file): bool => $file instanceof UploadedFile));
    }

    /**
     * @return array<string, mixed>
     */
    private function responsePayload(
        LiveChatConversation $conversation,
        LiveChatMessage $visitorMessage,
        LiveChatMessage $assistantMessage,
    ): array {
        return [
            'conversation' => [
                'uuid' => $conversation->uuid,
                'status' => $conversation->status->value,
                'flow' => $conversation->flow->value,
                'intent' => $conversation->intent?->value,
                'priority' => $conversation->priority->value,
                'assignment_queue' => $conversation->assignment_queue,
                'requires_contact' => $assistantMessage->requires_contact,
            ],
            'messages' => [
                $this->messagePayload($visitorMessage),
                $this->messagePayload($assistantMessage),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function messagePayload(LiveChatMessage $message): array
    {
        return [
            'id' => $message->getKey(),
            'role' => $message->role->value,
            'body' => $message->body,
            'requires_contact' => $message->requires_contact,
            'created_at' => $message->created_at?->toISOString(),
        ];
    }

    private function noStore(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
