<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Models;

use Capell\EmailStudio\Database\Factories\EmailMessageFactory;
use Capell\EmailStudio\Enums\EmailMessageStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property string $site_scope_key
 * @property int $email_profile_id
 * @property int|null $email_template_id
 * @property int|null $email_template_variant_id
 * @property EmailMessageStatus $status
 * @property string $subject
 * @property string|null $preview_text
 * @property string|null $rendered_html
 * @property string|null $rendered_text
 * @property array<string, mixed>|null $context_snapshot
 * @property array<string, string>|null $headers
 * @property array<int, array{disk: string, path: string, name: string, mime: string}>|null $attachments
 * @property string|null $triggered_by_type
 * @property int|null $triggered_by_id
 * @property CarbonImmutable|null $queued_at
 * @property CarbonImmutable|null $sent_at
 * @property CarbonImmutable|null $failed_at
 * @property string|null $failure_reason
 * @property EmailProfile|null $profile
 */
class EmailMessage extends Model
{
    /** @use HasFactory<EmailMessageFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'site_scope_key',
        'email_profile_id',
        'email_template_id',
        'email_template_variant_id',
        'status',
        'subject',
        'preview_text',
        'rendered_html',
        'rendered_text',
        'context_snapshot',
        'headers',
        'attachments',
        'triggered_by_type',
        'triggered_by_id',
        'queued_at',
        'sent_at',
        'failed_at',
        'failure_reason',
    ];

    protected static string $factory = EmailMessageFactory::class;

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-email-studio.tables.messages');

        return is_string($tableName) ? $tableName : 'email_messages';
    }

    /**
     * @return BelongsTo<EmailProfile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(EmailProfile::class, 'email_profile_id');
    }

    /**
     * @return BelongsTo<EmailTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    /**
     * @return BelongsTo<EmailTemplateVariant, $this>
     */
    public function templateVariant(): BelongsTo
    {
        return $this->belongsTo(EmailTemplateVariant::class, 'email_template_variant_id');
    }

    /**
     * @return HasMany<EmailRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class);
    }

    /**
     * @return HasMany<EmailEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(EmailEvent::class);
    }

    /**
     * @return HasMany<EmailReply, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(EmailReply::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => EmailMessageStatus::class,
            'context_snapshot' => 'array',
            'headers' => 'array',
            'attachments' => 'array',
            'queued_at' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'failed_at' => 'immutable_datetime',
        ];
    }
}
