<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Controllers;

use Capell\LiveChat\Actions\ApplyLiveChatCorsHeadersAction;
use Capell\LiveChat\Actions\DeleteLiveChatAttachmentsAction;
use Capell\LiveChat\Actions\GuardLiveChatInstallationOriginAction;
use Capell\LiveChat\Actions\GuardLiveChatSameSiteRequestAction;
use Capell\LiveChat\Actions\ResolveLiveChatConversationForInstallationAction;
use Capell\LiveChat\Actions\ResolveLiveChatInstallationAction;
use Capell\LiveChat\Actions\StoreLiveChatAttachmentsAction;
use Capell\LiveChat\Actions\StoreLiveChatMessageAction;
use Capell\LiveChat\Http\Controllers\Concerns\BuildsLiveChatPayloads;
use Capell\LiveChat\Http\Requests\StoreLiveChatMessageRequest;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Throwable;

final class StoreLiveChatMessageController
{
    use BuildsLiveChatPayloads;

    public function __invoke(StoreLiveChatMessageRequest $request): JsonResponse
    {
        $publicKey = $this->nullableString($request->route('public_key'));
        $conversationUuid = $this->nullableString($request->route('conversation'));
        $origin = null;
        $validated = $request->validated();

        abort_if($conversationUuid === null, 404);

        $installation = (new ResolveLiveChatInstallationAction)->handle($publicKey);

        abort_if($installation === null, 404);

        if ($publicKey !== null) {
            $origin = (new GuardLiveChatInstallationOriginAction)->handle($installation, $request);
        } else {
            GuardLiveChatSameSiteRequestAction::run($installation, $request);
        }

        $liveChatConversation = (new ResolveLiveChatConversationForInstallationAction)->handle(
            installation: $installation,
            uuid: $conversationUuid,
            visitorToken: $this->nullableString($validated['visitor_token'] ?? null),
        );

        $attachments = (new StoreLiveChatAttachmentsAction)->handle(
            files: $this->uploadedFiles($request->file('attachments', [])),
            conversationUuid: $liveChatConversation->uuid,
        );

        $data = $this->incomingMessageData($validated, $attachments);

        try {
            $result = app(StoreLiveChatMessageAction::class)->handle($liveChatConversation, $data);
        } catch (Throwable $exception) {
            DeleteLiveChatAttachmentsAction::run($attachments);

            throw $exception;
        }

        return $this->publicResponse(response()->json($this->responsePayload(
            $result['conversation'],
            $result['visitor_message'],
            $result['assistant_message'],
        )), $origin);
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
            'role' => $message->role->value,
            'body' => $message->body,
            'requires_contact' => $message->requires_contact,
            'created_at' => $message->created_at?->toISOString(),
        ];
    }

    private function publicResponse(JsonResponse $response, ?string $origin): JsonResponse
    {
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        (new ApplyLiveChatCorsHeadersAction)->handle($response, $origin);

        return $response;
    }
}
