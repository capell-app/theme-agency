<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\LiveChat\Enums\LiveChatKnowledgeGapStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $installation_id
 * @property int|null $conversation_id
 * @property int|null $message_id
 * @property string $question_hash
 * @property string|null $question
 * @property string|null $source_area
 * @property LiveChatKnowledgeGapStatus $status
 * @property int $occurrence_count
 * @property CarbonImmutable|null $first_seen_at
 * @property CarbonImmutable|null $last_seen_at
 * @property array<string, mixed>|null $metadata
 * @property-read LiveChatInstallation|null $installation
 * @property-read LiveChatConversation|null $conversation
 * @property-read LiveChatMessage|null $message
 */
class LiveChatKnowledgeGap extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'conversation_id',
        'first_seen_at',
        'installation_id',
        'last_seen_at',
        'message_id',
        'metadata',
        'occurrence_count',
        'question',
        'question_hash',
        'source_area',
        'status',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'occurrence_count' => 1,
        'status' => 'open',
    ];

    public static function hashQuestion(string $question): string
    {
        return hash('sha256', mb_strtolower(trim(preg_replace('/\s+/', ' ', $question) ?? $question)));
    }

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.knowledge_gaps');

        return is_string($tableName) ? $tableName : 'live_chat_knowledge_gaps';
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
            'first_seen_at' => 'immutable_datetime',
            'last_seen_at' => 'immutable_datetime',
            'metadata' => 'encrypted:array',
            'occurrence_count' => 'integer',
            'question' => 'encrypted',
            'status' => LiveChatKnowledgeGapStatus::class,
        ];
    }
}
