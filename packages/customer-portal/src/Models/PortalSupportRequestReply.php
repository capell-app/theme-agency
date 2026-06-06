<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $portal_support_request_id
 * @property string $sender_type
 * @property string|null $author_type
 * @property int|null $author_id
 * @property string $message
 * @property array<int, array<string, mixed>>|null $attachments
 * @property CarbonImmutable $submitted_at
 */
class PortalSupportRequestReply extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'portal_support_request_id',
        'sender_type',
        'author_type',
        'author_id',
        'message',
        'attachments',
        'submitted_at',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-customer-portal.tables.support_request_replies');

        return is_string($tableName) ? $tableName : 'portal_support_request_replies';
    }

    /**
     * @return BelongsTo<PortalSupportRequest, $this>
     */
    public function supportRequest(): BelongsTo
    {
        return $this->belongsTo(PortalSupportRequest::class, 'portal_support_request_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'message' => 'encrypted',
            'attachments' => 'encrypted:array',
            'submitted_at' => 'immutable_datetime',
        ];
    }
}
