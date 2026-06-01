<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Models;

use Capell\Core\Models\Site;
use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $site_id
 * @property int $portal_account_id
 * @property SupportRequestStatus $status
 * @property SupportRequestPriority $priority
 * @property string $subject
 * @property string $message
 * @property string|null $requester_email
 * @property string|null $requester_email_hash
 * @property array<string, mixed>|null $context
 * @property-read PortalAccount $account
 */
class PortalSupportRequest extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'portal_account_id',
        'status',
        'priority',
        'subject',
        'message',
        'requester_email',
        'requester_email_hash',
        'source',
        'external_reference',
        'context',
        'submitted_at',
        'resolved_at',
        'closed_at',
    ];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-customer-portal.tables.support_requests');

        return is_string($tableName) ? $tableName : 'portal_support_requests';
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<PortalAccount, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(PortalAccount::class, 'portal_account_id');
    }

    #[Override]
    protected static function booted(): void
    {
        static::saving(function (PortalSupportRequest $supportRequest): void {
            $supportRequest->requester_email = PortalAccount::normalizeEmail($supportRequest->requester_email);
            $supportRequest->requester_email_hash = PortalAccount::emailHash($supportRequest->requester_email);
        });
    }

    /**
     * @param  Builder<PortalSupportRequest>  $query
     * @return Builder<PortalSupportRequest>
     */
    protected function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            SupportRequestStatus::Resolved->value,
            SupportRequestStatus::Closed->value,
        ]);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => SupportRequestStatus::class,
            'priority' => SupportRequestPriority::class,
            'subject' => 'encrypted',
            'message' => 'encrypted',
            'requester_email' => 'encrypted',
            'context' => 'encrypted:array',
            'submitted_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
        ];
    }
}
