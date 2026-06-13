<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\LiveChatAIRunData;
use Capell\LiveChat\Models\LiveChatAIRun;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordLiveChatAIRunAction
{
    use AsAction;

    public function handle(LiveChatAIRunData $data): LiveChatAIRun
    {
        return LiveChatAIRun::query()->create([
            'installation_id' => $data->installationId,
            'conversation_id' => $data->conversationId,
            'message_id' => $data->messageId,
            'capability_key' => $data->capabilityKey,
            'model_tier' => $data->modelTier,
            'confidence' => $data->confidence,
            'latency_ms' => $data->latencyMs,
            'status' => $data->status,
            'source_document_ids' => $data->sourceDocumentIds,
            'refusal_reason' => $data->refusalReason,
            'error_message' => $data->errorMessage,
            'input_payload' => $data->inputPayload,
            'output_payload' => $data->outputPayload,
        ]);
    }
}
