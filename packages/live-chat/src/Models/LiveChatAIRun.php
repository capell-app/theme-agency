<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\LiveChat\Enums\LiveChatAIRunStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $installation_id
 * @property int|null $conversation_id
 * @property int|null $message_id
 * @property string $capability_key
 * @property string $model_tier
 * @property float|null $confidence
 * @property int|null $latency_ms
 * @property LiveChatAIRunStatus $status
 * @property list<int>|null $source_document_ids
 * @property string|null $refusal_reason
 * @property string|null $error_message
 * @property array<string, mixed>|null $input_payload
 * @property array<string, mixed>|null $output_payload
 * @property CarbonImmutable|null $created_at
 * @property-read LiveChatInstallation|null $installation
 * @property-read LiveChatConversation|null $conversation
 * @property-read LiveChatMessage|null $message
 */
class LiveChatAIRun extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'capability_key',
        'confidence',
        'conversation_id',
        'error_message',
        'input_payload',
        'installation_id',
        'latency_ms',
        'message_id',
        'model_tier',
        'output_payload',
        'refusal_reason',
        'source_document_ids',
        'status',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'model_tier' => 'local',
        'status' => 'succeeded',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.ai_runs');

        return is_string($tableName) ? $tableName : 'live_chat_ai_runs';
    }

    /**
     * @return BelongsTo<LiveChatInstallation, $this>
     */
    public function installation(): BelongsTo
    {
        return $this->belongsTo(LiveChatInstallation::class, 'installation_id');
    }

    /**
     * @return BelongsTo<LiveChatConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(LiveChatConversation::class, 'conversation_id');
    }

    /**
     * @return BelongsTo<LiveChatMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(LiveChatMessage::class, 'message_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'error_message' => 'encrypted',
            'input_payload' => 'encrypted:array',
            'latency_ms' => 'integer',
            'output_payload' => 'encrypted:array',
            'refusal_reason' => 'encrypted',
            'source_document_ids' => 'encrypted:array',
            'status' => LiveChatAIRunStatus::class,
        ];
    }
}
