<?php

declare(strict_types=1);

namespace Capell\LiveChat\Models;

use Capell\Core\Models\Site;
use Capell\LiveChat\Enums\ConversationFlow;
use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\EscalationReason;
use Capell\LiveChat\Enums\LiveChatIntent;
use Capell\LiveChat\Enums\LiveChatPriority;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Override;

/**
 * @property int $id
 * @property int $site_id
 * @property int|null $installation_id
 * @property string $uuid
 * @property ConversationStatus $status
 * @property ConversationFlow $flow
 * @property LiveChatIntent|null $intent
 * @property LiveChatPriority $priority
 * @property EscalationReason|null $escalation_reason
 * @property int|null $contact_id
 * @property int|null $lead_id
 * @property string|null $assignment_queue
 * @property string|null $first_page_url
 * @property string|null $last_page_url
 * @property string|null $visitor_name
 * @property string|null $visitor_email
 * @property string|null $visitor_email_hash
 * @property string|null $visitor_phone
 * @property string|null $visitor_phone_hash
 * @property string|null $visitor_company
 * @property string|null $visitor_token_hash
 * @property string $timezone
 * @property bool $processing_consent
 * @property bool $marketing_consent
 * @property array<string, mixed>|null $metadata
 * @property CarbonImmutable|null $preferred_callback_at
 * @property CarbonImmutable|null $last_message_at
 * @property CarbonImmutable|null $handoff_requested_at
 * @property CarbonImmutable|null $contact_captured_at
 * @property CarbonImmutable|null $escalated_at
 * @property CarbonImmutable|null $closed_at
 * @property-read Site $site
 * @property-read LiveChatInstallation|null $installation
 */
class LiveChatConversation extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'ai_disclosure_at',
        'assignment_queue',
        'closed_at',
        'contact_captured_at',
        'contact_id',
        'escalated_at',
        'escalation_reason',
        'first_page_url',
        'flow',
        'handoff_requested_at',
        'installation_id',
        'intent',
        'ip_hash',
        'last_message_at',
        'last_page_url',
        'lead_id',
        'locale',
        'marketing_consent',
        'metadata',
        'preferred_callback_at',
        'priority',
        'processing_consent',
        'referrer_url',
        'site_id',
        'status',
        'timezone',
        'user_agent_hash',
        'uuid',
        'visitor_company',
        'visitor_email',
        'visitor_email_hash',
        'visitor_name',
        'visitor_phone',
        'visitor_phone_hash',
        'visitor_token_hash',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'active',
        'flow' => 'message_first',
        'priority' => 'normal',
        'locale' => 'en',
        'timezone' => 'UTC',
    ];

    public static function hashVisitorToken(?string $visitorToken): ?string
    {
        return self::hashNullableIdentity($visitorToken);
    }

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-live-chat.tables.conversations');

        return is_string($tableName) ? $tableName : 'live_chat_conversations';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<LiveChatInstallation, $this>
     */
    public function installation(): BelongsTo
    {
        return $this->belongsTo(LiveChatInstallation::class, 'installation_id');
    }

    /**
     * @return HasMany<LiveChatMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(LiveChatMessage::class, 'conversation_id');
    }

    /**
     * @return HasMany<LiveChatAIRun, $this>
     */
    public function aiRuns(): HasMany
    {
        return $this->hasMany(LiveChatAIRun::class, 'conversation_id');
    }

    /**
     * @return HasMany<LiveChatKnowledgeGap, $this>
     */
    public function knowledgeGaps(): HasMany
    {
        return $this->hasMany(LiveChatKnowledgeGap::class, 'conversation_id');
    }

    #[Override]
    protected static function booted(): void
    {
        static::creating(function (LiveChatConversation $conversation): void {
            if (! is_string($conversation->uuid) || $conversation->uuid === '') {
                $conversation->uuid = (string) Str::uuid();
            }
        });

        static::saving(function (LiveChatConversation $conversation): void {
            $conversation->visitor_email_hash = self::hashNullableIdentity($conversation->visitor_email);
            $conversation->visitor_phone_hash = self::hashNullableIdentity($conversation->visitor_phone);
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ConversationStatus::Active,
            ConversationStatus::WaitingForVisitor,
            ConversationStatus::WaitingForHuman,
        ]);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'ai_disclosure_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'contact_captured_at' => 'immutable_datetime',
            'escalated_at' => 'immutable_datetime',
            'escalation_reason' => EscalationReason::class,
            'flow' => ConversationFlow::class,
            'handoff_requested_at' => 'immutable_datetime',
            'intent' => LiveChatIntent::class,
            'last_message_at' => 'immutable_datetime',
            'marketing_consent' => 'boolean',
            'metadata' => 'encrypted:array',
            'preferred_callback_at' => 'immutable_datetime',
            'priority' => LiveChatPriority::class,
            'processing_consent' => 'boolean',
            'status' => ConversationStatus::class,
            'visitor_company' => 'encrypted',
            'visitor_email' => 'encrypted',
            'visitor_name' => 'encrypted',
            'visitor_phone' => 'encrypted',
        ];
    }

    private static function hashNullableIdentity(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return hash_hmac('sha256', Str::lower(trim($value)), self::hashSecret());
    }

    private static function hashSecret(): string
    {
        $secret = config('capell-live-chat.hash_secret');

        if (is_string($secret) && $secret !== '') {
            return $secret;
        }

        $appKey = config('app.key');

        return is_string($appKey) && $appKey !== '' ? $appKey : 'capell-live-chat';
    }
}
