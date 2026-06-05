<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use jdavidbakr\MailTracker\Model\SentEmailUrlClicked as VendorSentEmailUrlClicked;
use Override;

/**
 * @property int $id
 * @property int $sent_email_id
 * @property string|null $url
 * @property string $hash
 * @property int $clicks
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class SentEmailUrlClicked extends VendorSentEmailUrlClicked
{
    use HasFactory;
    use HasFactory;

    protected $table = 'sent_emails_url_clicked';

    /**
     * @return BelongsTo<SentEmail, $this>
     */
    public function sentEmail(): BelongsTo
    {
        return $this->belongsTo(SentEmail::class, 'sent_email_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
