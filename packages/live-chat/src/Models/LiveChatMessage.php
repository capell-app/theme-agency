<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\LiveChat\Enums\LiveChatIntent;
use Capell\LiveChat\Enums\MessageRole;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $conversation_id
 * @property MessageRole $role
 * @property string $body
 * @property LiveChatIntent|null $intent
 * @property float|null $confidence
 * @property bool $requires_contact
 * @property array<int, array<string, mixed>>|null $attachments
 * @property array<string, mixed>|null $metadata
 * @property CarbonImmutable|null $read_at
 * @property-read LiveChatConversation $conversation
 */
class LiveChatMessage extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'attachments',
        'body',
        'confidence',
        'conversation_id',
        'intent',
        'metadata',
        'read_at',
        'requires_contact',
        'role',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.messages');

        return is_string($tableName) ? $tableName : 'live_chat_messages';
    }

    /**
     * @return BelongsTo<LiveChatConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(LiveChatConversation::class, 'conversation_id');
    }

    /**
     * @return HasMany<LiveChatAIRun, $this>
     */
    public function aiRuns(): HasMany
    {
        return $this->hasMany(LiveChatAIRun::class, 'message_id');
    }

    /**
     * @return HasMany<LiveChatKnowledgeGap, $this>
     */
    public function knowledgeGaps(): HasMany
    {
        return $this->hasMany(LiveChatKnowledgeGap::class, 'message_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'attachments' => 'encrypted:array',
            'body' => 'encrypted',
            'confidence' => 'float',
            'intent' => LiveChatIntent::class,
            'metadata' => 'encrypted:array',
            'read_at' => 'immutable_datetime',
            'requires_contact' => 'boolean',
            'role' => MessageRole::class,
        ];
    }
}
