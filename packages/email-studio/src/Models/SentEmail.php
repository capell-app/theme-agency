<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use jdavidbakr\MailTracker\Model\SentEmail as VendorSentEmail;
use Override;

/**
 * @property int $id
 * @property string $hash
 * @property string|null $headers
 * @property string|null $sender_name
 * @property string|null $sender_email
 * @property string|null $recipient_name
 * @property string|null $recipient_email
 * @property string|null $subject
 * @property string|null $content
 * @property int|null $opens
 * @property int|null $clicks
 * @property string|null $message_id
 * @property Collection<int|string, mixed>|null $meta
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $opened_at
 * @property CarbonImmutable|null $clicked_at
 */
class SentEmail extends VendorSentEmail
{
    use HasFactory;

    protected $table = 'sent_emails';

    /** @var list<string> */
    protected $fillable = [
        'hash',
        'headers',
        'sender_name',
        'sender_email',
        'recipient_name',
        'recipient_email',
        'subject',
        'content',
        'opens',
        'clicks',
        'message_id',
        'meta',
        'opened_at',
        'clicked_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @return HasMany<SentEmailUrlClicked, $this>
     */
    public function clickRows(): HasMany
    {
        return $this->hasMany(SentEmailUrlClicked::class, 'sent_email_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'meta' => 'collection',
            'opened_at' => 'immutable_datetime',
            'clicked_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
