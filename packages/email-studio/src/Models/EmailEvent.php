<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Models;

use Capell\EmailStudio\Database\Factories\EmailEventFactory;
use Capell\EmailStudio\Enums\EmailEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class EmailEvent extends Model
{
    /** @use HasFactory<EmailEventFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'site_scope_key',
        'email_profile_id',
        'email_message_id',
        'email_recipient_id',
        'type',
        'provider_event_id',
        'idempotency_key',
        'provider_payload',
        'occurred_at',
    ];

    protected static string $factory = EmailEventFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-email-studio.tables.events');

        return is_string($tableName) ? $tableName : 'email_events';
    }

    /**
     * @return BelongsTo<EmailProfile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(EmailProfile::class, 'email_profile_id');
    }

    /**
     * @return BelongsTo<EmailMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(EmailMessage::class, 'email_message_id');
    }

    /**
     * @return BelongsTo<EmailRecipient, $this>
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(EmailRecipient::class, 'email_recipient_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => EmailEventType::class,
            'provider_payload' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
